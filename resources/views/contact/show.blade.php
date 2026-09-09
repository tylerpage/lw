@extends('layouts.app')

@section('content')
<section class="py-16">
    <div class="mx-auto max-w-2xl px-4">
        <h1 class="font-display text-4xl font-bold">Contact</h1>
        <p class="mt-4 text-lg text-charcoal/80">Have a question about ecommerce strategy, a role, or a project? I'd love to hear from you.</p>

        <div class="mt-10">
            @livewire('contact-form')
        </div>
    </div>
</section>
@endsection
