<?php

namespace App\Filament\Pages;

use App\ContentAssistant\Services\ContentAssistantOrchestrator;
use App\ContentAssistant\Support\ContentTargetResolver;
use App\Enums\ContentProposalStatus;
use App\Enums\ContentTargetType;
use App\Models\AiConversation;
use App\Models\ContentProposal;
use App\Models\Page;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page as FilamentPage;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;

class ContentAssistant extends FilamentPage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

    protected static ?string $navigationLabel = 'AI Content Assistant';

    protected static ?string $title = 'AI Content Assistant';

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.pages.content-assistant';

    public ?int $conversationId = null;

    public ?int $targetPageId = null;

    public string $message = '';

    /** @var array<int, array<string, mixed>> */
    public array $diff = [];

    /** @var array<int, string> */
    public array $validationErrors = [];

    /** @var array<int, string> */
    public array $validationWarnings = [];

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user?->isSuperAdmin() || $user?->isEditor() || $user?->isAuthor();
    }

    public function mount(): void
    {
        $home = Page::query()->where('slug', 'home')->first();
        $this->targetPageId = $home?->id;
    }

    public function getConversationsProperty(): Collection
    {
        return AiConversation::query()
            ->where('user_id', auth()->id())
            ->latest('last_activity_at')
            ->limit(20)
            ->get();
    }

    public function getActiveConversationProperty(): ?AiConversation
    {
        if (! $this->conversationId) {
            return null;
        }

        return AiConversation::query()
            ->with(['messages', 'latestProposal.operations'])
            ->where('user_id', auth()->id())
            ->find($this->conversationId);
    }

    public function getLatestProposalProperty(): ?ContentProposal
    {
        return $this->activeConversation?->latestProposal;
    }

    public function getPageOptionsProperty(): array
    {
        return Page::query()
            ->orderBy('title')
            ->pluck('title', 'id')
            ->all();
    }

    public function startConversation(ContentAssistantOrchestrator $orchestrator): void
    {
        abort_if(! $this->targetPageId, 422, 'Select a page first.');

        $conversation = $orchestrator->startConversation(
            auth()->user(),
            ContentTargetType::Page,
            $this->targetPageId,
        );

        $this->conversationId = $conversation->id;
        $this->resetValidationState();

        Notification::make()->title('Conversation started')->success()->send();
    }

    public function sendMessage(ContentAssistantOrchestrator $orchestrator): void
    {
        $this->validate(['message' => ['required', 'string', 'max:5000']]);

        if (! $this->conversationId) {
            $this->startConversation($orchestrator);
        }

        $conversation = AiConversation::query()
            ->where('user_id', auth()->id())
            ->findOrFail($this->conversationId);

        $proposal = $orchestrator->sendMessage(
            $conversation,
            auth()->user(),
            trim($this->message),
            $orchestrator->generateIdempotencyKey(),
        );

        $this->message = '';
        $this->resetValidationState();
        $this->conversationId = $conversation->id;

        $result = $orchestrator->validateProposal($proposal->fresh('operations'), auth()->user());
        $this->applyValidationResult($result);

        Notification::make()
            ->title($result['valid'] ? 'Proposal ready for review' : 'Proposal needs revision')
            ->{$result['valid'] ? 'success' : 'warning'}()
            ->send();
    }

    public function validateProposal(ContentAssistantOrchestrator $orchestrator): void
    {
        $proposal = $this->latestProposal;
        abort_if(! $proposal, 422);

        $result = $orchestrator->validateProposal($proposal, auth()->user());
        $this->applyValidationResult($result);
    }

    public function applyDraft(ContentAssistantOrchestrator $orchestrator): void
    {
        $proposal = $this->latestProposal;
        abort_if(! $proposal, 422);

        if ($proposal->status !== ContentProposalStatus::Validated) {
            Notification::make()->title('Validate the proposal before saving a draft.')->danger()->send();

            return;
        }

        $orchestrator->applyDraft($proposal, auth()->user());

        Notification::make()->title('Draft saved')->body('The content is unpublished and ready for preview.')->success()->send();
    }

    public function openPreview(ContentAssistantOrchestrator $orchestrator): void
    {
        $proposal = $this->latestProposal;
        abort_if(! $proposal, 422);

        $url = $orchestrator->previewUrl($proposal, auth()->user());
        $this->dispatch('open-preview', url: $url);
    }

    public function rejectProposal(ContentAssistantOrchestrator $orchestrator): void
    {
        $proposal = $this->latestProposal;
        abort_if(! $proposal, 422);

        $orchestrator->rejectProposal($proposal, auth()->user());
        $this->resetValidationState();

        Notification::make()->title('Proposal rejected')->success()->send();
    }

    public function selectConversation(int $conversationId): void
    {
        $this->conversationId = $conversationId;
        $this->resetValidationState();

        $proposal = $this->latestProposal;

        if ($proposal) {
            $target = $proposal->target();
            if ($target instanceof Page) {
                $this->targetPageId = $target->id;
            }
        }
    }

    public function proposalStatusLabel(): ?string
    {
        return $this->latestProposal?->status->label();
    }

    public function targetLabel(): ?string
    {
        $conversation = $this->activeConversation;

        if (! $conversation?->target_type || ! $conversation->target_id) {
            return null;
        }

        $target = ContentTargetResolver::find($conversation->target_type, $conversation->target_id);

        return $target ? ContentTargetResolver::label($target) : null;
    }

    /**
     * @param  array{valid: bool, errors: array<int, string>, warnings: array<int, string>, diff: array<int, array<string, mixed>>}  $result
     */
    private function applyValidationResult(array $result): void
    {
        $this->validationErrors = $result['errors'];
        $this->validationWarnings = $result['warnings'];
        $this->diff = $result['diff'];
    }

    private function resetValidationState(): void
    {
        $this->diff = [];
        $this->validationErrors = [];
        $this->validationWarnings = [];
    }
}
