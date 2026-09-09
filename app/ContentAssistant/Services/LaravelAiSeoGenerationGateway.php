<?php

namespace App\ContentAssistant\Services;

use App\ContentAssistant\Agents\SeoProposalAgent;
use App\ContentAssistant\Contracts\SeoGenerationGateway;
use App\ContentAssistant\Enums\SeoGenerationMode;
use App\ContentAssistant\Support\ContentTargetResolver;
use App\ContentAssistant\Support\SeoPromptBuilder;
use App\Models\Page;
use App\Models\Post;
use App\Models\Project;
use Illuminate\Support\Str;
use Laravel\Ai\Enums\Lab;
use Laravel\Ai\Exceptions\ProviderConnectionException;
use Laravel\Ai\Responses\StructuredAgentResponse;
use RuntimeException;

class LaravelAiSeoGenerationGateway implements SeoGenerationGateway
{
    public function __construct(
        private SeoPromptBuilder $promptBuilder,
        private RuleBasedSeoGenerationGateway $fallback,
    ) {}

    /**
     * @return array<string, string|null>
     */
    public function generate(Page|Post|Project $record, SeoGenerationMode $mode): array
    {
        $agent = new SeoProposalAgent(
            systemInstructions: $this->promptBuilder->buildInstructions($mode),
        );

        $provider = Lab::tryFrom((string) config('content-assistant.provider', 'openai')) ?? Lab::OpenAI;
        $model = config('content-assistant.seo_model', config('content-assistant.model'));
        $timeout = (int) config('content-assistant.timeout', 60);

        try {
            $response = $agent->prompt(
                prompt: $this->promptBuilder->buildPrompt($record, $mode),
                provider: $provider,
                model: is_string($model) && $model !== '' ? $model : null,
                timeout: $timeout > 0 ? $timeout : null,
            );
        } catch (ProviderConnectionException) {
            return $this->fallback->generate($record, $mode);
        }

        if (! $response instanceof StructuredAgentResponse) {
            throw new RuntimeException('The SEO assistant returned an unexpected response format.');
        }

        return $this->normalizeSuggestions($response->structured, $record, $mode);
    }

    /**
     * @param  array<string, mixed>  $structured
     * @return array<string, string|null>
     */
    private function normalizeSuggestions(array $structured, Page|Post|Project $record, SeoGenerationMode $mode): array
    {
        $clean = fn (?string $value): ?string => filled($value)
            ? Str::limit(str($value)->replace('[DRAFT]', '')->squish()->toString(), 320, '…')
            : null;

        $all = [
            'seo_title' => $clean($structured['seo_title'] ?? null),
            'seo_description' => $clean($structured['seo_description'] ?? null),
            'og_title' => $clean($structured['og_title'] ?? null),
            'og_description' => $clean($structured['og_description'] ?? null),
            'canonical_url' => $clean($structured['canonical_url'] ?? null),
        ];

        if ($all['seo_title']) {
            $all['seo_title'] = Str::limit($all['seo_title'], 60, '…');
        }

        if ($all['seo_description']) {
            $all['seo_description'] = Str::limit($all['seo_description'], 160, '…');
        }

        if ($mode === SeoGenerationMode::FromContent && ! $all['canonical_url']) {
            $all['canonical_url'] = ContentTargetResolver::publicUrl($record);
        }

        $allowedKeys = match ($mode) {
            SeoGenerationMode::MetaOnly => ['seo_title', 'seo_description'],
            SeoGenerationMode::OpenGraphOnly => ['og_title', 'og_description'],
            SeoGenerationMode::ImproveExisting => ['seo_title', 'seo_description', 'og_title', 'og_description'],
            SeoGenerationMode::FromContent => ['seo_title', 'seo_description', 'og_title', 'og_description', 'canonical_url'],
        };

        return collect($all)
            ->only($allowedKeys)
            ->filter(fn (?string $value): bool => filled($value))
            ->all();
    }
}
