<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $siteName }}</title>
    @include('partials.favicons')
    @vite(['resources/css/app.css'])
</head>
<body class="flex min-h-screen items-center justify-center bg-cream px-4 font-body text-charcoal antialiased">
    <main class="flex flex-col items-center text-center">
        <x-site-logo />
        <h1 class="mt-8 font-display text-3xl font-semibold tracking-tight md:text-4xl">
            {{ $siteName }}
        </h1>
    </main>
</body>
</html>
