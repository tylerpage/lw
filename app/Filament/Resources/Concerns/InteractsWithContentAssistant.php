<?php

namespace App\Filament\Resources\Concerns;

use App\ContentAssistant\Support\ContentTargetResolver;
use App\Enums\ContentTargetType;
use App\Enums\PublishStatus;
use App\Filament\Pages\ContentAssistant;
use App\Models\Page;
use App\Models\Post;
use App\Models\Project;
use Filament\Actions\Action;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

trait InteractsWithContentAssistant
{
    protected function openInContentAssistantAction(): Action
    {
        return Action::make('openInContentAssistant')
            ->label('AI Assistant')
            ->icon('heroicon-o-sparkles')
            ->color('primary')
            ->url(fn (): string => $this->contentAssistantUrlForRecord($this->getRecord()));
    }

    protected function openInContentAssistantFromCreateAction(): Action
    {
        return Action::make('openInContentAssistant')
            ->label('AI Assistant')
            ->icon('heroicon-o-sparkles')
            ->color('primary')
            ->modalHeading('Continue in AI Assistant?')
            ->modalDescription('Your form will be saved as a draft first. You can describe content changes in plain language, review proposals, then return here to finish editing.')
            ->action(function (): void {
                $record = $this->createDraftForAssistant();

                $this->redirect($this->contentAssistantUrlForRecord($record));
            });
    }

    protected function contentAssistantUrlForRecord(Page|Post|Project $record): string
    {
        return ContentAssistant::getUrl([
            'targetType' => ContentTargetResolver::fromModel($record)->value,
            'targetId' => $record->getKey(),
            'start' => true,
            'returnUrl' => $this->editorReturnUrl($record),
        ]);
    }

    protected function editorReturnUrl(Page|Post|Project $record): string
    {
        return static::getResource()::getUrl('edit', ['record' => $record]);
    }

    protected function createDraftForAssistant(): Page|Post|Project
    {
        $data = $this->form->getState();
        $data['status'] = PublishStatus::Draft->value;
        $data = $this->normalizeDraftFormData($data);

        /** @var class-string<Model> $modelClass */
        $modelClass = static::getResource()::getModel();

        /** @var Page|Post|Project $record */
        $record = $modelClass::query()->create($data);

        return $record;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function normalizeDraftFormData(array $data): array
    {
        $targetType = $this->contentWorkshopTargetType();

        $title = trim((string) ($data['title'] ?? ''));

        if ($title === '') {
            $title = match ($targetType) {
                ContentTargetType::Post => 'Untitled post',
                ContentTargetType::Project => 'Untitled case study',
                ContentTargetType::Page => 'Untitled page',
            };
        }

        $data['title'] = $title;

        $slug = trim((string) ($data['slug'] ?? ''));

        if ($slug === '') {
            $slug = Str::slug($title).'-'.Str::lower(Str::random(4));
        }

        $data['slug'] = $this->uniqueSlug($targetType, $slug);

        if ($targetType === ContentTargetType::Page && blank($data['template'] ?? null)) {
            $data['template'] = 'default';
        }

        if ($targetType === ContentTargetType::Project && ! isset($data['sort_order'])) {
            $data['sort_order'] = 0;
        }

        return $data;
    }

    protected function uniqueSlug(ContentTargetType $targetType, string $slug): string
    {
        $modelClass = $targetType->modelClass();
        $candidate = $slug;
        $suffix = 2;

        while ($modelClass::query()->where('slug', $candidate)->exists()) {
            $candidate = $slug.'-'.$suffix;
            $suffix++;
        }

        return $candidate;
    }

    abstract protected function contentWorkshopTargetType(): ContentTargetType;
}
