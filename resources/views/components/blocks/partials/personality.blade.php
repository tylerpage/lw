<div class="rounded-2xl border border-golden/30 bg-golden/10 px-6 py-5">
    @if(! empty($block['status_line']))
        <p class="font-display text-lg font-semibold text-plum">{{ $block['status_line'] }}</p>
    @endif
    <x-markdown :content="$block['body'] ?? ''" class="{{ ! empty($block['status_line']) ? 'mt-2' : '' }} text-sm" />
</div>
