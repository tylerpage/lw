<?php

namespace App\Filament\Resources\Concerns;

use App\ContentAssistant\Enums\SeoGenerationMode;
use App\ContentAssistant\Services\SeoGenerationService;
use App\Models\Page;
use App\Models\Post;
use App\Models\Project;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;

trait InteractsWithAiSeoGeneration
{
    public function generateSeo(string $mode): void
    {
        $record = $this->resolveSeoGenerationRecord();
        $generationMode = SeoGenerationMode::from($mode);
        $suggestions = app(SeoGenerationService::class)->generate($record, $generationMode);

        if ($suggestions === []) {
            Notification::make()
                ->title('No SEO suggestions generated')
                ->warning()
                ->send();

            return;
        }

        foreach ($suggestions as $field => $value) {
            if ($value !== null) {
                data_set($this->data, $field, $value);
            }
        }

        Notification::make()
            ->title($this->seoGenerationNotificationTitle($generationMode))
            ->body('Review the SEO tab and save when ready. Nothing publishes automatically.')
            ->success()
            ->send();
    }

    private function resolveSeoGenerationRecord(): Page|Post|Project
    {
        $record = $this->getRecord();

        if ($record instanceof Page || $record instanceof Post || $record instanceof Project) {
            $record->fill($this->data ?? []);

            return $record;
        }

        /** @var class-string<Model> $modelClass */
        $modelClass = static::getResource()::getModel();

        /** @var Page|Post|Project $draft */
        $draft = new $modelClass;
        $draft->fill($this->data ?? []);

        return $draft;
    }

    private function seoGenerationNotificationTitle(SeoGenerationMode $mode): string
    {
        return match ($mode) {
            SeoGenerationMode::FromContent => 'SEO generated from content',
            SeoGenerationMode::MetaOnly => 'Meta title and description generated',
            SeoGenerationMode::OpenGraphOnly => 'Open Graph fields generated',
            SeoGenerationMode::ImproveExisting => 'Existing SEO improved',
        };
    }
}
