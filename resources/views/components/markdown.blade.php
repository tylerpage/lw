@props(['content' => ''])

@if(filled($content))
    <div {{ $attributes->class(['prose prose-lg max-w-none text-charcoal/90']) }}>
        {!! \App\Support\Markdown::render($content) !!}
    </div>
@endif
