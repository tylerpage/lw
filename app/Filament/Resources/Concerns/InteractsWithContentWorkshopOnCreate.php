<?php

namespace App\Filament\Resources\Concerns;

use App\ContentAssistant\Enums\ContentImportMode;
use App\ContentAssistant\Services\ContentImportService;
use App\ContentAssistant\Services\ContentWorkshopContextBuilder;
use Filament\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Js;

trait InteractsWithContentWorkshopOnCreate
{
    use InteractsWithContentAssistant;

    /**
     * @return array<int, Action>
     */
    protected function getContentWorkshopCreateHeaderActions(): array
    {
        return [
            $this->openInContentAssistantFromCreateAction(),
            Action::make('copyAiContext')
                ->label('Copy AI context')
                ->icon('heroicon-o-clipboard-document')
                ->action(function (): void {
                    $markdown = app(ContentWorkshopContextBuilder::class)->buildForDraft(
                        $this->contentWorkshopTargetType(),
                        $this->form->getState(),
                    );

                    $this->js('navigator.clipboard.writeText('.Js::from($markdown).')');

                    Notification::make()
                        ->title('AI context copied')
                        ->body('Paste this into ChatGPT or open the AI Assistant to workshop content here.')
                        ->success()
                        ->send();
                }),
            Action::make('downloadAiContext')
                ->label('Download context')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(function () {
                    $state = $this->form->getState();
                    $markdown = app(ContentWorkshopContextBuilder::class)->buildForDraft(
                        $this->contentWorkshopTargetType(),
                        $state,
                    );
                    $slug = str($state['slug'] ?? 'new-content')->slug();
                    $filename = $slug.'-ai-context.md';

                    return response()->streamDownload(
                        fn () => print ($markdown),
                        $filename,
                        ['Content-Type' => 'text/markdown'],
                    );
                }),
            Action::make('importContent')
                ->label('Import content')
                ->icon('heroicon-o-arrow-down-on-square')
                ->modalHeading('Import content from ChatGPT')
                ->modalDescription('Save a draft first, then paste Import Content JSON from your AI workshop session.')
                ->modalWidth('3xl')
                ->form([
                    Textarea::make('payload')
                        ->label('Import JSON')
                        ->rows(14)
                        ->required()
                        ->columnSpanFull(),
                    Select::make('mode')
                        ->label('Apply mode')
                        ->options([
                            ContentImportMode::Replace->value => 'Replace all blocks',
                            ContentImportMode::Append->value => 'Append blocks to existing content',
                            ContentImportMode::BlocksOnly->value => 'Replace blocks only (keep general/SEO fields)',
                        ])
                        ->default(ContentImportMode::Replace->value)
                        ->required(),
                    Placeholder::make('import_help')
                        ->label('Schema')
                        ->content(new HtmlString('Requires <code>import_version: 1</code>, matching <code>content_type</code>, a <code>summary</code>, and a flat <code>blocks</code> array.')),
                ])
                ->action(function (array $data): void {
                    $record = $this->createDraftForAssistant();
                    $service = app(ContentImportService::class);
                    $mode = ContentImportMode::from($data['mode']);
                    $result = $service->validatePayload($record, $data['payload'], $mode);

                    if ($result['errors'] !== []) {
                        Notification::make()
                            ->title('Import validation failed')
                            ->body(implode("\n", $result['errors']))
                            ->danger()
                            ->persistent()
                            ->send();

                        return;
                    }

                    $service->apply($record, $result['data'], auth()->user(), $mode);

                    $this->redirect(static::getResource()::getUrl('edit', ['record' => $record]));
                }),
        ];
    }
}
