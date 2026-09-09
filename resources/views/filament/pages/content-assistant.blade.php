<x-filament-panels::page>
    <div class="grid gap-6 lg:grid-cols-[280px_minmax(0,1fr)]">
        <aside class="space-y-4">
            <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-900">
                <h2 class="text-sm font-semibold text-gray-950 dark:text-white">Conversations</h2>
                <div class="mt-3 space-y-2">
                    @forelse ($this->conversations as $conversation)
                        <button
                            type="button"
                            wire:click="selectConversation({{ $conversation->id }})"
                            class="w-full rounded-lg border px-3 py-2 text-left text-sm transition @if($conversationId === $conversation->id) border-amber-400 bg-amber-50 dark:bg-amber-950/20 @else border-gray-200 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800 @endif"
                        >
                            <div class="font-medium text-gray-950 dark:text-white">{{ $conversation->title }}</div>
                            <div class="mt-1 text-xs text-gray-500">{{ $conversation->last_activity_at?->diffForHumans() }}</div>
                        </button>
                    @empty
                        <p class="text-sm text-gray-500">No conversations yet.</p>
                    @endforelse
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-900">
                <h2 class="text-sm font-semibold text-gray-950 dark:text-white">Content target</h2>
                <div class="mt-3 space-y-3">
                    <select
                        wire:model.live="targetPageId"
                        class="fi-input block w-full rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-900"
                    >
                        @foreach ($this->pageOptions as $id => $title)
                            <option value="{{ $id }}">{{ $title }}</option>
                        @endforeach
                    </select>
                    <x-filament::button wire:click="startConversation" color="gray" class="w-full">
                        Start new conversation
                    </x-filament::button>
                </div>
            </div>
        </aside>

        <div class="space-y-6">
            <section class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-900">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h2 class="text-base font-semibold text-gray-950 dark:text-white">Conversation</h2>
                        @if ($this->targetLabel())
                            <p class="mt-1 text-sm text-gray-500">{{ $this->targetLabel() }}</p>
                        @endif
                    </div>
                    @if ($this->proposalStatusLabel())
                        <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-200">
                            {{ $this->proposalStatusLabel() }}
                        </span>
                    @endif
                </div>

                <div class="mt-4 max-h-[420px] space-y-3 overflow-y-auto" aria-live="polite">
                    @forelse ($this->activeConversation?->messages ?? [] as $chatMessage)
                        <div @class([
                            'rounded-lg px-3 py-2 text-sm',
                            'bg-gray-100 text-gray-900 dark:bg-gray-800 dark:text-gray-100' => $chatMessage->role->value === 'user',
                            'bg-amber-50 text-gray-900 dark:bg-amber-950/20 dark:text-gray-100' => $chatMessage->role->value === 'assistant',
                        ])>
                            <div class="mb-1 text-xs font-semibold uppercase tracking-wide text-gray-500">
                                {{ $chatMessage->role->label() }}
                            </div>
                            <div class="whitespace-pre-wrap">{{ $chatMessage->content }}</div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">Describe the content change you want to make.</p>
                    @endforelse
                </div>

                <form wire:submit="sendMessage" class="mt-4 space-y-3">
                    <label class="sr-only" for="assistant-message">Message</label>
                    <textarea
                        id="assistant-message"
                        wire:model="message"
                        rows="4"
                        class="fi-input block w-full rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-900"
                        placeholder="Example: Make the homepage hero focus more on connecting marketing and engineering, but keep both CTAs."
                    ></textarea>
                    @error('message')
                        <p class="text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                    <x-filament::button type="submit">
                        Send
                    </x-filament::button>
                </form>
            </section>

            <section class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-900">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h2 class="text-base font-semibold text-gray-950 dark:text-white">Proposed changes</h2>
                    <div class="flex flex-wrap gap-2">
                        <x-filament::button wire:click="validateProposal" color="gray" size="sm">
                            Re-validate
                        </x-filament::button>
                        <x-filament::button wire:click="applyDraft" size="sm">
                            Save draft
                        </x-filament::button>
                        <x-filament::button wire:click="openPreview" color="gray" size="sm">
                            Preview
                        </x-filament::button>
                        <x-filament::button wire:click="rejectProposal" color="danger" size="sm">
                            Discard
                        </x-filament::button>
                    </div>
                </div>

                @if ($this->latestProposal)
                    <p class="mt-3 text-sm text-gray-700 dark:text-gray-200">{{ $this->latestProposal->summary }}</p>
                @endif

                @if ($validationErrors !== [])
                    <div class="mt-4 rounded-lg border border-danger-300 bg-danger-50 p-3 text-sm text-danger-800 dark:border-danger-800 dark:bg-danger-950/20 dark:text-danger-200" role="alert">
                        <p class="font-semibold">Validation errors</p>
                        <ul class="mt-2 list-disc pl-5">
                            @foreach ($validationErrors as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if ($validationWarnings !== [])
                    <div class="mt-4 rounded-lg border border-amber-300 bg-amber-50 p-3 text-sm text-amber-900 dark:border-amber-800 dark:bg-amber-950/20 dark:text-amber-100">
                        <p class="font-semibold">Warnings</p>
                        <ul class="mt-2 list-disc pl-5">
                            @foreach ($validationWarnings as $warning)
                                <li>{{ $warning }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="mt-4 space-y-4">
                    @forelse ($diff as $item)
                        <div class="rounded-lg border border-gray-200 p-3 dark:border-gray-700">
                            <div class="text-sm font-semibold text-gray-950 dark:text-white">{{ $item['label'] ?? 'Change' }}</div>

                            @if (($item['type'] ?? null) === 'field')
                                <dl class="mt-2 grid gap-2 text-sm">
                                    <div>
                                        <dt class="font-medium text-gray-500">Before</dt>
                                        <dd class="mt-1 whitespace-pre-wrap text-gray-900 dark:text-gray-100">{{ $item['before'] ?? '—' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="font-medium text-gray-500">After</dt>
                                        <dd class="mt-1 whitespace-pre-wrap text-gray-900 dark:text-gray-100">{{ $item['after'] ?? '—' }}</dd>
                                    </div>
                                </dl>
                            @elseif (($item['type'] ?? null) === 'block_fields')
                                <dl class="mt-2 grid gap-2 text-sm">
                                    <div>
                                        <dt class="font-medium text-gray-500">Before</dt>
                                        <dd class="mt-1"><pre class="overflow-x-auto rounded bg-gray-50 p-2 text-xs dark:bg-gray-800">{{ json_encode($item['before'] ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre></dd>
                                    </div>
                                    <div>
                                        <dt class="font-medium text-gray-500">After</dt>
                                        <dd class="mt-1"><pre class="overflow-x-auto rounded bg-gray-50 p-2 text-xs dark:bg-gray-800">{{ json_encode($item['after'] ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre></dd>
                                    </div>
                                </dl>
                            @else
                                <pre class="mt-2 overflow-x-auto rounded bg-gray-50 p-2 text-xs dark:bg-gray-800">{{ json_encode($item, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                            @endif
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">Send a message to generate a structured proposal and diff.</p>
                    @endforelse
                </div>
            </section>
        </div>
    </div>

    @script
    <script>
        $wire.on('open-preview', ({ url }) => {
            window.open(url, '_blank', 'noopener,noreferrer');
        });
    </script>
    @endscript
</x-filament-panels::page>
