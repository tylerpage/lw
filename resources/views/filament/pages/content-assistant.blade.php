<x-filament-panels::page>
    <div class="content-assistant">
        @if ($returnUrl)
            <x-filament::section compact>
                <p class="text-sm text-gray-600 dark:text-gray-300">
                    Editing <strong class="text-gray-950 dark:text-white">{{ $this->targetLabel() }}</strong>.
                    Use <strong class="text-gray-950 dark:text-white">Back to editor</strong> when you are done reviewing AI changes.
                </p>
            </x-filament::section>
        @endif

        <x-filament::section heading="How this works" compact secondary>
            <ol class="content-assistant__steps">
                <li class="content-assistant__step">
                    <span class="content-assistant__step-number">1</span>
                    <span><strong class="text-gray-950 dark:text-white">Describe</strong> the change in plain language.</span>
                </li>
                <li class="content-assistant__step">
                    <span class="content-assistant__step-number">2</span>
                    <span><strong class="text-gray-950 dark:text-white">Review</strong> the proposed edits in the panel on the right.</span>
                </li>
                <li class="content-assistant__step">
                    <span class="content-assistant__step-number">3</span>
                    <span><strong class="text-gray-950 dark:text-white">Approve</strong> to publish, or save a draft to preview first.</span>
                </li>
            </ol>
        </x-filament::section>

        <div class="content-assistant__layout">
            <div class="content-assistant__stack">
                <x-filament::section heading="Recent conversations">
                    <div class="content-assistant__conversation-list">
                        @forelse ($this->conversations as $conversation)
                            <button
                                type="button"
                                wire:click="selectConversation({{ $conversation->id }})"
                                @class([
                                    'content-assistant__conversation',
                                    'content-assistant__conversation--active' => $conversationId === $conversation->id,
                                    'content-assistant__conversation--idle' => $conversationId !== $conversation->id,
                                ])
                            >
                                <div class="content-assistant__conversation-title">{{ $conversation->title }}</div>
                                <div class="content-assistant__conversation-meta">{{ $conversation->last_activity_at?->diffForHumans() }}</div>
                            </button>
                        @empty
                            <p class="content-assistant__empty-text">No conversations yet. Pick content below and start one.</p>
                        @endforelse
                    </div>
                </x-filament::section>

                <x-filament::section
                    heading="Content to edit"
                    description="The assistant only changes content on the selected record."
                >
                    <div class="content-assistant__stack">
                        <x-filament::input.wrapper>
                            <x-filament::input.select wire:model.live="targetType">
                                @foreach (\App\Enums\ContentTargetType::cases() as $type)
                                    <option value="{{ $type->value }}">{{ $type->label() }}</option>
                                @endforeach
                            </x-filament::input.select>
                        </x-filament::input.wrapper>

                        <x-filament::input.wrapper>
                            <x-filament::input.select wire:model.live="targetId">
                                @foreach ($this->targetOptions as $id => $title)
                                    <option value="{{ $id }}">{{ $title }}</option>
                                @endforeach
                            </x-filament::input.select>
                        </x-filament::input.wrapper>

                        <x-filament::button wire:click="startConversation" color="gray" class="w-full" size="sm">
                            New conversation
                        </x-filament::button>
                    </div>
                </x-filament::section>
            </div>

            <x-filament::section class="content-assistant__chat" wire:poll.2s="pollForProposal">
                <x-slot:heading>Chat</x-slot:heading>

                @if ($this->targetLabel())
                    <x-slot:description>{{ $this->targetLabel() }}</x-slot:description>
                @endif

                @if ($this->proposalStatusLabel())
                    <x-slot:afterHeader>
                        <x-filament::badge :color="$this->proposalStatusColor()">
                            {{ $this->proposalStatusLabel() }}
                        </x-filament::badge>
                    </x-slot:afterHeader>
                @endif

                <div class="content-assistant__chat-messages" aria-live="polite">
                    @forelse ($this->activeConversation?->messages ?? [] as $chatMessage)
                        @php($isUser = $chatMessage->role->value === 'user')
                        <div @class([
                            'content-assistant__message-row',
                            'content-assistant__message-row--user' => $isUser,
                            'content-assistant__message-row--assistant' => ! $isUser,
                        ])>
                            <div @class([
                                'content-assistant__message',
                                'content-assistant__message--user' => $isUser,
                                'content-assistant__message--assistant' => ! $isUser,
                            ])>
                                <div @class([
                                    'content-assistant__message-label',
                                    'content-assistant__message-label--user' => $isUser,
                                    'content-assistant__message-label--assistant' => ! $isUser,
                                ])>
                                    {{ $isUser ? 'You' : 'Assistant' }}
                                </div>
                                @if ($chatMessage->content !== '[Image reference attached]')
                                    <div class="content-assistant__message-body">{{ $chatMessage->content }}</div>
                                @endif

                                @if ($chatMessage->attachments() !== [])
                                    <div class="content-assistant__message-attachments">
                                        @foreach ($chatMessage->attachments() as $attachment)
                                            <a
                                                href="{{ $attachment['url'] }}"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                class="content-assistant__attachment-link"
                                            >
                                                <img
                                                    src="{{ $attachment['url'] }}"
                                                    alt="{{ $attachment['original_name'] ?? 'Attached image' }}"
                                                    class="content-assistant__attachment-image"
                                                >
                                            </a>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="content-assistant__chat-empty">
                            <p class="font-medium text-gray-950 dark:text-white">Start by describing a content change</p>
                            <p class="content-assistant__empty-text mt-2 max-w-md">
                                Example: “Draft a blog post intro about aligning marketing and engineering teams.”
                            </p>
                        </div>
                    @endforelse

                    @if ($isProcessing)
                        <div class="content-assistant__message-row content-assistant__message-row--assistant">
                            <div class="content-assistant__message content-assistant__message--assistant">
                                <div class="content-assistant__message-label content-assistant__message-label--assistant">Assistant</div>
                                <div class="content-assistant__message-body content-assistant__processing">{{ $processingStatus }}</div>
                            </div>
                        </div>
                    @endif
                </div>

                <x-slot:footer>
                    <form wire:submit="sendMessage" class="content-assistant__composer">
                        <label class="sr-only" for="assistant-message">Message</label>
                        <x-filament::input.wrapper>
                            <textarea
                                id="assistant-message"
                                wire:model="message"
                                rows="3"
                                class="fi-input block w-full"
                                placeholder="Describe the content change you want…"
                                aria-describedby="assistant-message-hint"
                            ></textarea>
                        </x-filament::input.wrapper>

                        @error('message')
                            <p class="mt-2 text-sm text-danger-600">{{ $message }}</p>
                        @enderror

                        @error('attachments')
                            <p class="mt-2 text-sm text-danger-600">{{ $message }}</p>
                        @enderror

                        @error('attachments.*')
                            <p class="mt-2 text-sm text-danger-600">{{ $message }}</p>
                        @enderror

                        @if ($this->libraryAssets->isNotEmpty())
                            <div class="content-assistant__library-picker">
                                <div class="flex items-center justify-between gap-2">
                                    <p class="content-assistant__attachment-label">Media library</p>
                                    <a
                                        href="{{ \App\Filament\Resources\MediaAssets\MediaAssetResource::getUrl('index') }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="text-xs font-medium text-primary-600 hover:underline dark:text-primary-400"
                                    >
                                        Manage files
                                    </a>
                                </div>
                                <div class="content-assistant__library-grid">
                                    @foreach ($this->libraryAssets as $asset)
                                        @php($selected = in_array($asset->id, $libraryAssetIds, true))
                                        <button
                                            type="button"
                                            wire:click="toggleLibraryAsset({{ $asset->id }})"
                                            @class([
                                                'content-assistant__library-item',
                                                'content-assistant__library-item--selected' => $selected,
                                            ])
                                            title="{{ $asset->title }}"
                                        >
                                            <img
                                                src="{{ $asset->url() }}"
                                                alt="{{ $asset->alt_text ?? $asset->title }}"
                                                class="content-assistant__library-image"
                                            >
                                        </button>
                                    @endforeach
                                </div>
                                <p class="content-assistant__hint">
                                    Select reusable images uploaded in the Media Library.
                                </p>
                            </div>
                        @endif

                        <div class="content-assistant__attachment-picker">
                            <label for="assistant-attachments" class="content-assistant__attachment-label">
                                Attach reference images
                            </label>
                            <input
                                id="assistant-attachments"
                                type="file"
                                wire:model="attachments"
                                accept="image/*"
                                multiple
                                class="content-assistant__attachment-input"
                            >
                            <p class="content-assistant__hint">
                                Up to {{ config('content-assistant.attachments.max_files_per_message', 5) }} images.
                                Uploaded files are stored in the app and passed to the assistant by URL.
                            </p>
                        </div>

                        @if ($attachments !== [])
                            <div class="content-assistant__attachment-preview-list">
                                @foreach ($attachments as $index => $attachment)
                                    <div class="content-assistant__attachment-preview" wire:key="attachment-{{ $index }}">
                                        <img
                                            src="{{ $attachment->temporaryUrl() }}"
                                            alt="Pending upload"
                                            class="content-assistant__attachment-image"
                                        >
                                        <button
                                            type="button"
                                            wire:click="removeAttachment({{ $index }})"
                                            class="content-assistant__attachment-remove"
                                        >
                                            Remove
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <div wire:loading wire:target="attachments" class="content-assistant__hint">
                            Uploading image preview…
                        </div>

                        <div class="content-assistant__composer-footer">
                            <p id="assistant-message-hint" class="content-assistant__hint">
                                Enter to send · Shift+Enter for a new line · Content changes only, not layout or code.
                            </p>
                            <x-filament::button type="submit" wire:loading.attr="disabled" wire:target="sendMessage,attachments" :disabled="$isProcessing">
                                <span wire:loading.remove wire:target="sendMessage,attachments">{{ $isProcessing ? 'Working…' : 'Send' }}</span>
                                <span wire:loading wire:target="sendMessage,attachments">Sending…</span>
                            </x-filament::button>
                        </div>
                    </form>
                </x-slot:footer>
            </x-filament::section>

            <x-filament::section heading="Proposed changes">
                @if ($this->latestProposal?->summary)
                    <p class="content-assistant__summary">{{ $this->latestProposal->summary }}</p>
                @elseif (! $this->hasProposal())
                    <p class="content-assistant__empty-text">
                        Send a message to generate a structured proposal you can review here.
                    </p>
                @endif

                @if ($validationErrors !== [])
                    <div class="content-assistant__alert content-assistant__alert--danger mt-4" role="alert">
                        <p class="content-assistant__alert-title">Fix these before saving</p>
                        <ul class="content-assistant__alert-list">
                            @foreach ($validationErrors as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>

                        @if ($this->canRequestOverride())
                            <div class="mt-3 space-y-2">
                                <p class="text-sm text-gray-700 dark:text-gray-200">
                                    If you still want this change, ask the assistant to apply your request anyway.
                                </p>
                                <x-filament::button
                                    wire:click="requestOverride"
                                    wire:confirm="Ask the assistant to implement your request even if it shifts the content focus? Safety rules still apply (no code or layout changes)."
                                    color="warning"
                                    size="sm"
                                >
                                    Apply request anyway
                                </x-filament::button>
                            </div>
                        @endif
                    </div>
                @endif

                @if ($validationWarnings !== [])
                    <div class="content-assistant__alert content-assistant__alert--warning mt-4">
                        <p class="content-assistant__alert-title">Review warnings</p>
                        <ul class="content-assistant__alert-list">
                            @foreach ($validationWarnings as $warning)
                                <li>{{ $warning }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="content-assistant__diff-list mt-4">
                    @forelse ($this->formattedDiff as $item)
                        <div class="content-assistant__diff-card">
                            <div class="content-assistant__diff-header">
                                <p class="content-assistant__diff-title">{{ $item['title'] }}</p>
                                @if (! empty($item['description']))
                                    <p class="content-assistant__diff-description">{{ $item['description'] }}</p>
                                @endif
                            </div>

                            <div class="content-assistant__diff-body">
                                @if (($item['type'] ?? null) === 'block_fields' && ! empty($item['changes']))
                                    @foreach ($item['changes'] as $change)
                                        <div class="space-y-2">
                                            <p class="content-assistant__diff-field">{{ $change['field'] }}</p>
                                            <div class="grid gap-2">
                                                <div class="content-assistant__diff-snapshot content-assistant__diff-snapshot--before">
                                                    <p class="content-assistant__diff-snapshot-label content-assistant__diff-snapshot-label--before">Before</p>
                                                    <p class="content-assistant__diff-snapshot-value content-assistant__diff-snapshot-value--before">{{ $change['before'] }}</p>
                                                </div>
                                                <div class="content-assistant__diff-snapshot content-assistant__diff-snapshot--after">
                                                    <p class="content-assistant__diff-snapshot-label content-assistant__diff-snapshot-label--after">After</p>
                                                    <p class="content-assistant__diff-snapshot-value content-assistant__diff-snapshot-value--after">{{ $change['after'] }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach

                                    @if (! empty($item['preserved']))
                                        <p class="content-assistant__diff-preserved">
                                            Unchanged: {{ implode(', ', $item['preserved']) }}
                                        </p>
                                    @endif
                                @elseif (isset($item['before']) && isset($item['after']))
                                    <div class="grid gap-2">
                                        <div class="content-assistant__diff-snapshot content-assistant__diff-snapshot--before">
                                            <p class="content-assistant__diff-snapshot-label content-assistant__diff-snapshot-label--before">Before</p>
                                            <p class="content-assistant__diff-snapshot-value content-assistant__diff-snapshot-value--before">{{ $item['before'] }}</p>
                                        </div>
                                        <div class="content-assistant__diff-snapshot content-assistant__diff-snapshot--after">
                                            <p class="content-assistant__diff-snapshot-label content-assistant__diff-snapshot-label--after">After</p>
                                            <p class="content-assistant__diff-snapshot-value content-assistant__diff-snapshot-value--after">{{ $item['after'] }}</p>
                                        </div>
                                    </div>
                                @elseif (! empty($item['preview']))
                                    <ul class="content-assistant__alert-list text-sm text-gray-700 dark:text-gray-200">
                                        @foreach ($item['preview'] as $line)
                                            <li>{{ $line }}</li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        </div>
                    @empty
                        @if ($this->hasProposal())
                            <p class="content-assistant__empty-text">This proposal has no editable content changes.</p>
                        @endif
                    @endforelse
                </div>

                @if ($this->hasReviewableProposal())
                    <x-slot:footer>
                        <div class="content-assistant__actions">
                            @if ($this->canRequestOverride() && $validationErrors === [])
                                <x-filament::button
                                    wire:click="requestOverride"
                                    wire:confirm="Ask the assistant to implement your request even if it shifts the content focus? Safety rules still apply (no code or layout changes)."
                                    class="w-full"
                                    color="warning"
                                >
                                    Apply request anyway
                                </x-filament::button>
                            @endif

                            @if ($this->canApproveAndPublish())
                                <x-filament::button
                                    wire:click="approveAndPublish"
                                    wire:confirm="Publish these changes to the live site?"
                                    class="w-full"
                                >
                                    Approve & publish
                                </x-filament::button>
                            @endif

                            <x-filament::button
                                wire:click="applyDraft"
                                class="w-full"
                                color="gray"
                                :disabled="! $this->canSaveDraft()"
                            >
                                Save as draft
                            </x-filament::button>

                            @if (! $this->canSaveDraft() && ! $this->canRequestOverride())
                                <p class="content-assistant__hint text-center">Validate the proposal before saving or publishing.</p>
                            @elseif (! $this->canSaveDraft() && $this->canRequestOverride())
                                <p class="content-assistant__hint text-center">The assistant declined or could not apply your request. Use override to retry.</p>
                            @elseif (! $this->canApproveAndPublish())
                                <p class="content-assistant__hint text-center">Only editors can approve and publish. You can still save a draft.</p>
                            @endif

                            <div class="content-assistant__actions-row">
                                <x-filament::button wire:click="validateProposal" color="gray" size="sm" class="w-full">
                                    Re-validate
                                </x-filament::button>
                                <x-filament::button wire:click="openPreview" color="gray" size="sm" class="w-full">
                                    Preview
                                </x-filament::button>
                                <x-filament::button wire:click="rejectProposal" color="danger" size="sm" class="w-full">
                                    Discard
                                </x-filament::button>
                            </div>
                        </div>
                    </x-slot:footer>
                @endif
            </x-filament::section>
        </div>
    </div>

    @script
    <script>
        const bindAssistantComposer = () => {
            const textarea = document.getElementById('assistant-message');

            if (! textarea || textarea.dataset.enterToSendBound === 'true') {
                return;
            }

            textarea.dataset.enterToSendBound = 'true';

            textarea.addEventListener('keydown', (event) => {
                if (event.key !== 'Enter' || event.shiftKey || event.isComposing) {
                    return;
                }

                event.preventDefault();

                if ($wire.isProcessing) {
                    return;
                }

                textarea.closest('form')?.requestSubmit();
            });
        };

        bindAssistantComposer();

        Livewire.hook('morph.updated', bindAssistantComposer);

        $wire.on('open-preview', ({ url }) => {
            window.open(url, '_blank', 'noopener,noreferrer');
        });

        if (window.Echo && @js(auth()->id())) {
            window.Echo.private(`content-assistant.${@js(auth()->id())}`)
                .listen('.assistant.processing', (event) => {
                    $wire.handleAssistantBroadcast(event);
                })
                .listen('.assistant.proposal.ready', (event) => {
                    $wire.handleAssistantBroadcast(event);
                })
                .listen('.assistant.message.failed', (event) => {
                    $wire.handleAssistantBroadcast(event);
                });
        }
    </script>
    @endscript
</x-filament-panels::page>
