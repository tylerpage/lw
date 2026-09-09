<?php

namespace App\Events\ContentAssistant;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AssistantProposalReady implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @param  array{valid: bool, errors: array<int, string>, warnings: array<int, string>, diff: array<int, array<string, mixed>>}  $validation
     */
    public function __construct(
        public int $userId,
        public int $conversationId,
        public int $proposalId,
        public array $validation,
    ) {}

    /**
     * @return array<int, PrivateChannel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('content-assistant.'.$this->userId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'assistant.proposal.ready';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'conversation_id' => $this->conversationId,
            'proposal_id' => $this->proposalId,
            'valid' => $this->validation['valid'],
            'errors' => $this->validation['errors'],
            'warnings' => $this->validation['warnings'],
            'diff' => $this->validation['diff'],
        ];
    }
}
