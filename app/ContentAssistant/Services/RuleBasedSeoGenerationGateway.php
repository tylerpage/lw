<?php

namespace App\ContentAssistant\Services;

use App\ContentAssistant\Contracts\SeoGenerationGateway;
use App\ContentAssistant\Enums\SeoGenerationMode;
use App\ContentAssistant\Support\ContentTargetResolver;
use App\Models\Page;
use App\Models\Post;
use App\Models\Project;
use Illuminate\Support\Str;

class RuleBasedSeoGenerationGateway implements SeoGenerationGateway
{
    /**
     * @return array<string, string|null>
     */
    public function generate(Page|Post|Project $record, SeoGenerationMode $mode): array
    {
        $title = $this->cleanText($record->title ?? '');
        $summary = $this->extractSummary($record);
        $seoTitle = $this->limit($title, 60);
        $seoDescription = $this->limit($summary ?: $this->fallbackDescription($record), 160);

        if ($mode === SeoGenerationMode::ImproveExisting) {
            $seoTitle = $this->limit($this->cleanText($record->seo_title ?: $seoTitle), 60);
            $seoDescription = $this->limit($this->cleanText($record->seo_description ?: $seoDescription), 160);
        }

        $suggestions = match ($mode) {
            SeoGenerationMode::MetaOnly => [
                'seo_title' => $seoTitle,
                'seo_description' => $seoDescription,
            ],
            SeoGenerationMode::OpenGraphOnly => [
                'og_title' => $this->cleanText($record->seo_title ?: $record->og_title ?: $seoTitle),
                'og_description' => $this->cleanText($record->seo_description ?: $record->og_description ?: $seoDescription),
            ],
            SeoGenerationMode::ImproveExisting => [
                'seo_title' => $seoTitle,
                'seo_description' => $seoDescription,
                'og_title' => $this->cleanText($record->og_title ?: $seoTitle),
                'og_description' => $this->cleanText($record->og_description ?: $seoDescription),
            ],
            SeoGenerationMode::FromContent => [
                'seo_title' => $seoTitle,
                'seo_description' => $seoDescription,
                'og_title' => $seoTitle,
                'og_description' => $seoDescription,
                'canonical_url' => $record->canonical_url ?: ContentTargetResolver::publicUrl($record),
            ],
        };

        return collect($suggestions)
            ->map(fn (?string $value): ?string => filled($value) ? $value : null)
            ->all();
    }

    private function extractSummary(Page|Post|Project $record): string
    {
        return match ($record::class) {
            Post::class => $this->cleanText($record->excerpt ?: $this->extractTextFromBlocks($record->body ?? [])),
            Project::class => $this->cleanText($record->card_summary ?: $this->extractTextFromBlocks($record->blocks ?? [])),
            Page::class => $this->extractTextFromBlocks($record->blocks ?? []),
        };
    }

    /**
     * @param  array<int, array<string, mixed>>  $blocks
     */
    private function extractTextFromBlocks(array $blocks): string
    {
        $chunks = [];

        foreach ($blocks as $block) {
            if (($block['enabled'] ?? true) === false) {
                continue;
            }

            foreach (['headline', 'subheadline', 'content', 'body', 'heading', 'quote', 'status_line', 'cta_label'] as $field) {
                if (! empty($block[$field]) && is_string($block[$field])) {
                    $chunks[] = $block[$field];
                }
            }

            if (! empty($block['items']) && is_array($block['items'])) {
                foreach ($block['items'] as $item) {
                    if (is_array($item)) {
                        foreach (['question', 'answer', 'title', 'body'] as $field) {
                            if (! empty($item[$field]) && is_string($item[$field])) {
                                $chunks[] = $item[$field];
                            }
                        }
                    }
                }
            }
        }

        return $this->cleanText(implode(' ', $chunks));
    }

    private function fallbackDescription(Page|Post|Project $record): string
    {
        $siteBio = (string) config('app.name');

        return match ($record::class) {
            Post::class => "Read {$this->cleanText($record->title)} on {$siteBio}.",
            Project::class => "Case study: {$this->cleanText($record->title)}.",
            default => "Learn more about {$this->cleanText($record->title)}.",
        };
    }

    private function cleanText(?string $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        return str($value)
            ->replace('[DRAFT] ', '')
            ->replace('[DRAFT]', '')
            ->squish()
            ->toString();
    }

    private function limit(string $value, int $length): string
    {
        return Str::limit($value, $length, '…');
    }
}
