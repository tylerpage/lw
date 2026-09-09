<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $seo['title'] ?? $siteName ?? config('app.name') }}</title>
    @if(!empty($seo['description']))
        <meta name="description" content="{{ $seo['description'] }}">
    @endif
    @if(!empty($seo['canonical']))
        <link rel="canonical" href="{{ $seo['canonical'] }}">
    @endif
    @if(!empty($seo['robots']))
        <meta name="robots" content="{{ $seo['robots'] }}">
    @endif
    <meta property="og:title" content="{{ $seo['og_title'] ?? $seo['title'] ?? $siteName }}">
    @if(!empty($seo['og_description'] ?? $seo['description'] ?? null))
        <meta property="og:description" content="{{ $seo['og_description'] ?? $seo['description'] }}">
    @endif
    @if(!empty($seo['og_image']))
        <meta property="og:image" content="{{ $seo['og_image'] }}">
    @endif
    <meta name="twitter:card" content="summary_large_image">
    @include('partials.favicons')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @if(!empty($jsonLd))
        @foreach($jsonLd as $schema)
            <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
        @endforeach
    @endif
</head>
<body class="bg-cream text-charcoal font-body antialiased">
    <a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 focus:z-50 focus:rounded-lg focus:bg-white focus:px-4 focus:py-2 focus:shadow-lg">
        Skip to main content
    </a>

    <div class="sticky top-0 z-40">
    <header class="border-b border-blush/40 bg-warm-white/95 backdrop-blur" x-data="{ mobileOpen: false }">
        <div class="mx-auto flex max-w-6xl items-center justify-between gap-6 px-4 py-3">
            <a href="{{ route('home') }}" aria-label="{{ ($siteName ?? 'Lindsey Wegmann') }} home" class="inline-flex shrink-0 rounded-lg focus:outline-none focus-visible:ring-2 focus-visible:ring-golden focus-visible:ring-offset-2">
                <x-site-logo />
            </a>

            <nav aria-label="Primary" class="hidden items-center gap-6 md:flex">
                @foreach($headerMenu?->items ?? [] as $item)
                    <a href="{{ $item->resolvedUrl() }}" class="text-sm font-medium text-charcoal/80 hover:text-charcoal">{{ $item->label }}</a>
                @endforeach
                <a href="{{ route('contact') }}" class="rounded-full bg-golden px-4 py-2 text-sm font-semibold text-charcoal hover:bg-golden/90" data-analytics-event="cta_click" data-analytics-placement="header" data-analytics-label="Get in touch">Get in touch</a>
            </nav>

            <button
                type="button"
                class="inline-flex min-h-11 min-w-11 items-center justify-center rounded-lg border border-blush/60 text-charcoal md:hidden"
                @click="mobileOpen = !mobileOpen"
                :aria-expanded="mobileOpen.toString()"
                aria-controls="mobile-nav"
            >
                <span class="sr-only">Toggle navigation</span>
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
        </div>

        <nav
            id="mobile-nav"
            aria-label="Mobile"
            class="border-t border-blush/40 bg-warm-white md:hidden"
            x-show="mobileOpen"
            x-cloak
            @click.outside="mobileOpen = false"
        >
            <div class="mx-auto flex max-w-6xl flex-col gap-2 px-4 py-4">
                @foreach($headerMenu?->items ?? [] as $item)
                    <a href="{{ $item->resolvedUrl() }}" class="rounded-lg px-3 py-2 text-sm font-medium text-charcoal/80 hover:bg-lavender/30 hover:text-charcoal">{{ $item->label }}</a>
                @endforeach
                <a href="{{ route('contact') }}" class="mt-2 rounded-full bg-golden px-4 py-3 text-center text-sm font-semibold text-charcoal">Get in touch</a>
            </div>
        </nav>
    </header>
    </div>

    <div class="border-b border-blush/50 bg-gradient-to-r from-lavender/35 via-blush/25 to-lavender/35">
        <div class="mx-auto max-w-6xl px-4 py-4 text-center sm:py-5">
            <p class="font-display text-2xl font-bold tracking-wide text-charcoal sm:text-3xl">
                {{ $siteName ?? 'Lindsey Wegmann' }}
            </p>
        </div>
    </div>

    @if(!empty($isPreview))
        <div class="bg-charcoal px-4 py-2 text-center text-sm text-warm-white" role="status">
            Preview mode — this content is not published.
        </div>
    @endif

    <main id="main">
        @yield('content')
    </main>

    <footer class="mt-16 border-t border-blush/40 bg-lavender/20">
        <div class="mx-auto flex max-w-6xl flex-col gap-6 px-4 py-10 md:flex-row md:items-center md:justify-between">
            <p class="text-sm text-charcoal/70">&copy; {{ date('Y') }} {{ $siteName ?? config('app.name') }}. All rights reserved.</p>
            <nav aria-label="Footer" class="flex flex-wrap gap-4">
                @foreach($footerMenu?->items ?? [] as $item)
                    <a href="{{ $item->resolvedUrl() }}" class="text-sm text-charcoal/70 hover:text-charcoal">{{ $item->label }}</a>
                @endforeach
            </nav>
        </div>
    </footer>

    @if($analyticsConfig['enabled'] ?? false)
        <script>
            window.lwAnalytics = @json($analyticsConfig);
            window.lwTrack = function(event, properties) {
                if (window.lwAnalytics.debug) {
                    console.log('[analytics]', event, properties || {});
                }
                if (window.lwAnalytics.provider === 'plausible' && window.plausible) {
                    window.plausible(event, { props: properties || {} });
                }
            };
            document.addEventListener('analytics-event', function(e) {
                window.lwTrack(e.detail.event, e.detail.properties || {});
            });
        </script>
        @if(($analyticsConfig['provider'] ?? '') === 'plausible')
            <script defer data-domain="{{ $analyticsConfig['plausible_domain'] }}" src="https://plausible.io/js/script.js"></script>
        @endif
    @endif
    @livewireScripts
</body>
</html>
