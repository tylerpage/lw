<?php

namespace App\ContentAssistant\DTO;

use App\Enums\ContentTargetType;
use App\Models\AiConversation;
use Illuminate\Database\Eloquent\Model;

readonly class ContentAssistantRequest
{
    /**
     * @param  array<int, array{role: string, content: string}>  $messages
     * @param  array<int, array<string, mixed>>  $approvedSources
     */
    public function __construct(
        public AiConversation $conversation,
        public Model $target,
        public ContentTargetType $targetType,
        public array $messages,
        public string $latestUserMessage,
        public array $context,
        public array $approvedSources,
        public ?string $assistantInstructions,
    ) {}
}
