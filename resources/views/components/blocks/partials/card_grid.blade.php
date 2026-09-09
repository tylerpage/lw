@if(! empty($block['heading']))
    <h2 class="font-display text-3xl font-semibold">{{ $block['heading'] }}</h2>
@endif

<div class="mt-8 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
    @foreach($block['cards'] ?? [] as $card)
        <div class="rounded-2xl bg-warm-white p-6 shadow-sm">
            <x-block-image
                :src="$card['image'] ?? null"
                alt=""
                class="mb-4 h-40 w-full rounded-xl object-cover"
            />
            <h3 class="font-display text-xl font-semibold">{{ $card['title'] ?? '' }}</h3>
            <x-markdown :content="$card['body'] ?? ''" class="mt-2 text-sm" />
            @if(! empty($card['url']))
                <a href="{{ $card['url'] }}" class="mt-4 inline-block text-sm font-semibold text-plum hover:underline">Learn more</a>
            @endif
        </div>
    @endforeach
</div>
