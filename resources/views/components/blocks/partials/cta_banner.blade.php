<div class="rounded-2xl bg-plum px-8 py-10 text-center text-cream md:px-12">
    @if(! empty($block['heading']))
        <h2 class="font-display text-3xl font-semibold">{{ $block['heading'] }}</h2>
    @endif
    @if(! empty($block['body']))
        <x-markdown :content="$block['body']" class="mx-auto mt-4 max-w-2xl prose-invert" />
    @endif
    @if(! empty($block['cta_label']) && ! empty($block['cta_url']))
        <a href="{{ $block['cta_url'] }}" class="mt-6 inline-flex rounded-full bg-golden px-6 py-3 font-semibold text-charcoal transition hover:bg-golden/90">
            {{ $block['cta_label'] }}
        </a>
    @endif
</div>
