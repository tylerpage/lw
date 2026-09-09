<section @if(!empty($block['anchor_id'])) id="{{ $block['anchor_id'] }}" @endif class="py-12 {{ $block['background'] ?? '' }}">
    <div class="mx-auto max-w-6xl px-4">
        @include('components.blocks.partials.stats', ['block' => $block])
    </div>
</section>
