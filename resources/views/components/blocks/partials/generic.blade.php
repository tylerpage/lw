<div class="rounded-xl border border-blush/30 bg-warm-white/50 p-6">
    <p class="text-sm uppercase tracking-wide text-charcoal/50">{{ str_replace('_', ' ', $type) }} block</p>
    @if(!empty($block['heading']))<h2 class="mt-2 font-display text-2xl font-semibold">{{ $block['heading'] }}</h2>@endif
    @if(!empty($block['content']))<p class="mt-2 text-charcoal/80">{{ $block['content'] }}</p>@endif
    @if(!empty($block['quote']))<blockquote class="mt-2 italic">"{{ $block['quote'] }}"</blockquote>@endif
    @if(!empty($block['body']))<p class="mt-2">{{ $block['body'] }}</p>@endif
    @if(!empty($block['status_line']))<p class="mt-2 text-golden font-medium">{{ $block['status_line'] }}</p>@endif
</div>
