<?php

namespace App\Filament\Resources\Concerns;

use App\ContentAssistant\Enums\ContentImportMode;
use App\ContentAssistant\Services\ContentImportService;
use App\ContentAssistant\Services\ContentWorkshopContextBuilder;
use App\ContentAssistant\Support\ContentTargetResolver;
use App\Enums\PublishStatus;
use App\Support\PreviewUrl;
use Filament\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Js;

trait InteractsWithContentWorkshop
{
    /**
     * @return array<int, Action>
     */
    protected function getContentWorkshopHeaderActions(): array
    {
        return [
            Action::make('preview')
                ->label('Preview')
                ->icon('heroicon-o-eye')
                ->url(fn (): string => PreviewUrl::for($this->getRecord()))
                ->openUrlInNewTab(),
            Action::make('viewOnSite')
                ->label('View on site')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->url(fn (): ?string => ContentTargetResolver::publicUrl($this->getRecord()))
                ->openUrlInNewTab()
                ->visible(fn (): bool => $this->getRecord()->status === PublishStatus::Published),
            Action::make('copyAiContext')
                ->label('Copy AI context')
                ->icon('heroicon-o-clipboard-document')
                ->action(function (): void {
                    $markdown = app(ContentWorkshopContextBuilder::class)->build($this->getRecord());

                    $this->js('navigator.clipboard.writeText('.Js::from($markdown).')');

                    Notification::make()
                        ->title('AI context copied')
                        ->body('Paste this into ChatGPT to workshop content.')
                        ->success()
                        ->send();
                }),
            Action::make('downloadAiContext')
                ->label('Download context')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(function () {
                    $markdown = app(ContentWorkshopContextBuilder::class)->build($this->getRecord());
                    $filename = str($this->getRecord()->slug)->slug().'-ai-context.md';

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
                ->modalDescription('Paste Import Content JSON from your AI workshop session. Content is always saved as a draft.')
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
                        ->content(new HtmlString('Requires <code>import_version: 1</code>, matching <code>content_type</code>, a <code>summary</code>, and a flat <code>blocks</code> array. Use <strong>Validate</strong> before applying.')),
                ])
                ->extraModalFooterActions([
                    Action::make('validateImport')
                        ->label('Validate')
                        ->color('gray')
                        ->action(function (array $data): void {
                            $this->notifyImportValidation($data);
                        }),
                ])
                ->action(function (array $data): void {
                    $service = app(ContentImportService::class);
                    $mode = ContentImportMode::from($data['mode']);
                    $result = $service->validatePayload($this->getRecord(), $data['payload'], $mode);

                    if ($result['errors'] !== []) {
                        Notification::make()
                            ->title('Import validation failed')
                            ->body(implode("\n", $result['errors']))
                            ->danger()
                            ->persistent()
                            ->send();

                        return;
                    }

                    $service->apply($this->getRecord(), $result['data'], auth()->user(), $mode);

                    $this->fillForm();

                    $body = $result['data']->summary;

                    if ($result['warnings'] !== []) {
                        $body .= "\n\nWarnings:\n- ".implode("\n- ", $result['warnings']);
                    }

                    Notification::make()
                        ->title('Import applied as draft')
                        ->body($body)
                        ->success()
                        ->send();
                }),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function notifyImportValidation(array $data): void
    {
        $service = app(ContentImportService::class);
        $mode = ContentImportMode::from($data['mode']);
        $result = $service->validatePayload($this->getRecord(), $data['payload'], $mode);

        if ($result['errors'] !== []) {
            Notification::make()
                ->title('Validation failed')
                ->body(implode("\n", $result['errors']))
                ->danger()
                ->persistent()
                ->send();

            return;
        }

        $lines = [
            $result['data']->summary,
            '',
            'Blocks: '.count($result['data']->blocks),
            'Types: '.implode(', ', collect($result['data']->blocks)->pluck('type')->all()),
        ];

        foreach ($result['diff'] as $diff) {
            if ($diff['type'] === 'blocks') {
                $lines[] = "Block count: {$diff['before_count']} → {$diff['after_count']}";
            }

            if ($diff['type'] === 'field') {
                $lines[] = "Field {$diff['field']}: ".json_encode($diff['before']).' → '.json_encode($diff['after']);
            }
        }

        if ($result['warnings'] !== []) {
            $lines[] = '';
            $lines[] = 'Warnings:';
            foreach ($result['warnings'] as $warning) {
                $lines[] = '- '.$warning;
            }
        }

        Notification::make()
            ->title('Import is valid')
            ->body(implode("\n", $lines))
            ->success()
            ->persistent()
            ->send();
    }
}
