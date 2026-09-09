@php $capabilities = \App\Models\Capability::query()->orderBy('sort_order')->get(); @endphp
@if(!empty($block['heading']))<h2 class="font-display text-3xl font-semibold">{{ $block['heading'] }}</h2>@endif
<div class="mt-8 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
    @foreach($capabilities as $capability)
        <div class="rounded-2xl bg-warm-white p-6 shadow-sm">
            <h3 class="font-display text-xl font-semibold">{{ $capability->name }}</h3>
            <ul class="mt-3 list-disc space-y-1 pl-5 text-sm text-charcoal/80">
                @foreach($capability->items ?? [] as $item)
                    <li>{{ $item }}</li>
                @endforeach
            </ul>
        </div>
    @endforeach
</div>
