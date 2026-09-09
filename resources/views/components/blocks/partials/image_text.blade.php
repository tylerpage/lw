@php
    $imagePosition = ($block['image_position'] ?? 'left') === 'right' ? 'md:flex-row-reverse' : 'md:flex-row';
@endphp

<div class="flex flex-col gap-8 {{ $imagePosition }} md:items-center">
    @if(\App\Support\MediaUrl::url($block['image'] ?? null))
        <div class="md:w-1/2">
            <x-block-image
                :src="$block['image']"
                :alt="$block['image_alt'] ?? ''"
                class="w-full rounded-2xl object-cover shadow-sm"
            />
        </div>
    @endif
    <div class="md:w-1/2">
        @if(! empty($block['heading']))
            <h2 class="font-display text-3xl font-semibold">{{ $block['heading'] }}</h2>
        @endif
        <x-markdown :content="$block['content'] ?? ''" class="{{ ! empty($block['heading']) ? 'mt-4' : '' }}" />
    </div>
</div>
