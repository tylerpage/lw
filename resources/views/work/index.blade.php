@extends('layouts.app')

@section('content')
<section class="py-16">
    <div class="mx-auto max-w-6xl px-4">
        <h1 class="font-display text-4xl font-bold">Work</h1>
        <p class="mt-4 max-w-2xl text-lg text-charcoal/80">Selected case studies demonstrating ecommerce strategy, solutioning, and delivery.</p>

        @if($disciplines->isNotEmpty() || $industries->isNotEmpty())
            <form method="GET" class="mt-8 flex flex-wrap gap-4">
                @if($disciplines->isNotEmpty())
                    <select name="discipline" class="rounded-lg border border-blush/50 bg-white px-3 py-2">
                        <option value="">All disciplines</option>
                        @foreach($disciplines as $discipline)
                            <option value="{{ $discipline->slug }}" @selected(request('discipline') === $discipline->slug)>{{ $discipline->name }}</option>
                        @endforeach
                    </select>
                @endif
                @if($industries->isNotEmpty())
                    <select name="industry" class="rounded-lg border border-blush/50 bg-white px-3 py-2">
                        <option value="">All industries</option>
                        @foreach($industries as $industry)
                            <option value="{{ $industry->slug }}" @selected(request('industry') === $industry->slug)>{{ $industry->name }}</option>
                        @endforeach
                    </select>
                @endif
                <button type="submit" class="rounded-full bg-golden px-4 py-2 text-sm font-semibold">Filter</button>
            </form>
        @endif

        <div class="mt-10 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            @forelse($projects as $project)
                <a href="{{ route('work.show', $project->slug) }}" class="rounded-2xl bg-warm-white p-6 shadow-sm hover:shadow-md">
                    <p class="text-xs uppercase tracking-wide text-golden">{{ $project->displayClientName() }}</p>
                    <h2 class="mt-2 font-display text-xl font-semibold">{{ $project->title }}</h2>
                    <p class="mt-2 text-sm text-charcoal/70">{{ $project->card_summary }}</p>
                </a>
            @empty
                <p class="text-charcoal/60">Published case studies will appear here.</p>
            @endforelse
        </div>

        <div class="mt-10">{{ $projects->links() }}</div>
    </div>
</section>
@endsection
