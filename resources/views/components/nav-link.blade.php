@props(['href' => '#', 'active' => false])

@php
    $classes = $active
        ? 'px-4 py-2 text-sm font-medium text-mirage-800 bg-mirage-100 rounded-xl transition-all duration-200'
        : 'px-4 py-2 text-sm font-medium text-gray-600 hover:text-mirage-700 hover:bg-mirage-50 rounded-xl transition-all duration-200';
@endphp

<a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
