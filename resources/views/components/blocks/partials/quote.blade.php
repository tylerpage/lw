<blockquote class="border-l-4 border-golden pl-6">
    <x-markdown :content="$block['quote'] ?? ''" class="text-xl italic" />
    @if(! empty($block['attribution']))
        <footer class="mt-4 text-sm text-charcoal/60">— {{ $block['attribution'] }}</footer>
    @endif
</blockquote>
