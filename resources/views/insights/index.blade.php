@extends('layouts.app')

@section('content')
<section class="py-16">
    <div class="mx-auto max-w-6xl px-4">
        <h1 class="font-display text-4xl font-bold">Insights</h1>
        <p class="mt-4 text-lg text-charcoal/80">Thoughts on ecommerce strategy, operations, and cross-functional delivery.</p>

        @if($featured)
            <a href="{{ route('insights.show', $featured->slug) }}" class="mt-10 block rounded-2xl bg-lavender/30 p-8 hover:shadow-md">
                <p class="text-xs uppercase tracking-wide text-golden">Featured</p>
                <h2 class="mt-2 font-display text-2xl font-semibold">{{ $featured->title }}</h2>
                <p class="mt-2 text-charcoal/70">{{ $featured->excerpt }}</p>
            </a>
        @endif

        @if($categories->isNotEmpty())
            <div class="mt-8 flex flex-wrap gap-2">
                <a href="{{ route('insights.index') }}" class="rounded-full px-3 py-1 text-sm {{ !request('category') ? 'bg-golden' : 'bg-warm-white' }}">All</a>
                @foreach($categories as $category)
                    <a href="{{ route('insights.index', ['category' => $category->slug]) }}" class="rounded-full px-3 py-1 text-sm {{ request('category') === $category->slug ? 'bg-golden' : 'bg-warm-white' }}">{{ $category->name }}</a>
                @endforeach
            </div>
        @endif

        <div class="mt-10 grid gap-6 md:grid-cols-2">
            @forelse($posts as $post)
                <article class="rounded-2xl bg-warm-white p-6 shadow-sm">
                    <time datetime="{{ $post->published_at?->toDateString() }}" class="text-sm text-charcoal/60">{{ $post->published_at?->format('F j, Y') }}</time>
                    <h2 class="mt-2 font-display text-xl font-semibold">
                        <a href="{{ route('insights.show', $post->slug) }}">{{ $post->title }}</a>
                    </h2>
                    <p class="mt-2 text-sm text-charcoal/70">{{ $post->excerpt }}</p>
                    <p class="mt-3 text-xs text-charcoal/50">{{ $post->readingTime() }} min read</p>
                </article>
            @empty
                <p class="text-charcoal/60">Published insights will appear here.</p>
            @endforelse
        </div>

        <div class="mt-10">{{ $posts->links() }}</div>
    </div>
</section>
@endsection
