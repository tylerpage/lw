<?php

namespace App\Filament\Pages;

use App\ContentAssistant\Services\AssistantImageStorage;
use App\ContentAssistant\Services\ContentAssistantOrchestrator;
use App\ContentAssistant\Support\ContentTargetResolver;
use App\ContentAssistant\Support\ProposalDiffPresenter;
use App\Enums\ContentProposalStatus;
use App\Enums\ContentTargetType;
use App\Models\AiConversation;
use App\Models\ContentProposal;
use App\Models\Page;
use App\Models\Post;
use App\Models\Project;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page as FilamentPage;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Url;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class ContentAssistant extends FilamentPage
{
    use WithFileUploads;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

    protected static ?string $navigationLabel = 'AI Content Assistant';

    protected static ?string $title = 'AI Content Assistant';

    protected ?string $subheading = 'Describe content changes in plain language, review the proposal, then save a draft or approve to publish.';

    protected static ?int $navigationSort = 2;

    protected Width|string|null $maxContentWidth = Width::Full;

    protected string $view = 'filament.pages.content-assistant';

    public ?int $conversationId = null;

    #[Url]
    public string $targetType = 'page';

    #[Url]
    public ?int $targetId = null;

    public string $message = '';

    public bool $isProcessing = false;

    public string $processingStatus = 'Generating proposal…';

    public ?string $processingStartedAt = null;

    /** @var array<int, TemporaryUploadedFile> */
    public array $attachments = [];

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
        if (! $this->targetId) {
            $home = Page::query()->where('slug', 'home')->first();
            $this->targetType = ContentTargetType::Page->value;
            $this->targetId = $home?->id;
        }

        if ($this->targetId && request()->boolean('start')) {
            $this->startConversation(app(ContentAssistantOrchestrator::class));
        }
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

    public function getTargetTypeEnumProperty(): ContentTargetType
    {
        return ContentTargetType::tryFrom($this->targetType) ?? ContentTargetType::Page;
    }

    /**
     * @return array<int, string>
     */
    public function getTargetOptionsProperty(): array
    {
        return match ($this->targetTypeEnum) {
            ContentTargetType::Page => Page::query()->orderBy('title')->pluck('title', 'id')->all(),
            ContentTargetType::Post => Post::query()->orderBy('title')->pluck('title', 'id')->all(),
            ContentTargetType::Project => Project::query()->orderBy('title')->pluck('title', 'id')->all(),
        };
    }

    public function updatedTargetType(): void
    {
        $this->targetId = array_key_first($this->targetOptions) ?: null;
        $this->conversationId = null;
        $this->resetValidationState();
        $this->isProcessing = false;
    }

    public function startConversation(ContentAssistantOrchestrator $orchestrator): void
    {
        abort_if(! $this->targetId, 422, 'Select content to edit first.');

        $conversation = $orchestrator->startConversation(
            auth()->user(),
            $this->targetTypeEnum,
            $this->targetId,
        );

        $this->conversationId = $conversation->id;
        $this->resetValidationState();
        $this->isProcessing = false;

        Notification::make()->title('Conversation started')->success()->send();
    }

    public function sendMessage(ContentAssistantOrchestrator $orchestrator, AssistantImageStorage $imageStorage): void
    {
        $maxFiles = config('content-assistant.attachments.max_files_per_message', 5);
        $maxSizeKb = config('content-assistant.attachments.max_file_size_kb', 5120);

        $this->validate([
            'message' => ['nullable', 'string', 'max:5000'],
            'attachments' => ['array', 'max:'.$maxFiles],
            'attachments.*' => ['image', 'max:'.$maxSizeKb],
        ]);

        if (trim($this->message) === '' && $this->attachments === []) {
            throw ValidationException::withMessages([
                'message' => 'Add a message or attach at least one image.',
            ]);
        }

        if (! $this->conversationId) {
            $this->startConversation($orchestrator);
        }

        $conversation = AiConversation::query()
            ->where('user_id', auth()->id())
            ->findOrFail($this->conversationId);

        $storedAttachments = $imageStorage->storeMany($this->attachments, $conversation->id);

        $proposal = $orchestrator->sendMessage(
            $conversation,
            auth()->user(),
            trim($this->message),
            $orchestrator->generateIdempotencyKey(),
            $storedAttachments,
        );

        $this->message = '';
        $this->attachments = [];
        $this->resetValidationState();
        $this->conversationId = $conversation->id;

        if ($proposal === null) {
            $this->isProcessing = true;
            $this->processingStatus = 'Generating proposal…';
            $this->processingStartedAt = now()->toIso8601String();

            Notification::make()
                ->title('Message sent')
                ->body('The assistant is working on your proposal.')
                ->success()
                ->send();

            return;
        }

        $result = $orchestrator->validateProposal($proposal->fresh('operations'), auth()->user());
        $this->applyValidationResult($result);

        Notification::make()
            ->title($result['valid'] ? 'Proposal ready for review' : 'Proposal needs revision')
            ->{$result['valid'] ? 'success' : 'warning'}()
            ->send();
    }

    public function pollForProposal(ContentAssistantOrchestrator $orchestrator): void
    {
        if (! $this->isProcessing || ! $this->conversationId || ! $this->processingStartedAt) {
            return;
        }

        $proposal = ContentProposal::query()
            ->where('conversation_id', $this->conversationId)
            ->where('created_at', '>=', $this->processingStartedAt)
            ->latest()
            ->first();

        if (! $proposal || $proposal->status === ContentProposalStatus::Proposed) {
            return;
        }

        $this->finishProcessing($orchestrator, $proposal);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function handleAssistantBroadcast(array $payload): void
    {
        if (($payload['conversation_id'] ?? null) !== $this->conversationId) {
            return;
        }

        if (isset($payload['status'])) {
            $this->processingStatus = (string) $payload['status'];

            return;
        }

        if (isset($payload['message']) && ! isset($payload['valid'])) {
            $this->isProcessing = false;
            Notification::make()->title('Assistant failed')->body((string) $payload['message'])->danger()->send();

            return;
        }

        if (isset($payload['valid'])) {
            $this->isProcessing = false;
            $this->applyValidationResult([
                'valid' => (bool) $payload['valid'],
                'errors' => $payload['errors'] ?? [],
                'warnings' => $payload['warnings'] ?? [],
                'diff' => $payload['diff'] ?? [],
            ]);

            Notification::make()
                ->title($payload['valid'] ? 'Proposal ready for review' : 'Proposal needs revision')
                ->{($payload['valid'] ?? false) ? 'success' : 'warning'}()
                ->send();
        }
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

        Notification::make()
            ->title('Draft saved')
            ->body('Your changes are in the editor. The live site is unchanged until you publish.')
            ->success()
            ->send();
    }

    public function approveAndPublish(ContentAssistantOrchestrator $orchestrator): void
    {
        $proposal = $this->latestProposal;
        abort_if(! $proposal, 422);

        if (! $this->canApproveAndPublish()) {
            Notification::make()->title('You cannot publish this proposal.')->danger()->send();

            return;
        }

        $orchestrator->approveAndPublish($proposal, auth()->user());

        Notification::make()
            ->title('Changes published')
            ->body('The approved updates are now live on the public site.')
            ->success()
            ->send();
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

    public function removeAttachment(int $index): void
    {
        unset($this->attachments[$index]);
        $this->attachments = array_values($this->attachments);
    }

    public function selectConversation(int $conversationId): void
    {
        $this->conversationId = $conversationId;
        $this->attachments = [];
        $this->resetValidationState();
        $this->isProcessing = false;

        $conversation = AiConversation::query()->find($conversationId);

        if ($conversation?->target_type && $conversation->target_id) {
            $this->targetType = $conversation->target_type->value;
            $this->targetId = $conversation->target_id;
        }
    }

    public function proposalStatusLabel(): ?string
    {
        return $this->latestProposal?->status->label();
    }

    public function proposalStatusColor(): string
    {
        return match ($this->latestProposal?->status) {
            ContentProposalStatus::Validated,
            ContentProposalStatus::DraftSaved,
            ContentProposalStatus::Approved,
            ContentProposalStatus::Published => 'success',
            ContentProposalStatus::NeedsRevision,
            ContentProposalStatus::Stale => 'warning',
            ContentProposalStatus::Rejected,
            ContentProposalStatus::Failed => 'danger',
            default => 'gray',
        };
    }

    public function hasProposal(): bool
    {
        return $this->latestProposal !== null
            && ($this->latestProposal->payload['operations'] ?? []) !== [];
    }

    public function canSaveDraft(): bool
    {
        return $this->latestProposal?->status === ContentProposalStatus::Validated
            && $this->validationErrors === [];
    }

    public function canApproveAndPublish(): bool
    {
        $proposal = $this->latestProposal;

        if (! $proposal || $this->validationErrors !== []) {
            return false;
        }

        return $proposal->status === ContentProposalStatus::Validated
            && auth()->user()?->can('publish', $proposal);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getFormattedDiffProperty(): array
    {
        return app(ProposalDiffPresenter::class)->present($this->diff);
    }

    public function targetLabel(): ?string
    {
        $conversation = $this->activeConversation;

        if (! $conversation?->target_type || ! $conversation->target_id) {
            if ($this->targetId) {
                $target = ContentTargetResolver::find($this->targetTypeEnum, $this->targetId);

                return $target ? ContentTargetResolver::label($target) : null;
            }

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

    private function finishProcessing(ContentAssistantOrchestrator $orchestrator, ContentProposal $proposal): void
    {
        $result = $orchestrator->validateProposal($proposal->fresh('operations'), auth()->user());
        $this->isProcessing = false;
        $this->applyValidationResult($result);

        Notification::make()
            ->title($result['valid'] ? 'Proposal ready for review' : 'Proposal needs revision')
            ->{$result['valid'] ? 'success' : 'warning'}()
            ->send();
    }

    private function resetValidationState(): void
    {
        $this->diff = [];
        $this->validationErrors = [];
        $this->validationWarnings = [];
    }
}
