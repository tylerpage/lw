@extends('layouts.app')

@section('content')
<section class="py-16">
    <div class="mx-auto max-w-3xl px-4">
        <h1 class="font-display text-3xl font-bold">Search</h1>
        <form method="GET" class="mt-6">
            <label for="q" class="sr-only">Search</label>
            <input type="search" name="q" id="q" value="{{ $query }}" class="w-full rounded-lg border border-blush/50 px-4 py-3">
        </form>

        @if($query !== '')
            <div class="mt-10 space-y-8">
                @if($projects->isNotEmpty())
                    <div>
                        <h2 class="font-display text-xl font-semibold">Work</h2>
                        <ul class="mt-4 space-y-2">
                            @foreach($projects as $project)
                                <li><a href="{{ route('work.show', $project->slug) }}" class="text-plum hover:underline">{{ $project->title }}</a></li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                @if($posts->isNotEmpty())
                    <div>
                        <h2 class="font-display text-xl font-semibold">Insights</h2>
                        <ul class="mt-4 space-y-2">
                            @foreach($posts as $post)
                                <li><a href="{{ route('insights.show', $post->slug) }}" class="text-plum hover:underline">{{ $post->title }}</a></li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                @if($projects->isEmpty() && $posts->isEmpty())
                    <p class="text-charcoal/60">No results found.</p>
                @endif
            </div>
        @endif
    </div>
</section>
@endsection
