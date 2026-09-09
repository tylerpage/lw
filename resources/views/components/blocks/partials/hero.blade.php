<div class="grid items-center gap-10 md:grid-cols-2">
    <div>
        <h1 class="font-display text-4xl font-bold leading-tight md:text-5xl">{{ $block['headline'] }}</h1>
        @if(!empty($block['subheadline']))
            <p class="mt-4 text-lg text-charcoal/80">{{ $block['subheadline'] }}</p>
        @endif
        <div class="mt-8 flex flex-wrap gap-4">
            @if(!empty($block['primary_cta_label']))
                <a href="{{ $block['primary_cta_url'] ?? '#' }}" data-analytics-event="cta_click" data-analytics-placement="hero" class="rounded-full bg-golden px-6 py-3 font-semibold text-charcoal">{{ $block['primary_cta_label'] }}</a>
            @endif
            @if(!empty($block['secondary_cta_label']))
                <a href="{{ $block['secondary_cta_url'] ?? route('contact') }}" data-analytics-event="cta_click" data-analytics-placement="hero" class="rounded-full border border-charcoal/20 px-6 py-3 font-semibold">{{ $block['secondary_cta_label'] }}</a>
            @endif
        </div>
    </div>
    <x-block-image
        :src="$block['image'] ?? null"
        :alt="$block['image_alt'] ?? ''"
        class="aspect-square w-full max-w-md rounded-2xl object-cover object-top shadow-lg"
        width="448"
        height="448"
    />
</div>
