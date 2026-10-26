@props(['href' => '#', 'icon' => '', 'active' => false, 'badge' => null])

<a href="{{ $href }}"
   class="flex items-center justify-between px-4 py-2.5 rounded-lg text-sm font-medium transition
          {{ $active
              ? 'bg-indigo-800 text-white'
              : 'text-indigo-200 hover:bg-indigo-800/60 hover:text-white' }}">
    <div class="flex items-center gap-3">
        <span class="text-base">{{ $icon }}</span>
        <span>{{ $slot }}</span>
    </div>
    @if($badge && $badge > 0)
        <span class="inline-flex items-center justify-center px-2 py-0.5 text-xs font-bold leading-none text-white bg-rose-500 rounded-full shadow-sm animate-pulse">
            {{ $badge }}
        </span>
    @endif
</a>
