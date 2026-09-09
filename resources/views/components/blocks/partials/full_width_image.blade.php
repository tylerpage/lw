<figure>
    <x-block-image
        :src="$block['image'] ?? null"
        :alt="$block['image_alt'] ?? ''"
        class="w-full rounded-2xl object-cover shadow-lg"
    />
    @if(! empty($block['caption']))
        <figcaption class="mt-3 text-center text-sm text-charcoal/70">{{ $block['caption'] }}</figcaption>
    @endif
</figure>
