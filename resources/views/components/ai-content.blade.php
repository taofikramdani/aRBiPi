@props(['content'])

<div {{ $attributes->class('ai-content') }}>
    {!! \App\Support\AiContentFormatter::toHtml($content) !!}
</div>
