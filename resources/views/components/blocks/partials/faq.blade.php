@if(! empty($block['heading']))
    <h2 class="font-display text-3xl font-semibold">{{ $block['heading'] }}</h2>
@endif

<div class="mt-8 space-y-4">
    @foreach($block['items'] ?? [] as $item)
        <details class="rounded-xl border border-blush/30 bg-warm-white p-5">
            <summary class="cursor-pointer font-semibold">{{ $item['question'] ?? '' }}</summary>
            <x-markdown :content="$item['answer'] ?? ''" class="mt-3 text-sm" />
        </details>
    @endforeach
</div>
