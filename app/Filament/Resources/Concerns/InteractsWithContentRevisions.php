<?php

namespace App\Filament\Resources\Concerns;

use App\ContentAssistant\Services\ContentRevisionService;
use App\Models\PageRevision;
use App\Models\PostRevision;
use App\Support\PreviewUrl;
use Filament\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;

trait InteractsWithContentRevisions
{
    /**
     * @return array<int, Action>
     */
    protected function getContentRevisionToolActions(): array
    {
        return [
            Action::make('versionHistory')
                ->label('Version history')
                ->icon('heroicon-o-clock')
                ->modalHeading('Version history')
                ->modalDescription('Saved snapshots of this content. The live site keeps the published version until you publish your draft changes.')
                ->modalWidth('4xl')
                ->modalContent(fn (): View => view('filament.modals.content-version-history', [
                    'record' => $this->getRecord(),
                    'revisions' => $this->getRecord()->revisions()->with('user')->latest()->limit(50)->get(),
                    'liveRevisionId' => $this->getRecord()->published_revision_id,
                    'hasPendingDraft' => $this->getRecord()->hasPendingDraft(),
                ]))
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Close'),
            Action::make('publishPendingChanges')
                ->label('Publish changes')
                ->icon('heroicon-o-arrow-up-circle')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn (): bool => $this->getRecord()->hasPendingDraft() && $this->canPublishContentChanges())
                ->authorize(fn (): bool => $this->canPublishContentChanges())
                ->modalHeading('Publish changes to the live site?')
                ->modalDescription('This saves your current editor content and makes it visible on the public site.')
                ->action(function (): void {
                    $this->save(shouldRedirect: false, shouldSendSavedNotification: false);

                    $record = $this->getRecord()->refresh();
                    $service = app(ContentRevisionService::class);

                    $service->publishPendingChanges($record, auth()->user());
                    $record->save();

                    $this->fillForm();

                    Notification::make()
                        ->title('Changes published')
                        ->body('The public site now matches the editor.')
                        ->success()
                        ->send();
                }),
            Action::make('discardDraftChanges')
                ->label('Discard draft changes')
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('warning')
                ->requiresConfirmation()
                ->visible(fn (): bool => $this->getRecord()->hasPendingDraft())
                ->modalHeading('Discard unpublished changes?')
                ->modalDescription('This restores the editor to the live on-site version. Saved version history is kept.')
                ->action(function (): void {
                    $record = $this->getRecord();
                    $revision = $record->publishedRevision;

                    abort_if(! $revision, 422, 'No published version is pinned for this record.');

                    $service = app(ContentRevisionService::class);
                    $service->record($record, auth()->user(), 'discard_draft', 'Before discarding draft');
                    $service->applyToModel($record, $revision);
                    $service->clearUnpublishedChanges($record);
                    $record->save();

                    $this->fillForm();

                    Notification::make()
                        ->title('Draft changes discarded')
                        ->body('The editor now matches the live on-site version.')
                        ->success()
                        ->send();
                }),
        ];
    }

    public function restoreContentRevision(int $revisionId): void
    {
        $record = $this->getRecord();
        $revision = $record->revisions()->findOrFail($revisionId);

        abort_unless($revision instanceof PageRevision || $revision instanceof PostRevision, 422);

        $service = app(ContentRevisionService::class);

        if ($record->isPubliclyVisible() && ! $record->has_unpublished_changes) {
            $liveRevision = $service->record($record, auth()->user(), 'restore', 'Live version (on site)');
            $service->pinPublishedRevision($record, $liveRevision);
        } else {
            $service->record($record, auth()->user(), 'restore', 'Before restore');
        }

        $service->applyToModel($record, $revision);

        if ($record->isPubliclyVisible()) {
            $record->has_unpublished_changes = true;
        }

        $record->save();
        $this->fillForm();

        Notification::make()
            ->title('Version restored to editor')
            ->body('Review the content and publish when ready. The live site is unchanged until you publish.')
            ->success()
            ->send();
    }

    protected function getPendingDraftBannerComponent(): Component
    {
        return Section::make('Unpublished changes')
            ->description('You are editing a draft. The public site still shows the last published version until you publish these changes.')
            ->schema([
                Placeholder::make('draft_status')
                    ->label('')
                    ->content(new HtmlString(
                        '<p class="text-sm text-gray-600 dark:text-gray-300">Use <strong>Preview</strong> to review draft changes, then <strong>Tools → Publish changes</strong> when ready. Open <strong>Version history</strong> to compare or restore earlier snapshots.</p>'
                    )),
            ])
            ->visible(fn (): bool => $this->getRecord()->hasPendingDraft())
            ->columnSpanFull()
            ->compact();
    }

    protected function canPublishContentChanges(): bool
    {
        $user = auth()->user();

        return $user !== null && ($user->isSuperAdmin() || $user->isEditor());
    }

    protected function previewUrlForRevision(PageRevision|PostRevision $revision): string
    {
        $record = $this->getRecord();

        return PreviewUrl::for($record, revisionId: $revision->id);
    }
}
