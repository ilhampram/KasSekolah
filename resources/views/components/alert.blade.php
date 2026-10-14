@props(['type' => 'info', 'dismissible' => true])

@php
    $colors = match ($type) {
        'success' => 'bg-emerald-50 border-emerald-200 text-emerald-700',
        'error'   => 'bg-red-50 border-red-200 text-red-700',
        'warning' => 'bg-yellow-50 border-yellow-200 text-yellow-700',
        default   => 'bg-blue-50 border-blue-200 text-blue-700',
    };
    $icons = match ($type) {
        'success' => '✅',
        'error'   => '❌',
        'warning' => '⚠️',
        default   => 'ℹ️',
    };
@endphp

<div x-data="{ show: true }" x-show="show" x-transition
     class="p-4 border rounded-lg text-sm flex items-start gap-3 {{ $colors }}">
    <span class="text-base">{{ $icons }}</span>
    <div class="flex-1">{{ $slot }}</div>
    @if($dismissible)
        <button @click="show = false" class="ml-auto opacity-60 hover:opacity-100 transition">&times;</button>
    @endif
</div>
