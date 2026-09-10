<div class="space-y-4">
    @if ($hasPendingDraft)
        <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-100">
            The public site is still showing the
            @if ($liveRevisionId)
                pinned live version below.
            @else
                last saved live content.
            @endif
            Your editor contains newer draft changes.
        </div>
    @endif

    @if ($revisions->isEmpty())
        <p class="text-sm text-gray-600 dark:text-gray-300">No saved versions yet. Versions are created when you save AI changes, import content, or restore an older snapshot.</p>
    @else
        <div class="divide-y divide-gray-200 rounded-lg border border-gray-200 dark:divide-white/10 dark:border-white/10">
            @foreach ($revisions as $revision)
                @php
                    $isLive = $liveRevisionId === $revision->id;
                    $previewUrl = \App\Support\PreviewUrl::for($record, revisionId: $revision->id);
                @endphp
                <div class="flex flex-col gap-3 px-4 py-3 sm:flex-row sm:items-center sm:justify-between" wire:key="revision-{{ $revision->id }}">
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <p class="font-medium text-gray-950 dark:text-white">
                                {{ $revision->label ?: 'Saved version' }}
                            </p>
                            @if ($isLive)
                                <span class="rounded-full bg-success-100 px-2 py-0.5 text-xs font-medium text-success-700 dark:bg-success-500/10 dark:text-success-300">Live on site</span>
                            @endif
                        </div>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                            {{ $revision->created_at?->timezone(config('app.timezone'))->format('M j, Y g:i A') }}
                            @if ($revision->user)
                                · {{ $revision->user->name }}
                            @endif
                            @if ($revision->source)
                                · {{ str($revision->source)->replace('_', ' ')->title() }}
                            @endif
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <x-filament::button
                            tag="a"
                            href="{{ $previewUrl }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            size="sm"
                            color="gray"
                        >
                            Preview
                        </x-filament::button>

                        <x-filament::button
                            size="sm"
                            color="gray"
                            wire:click="restoreContentRevision({{ $revision->id }})"
                            wire:confirm="Restore this version into the editor? The live site will not change until you use Tools → Publish changes."
                        >
                            Restore to editor
                        </x-filament::button>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
