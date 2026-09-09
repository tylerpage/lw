@extends('layouts.app')

@section('content')
<section class="py-24 text-center">
    <div class="mx-auto max-w-xl px-4">
        <p class="text-6xl">🐕</p>
        <h1 class="mt-6 font-display text-4xl font-bold">Page not found</h1>
        <p class="mt-4 text-lg text-charcoal/80">Looks like this page wandered off. Let's get you back on track.</p>
        <div class="mt-8 flex flex-wrap justify-center gap-4">
            <a href="{{ route('home') }}" class="rounded-full bg-golden px-6 py-3 font-semibold">Go home</a>
            <a href="{{ route('work.index') }}" class="rounded-full border border-charcoal/20 px-6 py-3 font-semibold">View work</a>
        </div>
    </div>
</section>
@endsection
