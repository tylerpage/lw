@props(['src', 'alt' => ''])

@php($url = \App\Support\MediaUrl::url($src))

@if($url)
    <img {{ $attributes->merge(['src' => $url, 'alt' => $alt]) }}>
@endif
