@props(['href' => '#', 'icon' => '', 'active' => false])

<a href="{{ $href }}"
   class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium transition
          {{ $active
              ? 'bg-indigo-800 text-white'
              : 'text-indigo-200 hover:bg-indigo-800/60 hover:text-white' }}">
    <span class="text-base">{{ $icon }}</span>
    <span>{{ $slot }}</span>
</a>
