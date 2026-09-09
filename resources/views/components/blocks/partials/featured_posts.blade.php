@php $posts = \App\Models\Post::query()->published()->latest('published_at')->limit($block['limit'] ?? 3)->get(); @endphp
@if(!empty($block['heading']))<h2 class="font-display text-3xl font-semibold">{{ $block['heading'] }}</h2>@endif
<div class="mt-8 grid gap-6 md:grid-cols-3">
    @forelse($posts as $post)
        <a href="{{ route('insights.show', $post->slug) }}" class="group rounded-2xl bg-warm-white p-6 shadow-sm">
            <h3 class="font-display text-lg font-semibold">{{ $post->title }}</h3>
            <p class="mt-2 text-sm text-charcoal/70">{{ $post->excerpt }}</p>
        </a>
    @empty
        <p class="text-charcoal/60">Insights will appear here when published.</p>
    @endforelse
</div>
