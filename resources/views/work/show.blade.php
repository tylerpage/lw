@extends('layouts.app')

@section('content')
<section class="py-16">
    <div class="mx-auto max-w-4xl px-4">
        <p class="text-sm uppercase tracking-wide text-golden">{{ $project->displayClientName() }}</p>
        <h1 class="mt-2 font-display text-4xl font-bold">{{ $project->title }}</h1>
        @if($project->role)
            <p class="mt-4 text-charcoal/70">Role: {{ $project->role }}</p>
        @endif
        @if($project->card_summary)
            <p class="mt-4 text-lg text-charcoal/80">{{ $project->card_summary }}</p>
        @endif

        {!! $content !!}

        @if($project->metrics->isNotEmpty())
            <div class="mt-12 grid gap-4 sm:grid-cols-3">
                @foreach($project->metrics as $metric)
                    <div class="rounded-xl bg-lavender/30 p-4 text-center">
                        <p class="font-display text-2xl font-bold">{{ $metric->value }}</p>
                        <p class="text-sm text-charcoal/70">{{ $metric->label }}</p>
                    </div>
                @endforeach
            </div>
        @endif

        @if($related->isNotEmpty())
            <div class="mt-16">
                <h2 class="font-display text-2xl font-semibold">Related work</h2>
                <div class="mt-6 grid gap-4 md:grid-cols-3">
                    @foreach($related as $item)
                        <a href="{{ route('work.show', $item->slug) }}" class="rounded-xl bg-warm-white p-4 hover:shadow">{{ $item->title }}</a>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="mt-16 text-center">
            <a href="{{ route('contact') }}" data-analytics-event="cta_click" data-analytics-placement="case_study" class="rounded-full bg-golden px-6 py-3 font-semibold">Get in touch</a>
        </div>
    </div>
</section>
@endsection
