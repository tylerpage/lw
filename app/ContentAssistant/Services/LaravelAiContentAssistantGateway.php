<?php

namespace App\ContentAssistant\Services;

use App\ContentAssistant\Agents\ContentProposalAgent;
use App\ContentAssistant\Contracts\ContentAssistantGateway;
use App\ContentAssistant\DTO\ContentAssistantRequest;
use App\ContentAssistant\DTO\ContentProposalData;
use App\ContentAssistant\Support\ContentProposalPromptBuilder;
use App\ContentAssistant\Support\ContentProposalResponseMapper;
use App\Support\MediaLibraryMimeTypes;
use Laravel\Ai\Enums\Lab;
use Laravel\Ai\Exceptions\ProviderConnectionException;
use Laravel\Ai\Files\Image;
use Laravel\Ai\Messages\AssistantMessage;
use Laravel\Ai\Messages\UserMessage;
use Laravel\Ai\Responses\StructuredAgentResponse;
use RuntimeException;

class LaravelAiContentAssistantGateway implements ContentAssistantGateway
{
    public function __construct(
        private ContentProposalPromptBuilder $promptBuilder,
        private ContentProposalResponseMapper $responseMapper,
    ) {}

    public function propose(ContentAssistantRequest $request): ContentProposalData
    {
        $agent = new ContentProposalAgent(
            systemInstructions: $this->promptBuilder->buildInstructions($request),
            conversationMessages: $this->conversationMessages($request),
        );

        $provider = Lab::tryFrom((string) config('content-assistant.provider', 'openai')) ?? Lab::OpenAI;
        $model = config('content-assistant.model');
        $timeout = (int) config('content-assistant.timeout', 60);

        try {
            $response = $agent->prompt(
                prompt: $this->promptBuilder->buildPrompt($request),
                attachments: $this->attachments($request),
                provider: $provider,
                model: is_string($model) && $model !== '' ? $model : null,
                timeout: $timeout > 0 ? $timeout : null,
            );
        } catch (ProviderConnectionException $exception) {
            throw new RuntimeException('The content assistant provider is unavailable. Please try again shortly.', previous: $exception);
        }

        if (! $response instanceof StructuredAgentResponse) {
            throw new RuntimeException('The content assistant returned an unexpected response format.');
        }

        return $this->responseMapper->toProposalData($response->structured);
    }

    /**
     * @return array<int, UserMessage|AssistantMessage>
     */
    private function conversationMessages(ContentAssistantRequest $request): array
    {
        $messages = collect($request->messages);

        if ($messages->isNotEmpty()) {
            $messages = $messages->slice(0, -1);
        }

        return $messages
            ->map(function (array $message): UserMessage|AssistantMessage {
                $content = (string) ($message['content'] ?? '');

                return ($message['role'] ?? '') === 'assistant'
                    ? new AssistantMessage($content)
                    : new UserMessage($content);
            })
            ->values()
            ->all();
    }

    /**
     * @return array<int, Image>
     */
    private function attachments(ContentAssistantRequest $request): array
    {
        $disk = config('content-assistant.attachments.disk', 'public');

        return collect($request->latestAttachments)
            ->filter(fn (array $attachment): bool => filled($attachment['path'] ?? null))
            ->filter(fn (array $attachment): bool => MediaLibraryMimeTypes::isVisionAttachment(
                $attachment['mime_type'] ?? null,
            ))
            ->map(fn (array $attachment): Image => Image::fromStorage((string) $attachment['path'], $disk))
            ->values()
            ->all();
    }
}
