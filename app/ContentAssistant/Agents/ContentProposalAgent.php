<?php

namespace App\ContentAssistant\Agents;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Messages\Message;
use Laravel\Ai\Promptable;
use Stringable;

class ContentProposalAgent implements Agent, Conversational, HasStructuredOutput
{
    use Promptable;

    /**
     * @param  array<int, Message>  $conversationMessages
     */
    public function __construct(
        private string $systemInstructions,
        private array $conversationMessages = [],
    ) {}

    public function instructions(): Stringable|string
    {
        return $this->systemInstructions;
    }

    /**
     * @return array<int, Message>
     */
    public function messages(): iterable
    {
        return $this->conversationMessages;
    }

    public function schema(JsonSchema $schema): array
    {
        $operation = $schema->object([
            'op' => $schema->string()->required(),
            'field' => $schema->string(),
            'value' => $schema->string(),
            'block_index' => $schema->integer(),
            'block_id' => $schema->string(),
            'fields' => $schema->object([]),
            'preserve' => $schema->array()->items($schema->string()),
            'block' => $schema->object([]),
            'to_index' => $schema->integer(),
            'slug' => $schema->string(),
        ]);

        return [
            'summary' => $schema->string()->required(),
            'assistant_message' => $schema->string()->required(),
            'operations' => $schema->array()->items($operation)->required(),
            'warnings' => $schema->array()->items($schema->string()),
            'unverified_claims' => $schema->array()->items($schema->string()),
            'sources' => $schema->array()->items($schema->object([
                'id' => $schema->integer(),
                'title' => $schema->string(),
                'excerpt' => $schema->string(),
            ])),
        ];
    }
}
