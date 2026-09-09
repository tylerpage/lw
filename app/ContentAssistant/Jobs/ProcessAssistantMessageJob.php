<?php

namespace App\ContentAssistant\Jobs;

use App\ContentAssistant\Services\ContentAssistantOrchestrator;
use App\Events\ContentAssistant\AssistantMessageFailed;
use App\Events\ContentAssistant\AssistantMessageProcessing;
use App\Events\ContentAssistant\AssistantProposalReady;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class ProcessAssistantMessageJob implements ShouldQueue
{
    use Queueable;

    /**
     * @param  array<int, array{path: string, url: string, original_name?: string, mime_type?: string, size?: int}>  $attachments
     */
    public function __construct(
        public int $conversationId,
        public int $userId,
        public string $idempotencyKey,
        public array $attachments = [],
    ) {}

    public function handle(ContentAssistantOrchestrator $orchestrator): void
    {
        $user = User::query()->findOrFail($this->userId);

        event(new AssistantMessageProcessing($this->userId, $this->conversationId));

        try {
            $proposal = $orchestrator->generateProposal(
                conversationId: $this->conversationId,
                user: $user,
                idempotencyKey: $this->idempotencyKey,
                attachments: $this->attachments,
            );

            $validation = $orchestrator->validateProposal($proposal->fresh('operations'), $user);

            event(new AssistantProposalReady(
                userId: $this->userId,
                conversationId: $this->conversationId,
                proposalId: $proposal->id,
                validation: $validation,
            ));
        } catch (Throwable $exception) {
            event(new AssistantMessageFailed(
                userId: $this->userId,
                conversationId: $this->conversationId,
                message: $exception->getMessage() !== ''
                    ? $exception->getMessage()
                    : 'The assistant could not generate a proposal.',
            ));

            throw $exception;
        }
    }
}
