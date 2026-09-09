<?php

namespace App\ContentAssistant\DTO;

use App\Enums\ContentTargetType;
use App\Models\AiConversation;
use Illuminate\Database\Eloquent\Model;

readonly class ContentAssistantRequest
{
    /**
     * @param  array<int, array{role: string, content: string, attachments?: array<int, array<string, mixed>>}>  $messages
     * @param  array<int, array<string, mixed>>  $approvedSources
     * @param  array<int, array{path: string, url: string, original_name?: string, mime_type?: string, size?: int}>  $latestAttachments
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
        public array $latestAttachments = [],
    ) {}
}
