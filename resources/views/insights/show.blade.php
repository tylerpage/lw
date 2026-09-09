@extends('layouts.app')

@section('content')
<article class="py-16">
    <div class="mx-auto max-w-3xl px-4">
        <header>
            <time datetime="{{ $post->published_at?->toDateString() }}" class="text-sm text-charcoal/60">{{ $post->published_at?->format('F j, Y') }}</time>
            <h1 class="mt-2 font-display text-4xl font-bold">{{ $post->title }}</h1>
            @if($post->excerpt)
                <p class="mt-4 text-xl text-charcoal/80">{{ $post->excerpt }}</p>
            @endif
            <p class="mt-4 text-sm text-charcoal/60">{{ $post->readingTime() }} min read @if($post->author) · {{ $post->author->name }}@endif</p>
        </header>

        <div class="prose prose-lg mt-10 max-w-none">
            {!! $content !!}
        </div>

        @if($related->isNotEmpty())
            <aside class="mt-16 border-t border-blush/40 pt-10">
                <h2 class="font-display text-xl font-semibold">Related insights</h2>
                <ul class="mt-4 space-y-2">
                    @foreach($related as $item)
                        <li><a href="{{ route('insights.show', $item->slug) }}" class="text-plum hover:underline">{{ $item->title }}</a></li>
                    @endforeach
                </ul>
            </aside>
        @endif
    </div>
</article>
@endsection
