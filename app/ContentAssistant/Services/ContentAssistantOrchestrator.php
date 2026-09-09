<?php

namespace App\ContentAssistant\Services;

use App\ContentAssistant\Contracts\ContentAssistantGateway;
use App\ContentAssistant\DTO\ContentAssistantRequest;
use App\ContentAssistant\DTO\ContentProposalData;
use App\ContentAssistant\Jobs\ProcessAssistantMessageJob;
use App\ContentAssistant\Support\ContentRevisionTracker;
use App\ContentAssistant\Support\ContentTargetResolver;
use App\Enums\AiConversationStatus;
use App\Enums\AiMessageRole;
use App\Enums\ContentProposalStatus;
use App\Enums\ContentTargetType;
use App\Models\AiConversation;
use App\Models\AiMessage;
use App\Models\ContentAssistantAuditEvent;
use App\Models\ContentProposal;
use App\Models\ContentProposalOperation;
use App\Models\User;
use App\Support\PreviewUrl;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ContentAssistantOrchestrator
{
    public function __construct(
        private ContentAssistantGateway $gateway,
        private ContentContextBuilder $contextBuilder,
        private ProposalSchemaValidator $schemaValidator,
        private ProposalPolicyValidator $policyValidator,
        private ContentClaimValidator $claimValidator,
        private ProposalDiffBuilder $diffBuilder,
        private ApplyProposalToDraft $applyProposalToDraft,
    ) {}

    public function startConversation(User $user, ContentTargetType $targetType, int $targetId, ?string $title = null): AiConversation
    {
        $target = ContentTargetResolver::find($targetType, $targetId);

        abort_if(! $target, 404);
        abort_unless($user->can('update', $target), 403);

        $conversation = AiConversation::query()->create([
            'user_id' => $user->id,
            'title' => $title ?: ContentTargetResolver::label($target),
            'target_type' => $targetType,
            'target_id' => $targetId,
            'status' => AiConversationStatus::Active,
            'last_activity_at' => now(),
        ]);

        ContentAssistantAuditEvent::record('assistant_conversation_started', $user, $conversation, [
            'target_type' => $targetType->value,
            'target_id' => $targetId,
        ]);

        return $conversation;
    }

    /**
     * @param  array<int, array{path: string, url: string, original_name?: string, mime_type?: string, size?: int}>  $attachments
     */
    public function sendMessage(
        AiConversation $conversation,
        User $user,
        string $message,
        ?string $idempotencyKey = null,
        array $attachments = [],
    ): ?ContentProposal {
        abort_unless($conversation->user_id === $user->id, 403);

        $target = $conversation->target_type && $conversation->target_id
            ? ContentTargetResolver::find($conversation->target_type, $conversation->target_id)
            : null;

        abort_if(! $target, 422, 'Conversation has no content target.');

        $message = trim($message);

        abort_if($message === '' && $attachments === [], 422, 'Provide a message or at least one image.');

        if ($idempotencyKey) {
            $existing = ContentProposal::query()->where('idempotency_key', $idempotencyKey)->first();
            if ($existing) {
                return $existing;
            }
        }

        AiMessage::query()->create([
            'conversation_id' => $conversation->id,
            'role' => AiMessageRole::User,
            'content' => $message !== '' ? $message : '[Image reference attached]',
            'metadata' => $attachments !== [] ? ['attachments' => $attachments] : null,
        ]);

        $conversation->update(['last_activity_at' => now()]);

        $resolvedKey = $idempotencyKey ?? $this->generateIdempotencyKey();

        if (config('content-assistant.async')) {
            ProcessAssistantMessageJob::dispatch(
                conversationId: $conversation->id,
                userId: $user->id,
                idempotencyKey: $resolvedKey,
                attachments: $attachments,
            );

            return null;
        }

        return $this->generateProposal(
            conversationId: $conversation->id,
            user: $user,
            idempotencyKey: $resolvedKey,
            attachments: $attachments,
        );
    }

    /**
     * @param  array<int, array{path: string, url: string, original_name?: string, mime_type?: string, size?: int}>  $attachments
     */
    public function regenerateWithOverride(AiConversation $conversation, User $user): ?ContentProposal
    {
        abort_unless($conversation->user_id === $user->id, 403);

        $target = $conversation->target_type && $conversation->target_id
            ? ContentTargetResolver::find($conversation->target_type, $conversation->target_id)
            : null;

        abort_if(! $target, 422, 'Conversation has no content target.');

        $latestProposal = $conversation->latestProposal;

        if ($latestProposal && in_array($latestProposal->status, [
            ContentProposalStatus::Proposed,
            ContentProposalStatus::NeedsRevision,
        ], true)) {
            $latestProposal->update(['status' => ContentProposalStatus::Rejected]);
        }

        ContentAssistantAuditEvent::record('assistant_override_requested', $user, $conversation, [
            'target_type' => $conversation->target_type?->value,
            'target_id' => $conversation->target_id,
        ]);

        $latestUser = $conversation->messages()
            ->where('role', AiMessageRole::User)
            ->latest()
            ->first();

        abort_if(! $latestUser, 422, 'No user message found to retry.');

        $attachments = $latestUser->attachments();
        $idempotencyKey = $this->generateIdempotencyKey();

        if (config('content-assistant.async')) {
            ProcessAssistantMessageJob::dispatch(
                conversationId: $conversation->id,
                userId: $user->id,
                idempotencyKey: $idempotencyKey,
                attachments: $attachments,
                overrideGuardrails: true,
            );

            return null;
        }

        return $this->generateProposal(
            conversationId: $conversation->id,
            user: $user,
            idempotencyKey: $idempotencyKey,
            attachments: $attachments,
            overrideGuardrails: true,
        );
    }

    public function generateProposal(
        int $conversationId,
        User $user,
        ?string $idempotencyKey = null,
        array $attachments = [],
        bool $overrideGuardrails = false,
    ): ContentProposal {
        $conversation = AiConversation::query()->findOrFail($conversationId);
        abort_unless($conversation->user_id === $user->id, 403);

        $target = $conversation->target_type && $conversation->target_id
            ? ContentTargetResolver::find($conversation->target_type, $conversation->target_id)
            : null;

        abort_if(! $target, 422, 'Conversation has no content target.');

        if ($idempotencyKey) {
            $existing = ContentProposal::query()->where('idempotency_key', $idempotencyKey)->first();
            if ($existing) {
                return $existing;
            }
        }

        $messages = $conversation->messages()->get()->map(fn (AiMessage $item): array => [
            'role' => $item->role->value,
            'content' => $item->content,
            'attachments' => $item->attachments(),
        ])->all();

        $latestUser = collect($messages)->reverse()->first(fn (array $item): bool => ($item['role'] ?? '') === 'user');
        $latestUserMessage = filled($latestUser['content'] ?? null) && ($latestUser['content'] ?? '') !== '[Image reference attached]'
            ? (string) $latestUser['content']
            : 'Review the attached image(s) and suggest relevant content updates.';

        if ($attachments === [] && is_array($latestUser)) {
            $attachments = $latestUser['attachments'] ?? [];
        }

        $context = array_merge(
            $this->contextBuilder->build($target),
            ['latest_attachments' => $attachments],
        );

        $request = new ContentAssistantRequest(
            conversation: $conversation,
            target: $target,
            targetType: $conversation->target_type,
            messages: $messages,
            latestUserMessage: $latestUserMessage,
            context: $context,
            approvedSources: $this->contextBuilder->approvedSourcesFor($conversation->target_type),
            assistantInstructions: $this->contextBuilder->assistantInstructions(),
            latestAttachments: $attachments,
            overrideGuardrails: $overrideGuardrails,
        );

        try {
            $proposalData = $this->gateway->propose($request);
        } catch (\Throwable $exception) {
            ContentAssistantAuditEvent::record('assistant_proposal_failed', $user, $conversation, [
                'error' => class_basename($exception),
            ]);

            throw $exception;
        }

        return $this->persistProposal($conversation, $user, $target, $proposalData, $idempotencyKey);
    }

    private function persistProposal(
        AiConversation $conversation,
        User $user,
        Model $target,
        ContentProposalData $proposalData,
        ?string $idempotencyKey,
    ): ContentProposal {
        if ($proposalData->assistantMessage) {
            AiMessage::query()->create([
                'conversation_id' => $conversation->id,
                'role' => AiMessageRole::Assistant,
                'content' => $proposalData->assistantMessage,
            ]);
        }

        $payload = $proposalData->toPayload(
            targetId: $target->getKey(),
            expectedRevision: ContentRevisionTracker::revisionNumber($target),
            targetType: $conversation->target_type->value,
        );

        $proposal = DB::transaction(function () use ($conversation, $user, $proposalData, $payload, $target, $idempotencyKey) {
            $proposal = ContentProposal::query()->create([
                'conversation_id' => $conversation->id,
                'user_id' => $user->id,
                'target_type' => $conversation->target_type,
                'target_id' => $target->getKey(),
                'expected_revision' => ContentRevisionTracker::revisionNumber($target),
                'content_hash' => ContentRevisionTracker::hash($target),
                'status' => ContentProposalStatus::Proposed,
                'summary' => $proposalData->summary,
                'payload' => $payload,
                'sources' => $proposalData->sources,
                'warnings' => $proposalData->warnings,
                'unverified_claims' => $proposalData->unverifiedClaims,
                'idempotency_key' => $idempotencyKey,
            ]);

            foreach ($proposalData->operations as $index => $operation) {
                ContentProposalOperation::query()->create([
                    'proposal_id' => $proposal->id,
                    'sort_order' => $index,
                    'operation' => $operation,
                ]);
            }

            $conversation->update(['last_activity_at' => now()]);

            return $proposal;
        });

        ContentAssistantAuditEvent::record('assistant_proposal_generated', $user, $proposal);

        return $proposal->load('operations');
    }

    /**
     * @return array{valid: bool, errors: array<int, string>, warnings: array<int, string>, diff: array<int, array<string, mixed>>}
     */
    public function validateProposal(ContentProposal $proposal, User $user): array
    {
        $target = $proposal->target();
        abort_if(! $target, 422);
        abort_unless($user->can('update', $target), 403);

        if ($proposal->isStale()) {
            $proposal->update(['status' => ContentProposalStatus::Stale]);

            return [
                'valid' => false,
                'errors' => ['The underlying content changed after this proposal was generated.'],
                'warnings' => [],
                'diff' => [],
            ];
        }

        $errors = array_merge(
            $this->policyValidator->validate($user, $target),
            $this->schemaValidator->validate($target, $proposal->payload),
        );

        $claims = $this->claimValidator->validate($proposal->payload);
        $warnings = array_merge($proposal->warnings ?? [], $claims);

        $valid = $errors === [];

        $proposal->update([
            'status' => $valid ? ContentProposalStatus::Validated : ContentProposalStatus::NeedsRevision,
            'validation_errors' => $errors,
            'unverified_claims' => $claims,
            'warnings' => $warnings,
        ]);

        return [
            'valid' => $valid,
            'errors' => $errors,
            'warnings' => $warnings,
            'diff' => $this->diffBuilder->build($target, $proposal->payload),
        ];
    }

    public function applyDraft(ContentProposal $proposal, User $user): ContentProposal
    {
        abort_unless($user->can('applyDraft', $proposal), 403);

        $applied = $this->applyProposalToDraft->apply($proposal, $user);

        ContentAssistantAuditEvent::record('assistant_draft_saved', $user, $applied);

        return $applied;
    }

    public function approveAndPublish(ContentProposal $proposal, User $user): ContentProposal
    {
        abort_unless($user->can('publish', $proposal), 403);

        $applied = $this->applyProposalToDraft->apply($proposal, $user, publish: true);

        ContentAssistantAuditEvent::record('assistant_proposal_approved', $user, $applied);
        ContentAssistantAuditEvent::record('assistant_proposal_published', $user, $applied);

        return $applied;
    }

    public function previewUrl(ContentProposal $proposal, User $user): string
    {
        $target = $proposal->target();
        abort_if(! $target, 422);
        abort_unless($user->can('view', $target), 403);

        ContentAssistantAuditEvent::record('assistant_preview_opened', $user, $proposal);

        return PreviewUrl::for($target);
    }

    public function rejectProposal(ContentProposal $proposal, User $user): ContentProposal
    {
        abort_unless($user->can('update', $proposal->target()), 403);

        $proposal->update(['status' => ContentProposalStatus::Rejected]);
        ContentAssistantAuditEvent::record('assistant_proposal_rejected', $user, $proposal);

        return $proposal;
    }

    public function generateIdempotencyKey(): string
    {
        return Str::uuid()->toString();
    }
}
