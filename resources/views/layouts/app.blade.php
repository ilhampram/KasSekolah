<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Kas Sekolah') — {{ config('app.name', 'Kas Sekolah') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-100 min-h-screen flex">

    {{-- ─── Sidebar ─────────────────────────────────────────── --}}
    <aside id="sidebar"
           class="fixed inset-y-0 left-0 z-30 w-64 bg-indigo-900 text-white transform -translate-x-full
                  transition-transform duration-200 ease-in-out lg:translate-x-0 lg:static lg:inset-auto">

        {{-- Brand --}}
        <div class="flex items-center gap-3 px-6 py-5 border-b border-indigo-800">
            <span class="text-2xl">💰</span>
            <span class="font-bold text-lg leading-tight">Kas Sekolah</span>
        </div>

        {{-- Nav Menu --}}
        <nav class="mt-4 flex flex-col gap-1 px-3">
            @auth
                @if(auth()->user()->role === 'admin' || auth()->user()->role === 'bendahara_kelas')
                    {{-- Admin / Bendahara Menu --}}
                    <x-sidebar-link href="{{ route('admin.dashboard') }}" icon="📊" :active="request()->routeIs('admin.dashboard')">
                        Dashboard
                    </x-sidebar-link>
                    <x-sidebar-link href="{{ route('admin.academic-years.index') }}" icon="📅" :active="request()->routeIs('admin.academic-years.*')">
                        Tahun Ajaran
                    </x-sidebar-link>
                    <x-sidebar-link href="{{ route('admin.classes.index') }}" icon="🏫" :active="request()->routeIs('admin.classes.*')">
                        Master Kelas
                    </x-sidebar-link>
                    <x-sidebar-link href="{{ route('admin.students.index') }}" icon="👥" :active="request()->routeIs('admin.students.*')">
                        Master Siswa
                    </x-sidebar-link>
                    <x-sidebar-link href="{{ route('admin.categories.index') }}" icon="📂" :active="request()->routeIs('admin.categories.*')">
                        Kategori Kas
                    </x-sidebar-link>
                    <x-sidebar-link href="{{ route('admin.dues.index') }}" icon="💵" :active="request()->routeIs('admin.dues.*') || request()->routeIs('admin.bills.*')">
                        Tagihan Siswa
                    </x-sidebar-link>
                    <x-sidebar-link href="{{ route('admin.payments.index') }}" icon="🧾" :active="request()->routeIs('admin.payments.*')">
                        Kasir & Pembayaran
                    </x-sidebar-link>
                    <x-sidebar-link href="{{ route('admin.transactions.index') }}" icon="💳" :active="request()->routeIs('admin.transactions.*')">
                        Transaksi Kas
                    </x-sidebar-link>
                    <x-sidebar-link href="{{ route('admin.reports.index') }}" icon="📄" :active="request()->routeIs('admin.reports.*')">
                        Laporan Keuangan
                    </x-sidebar-link>
                @elseif(auth()->user()->role === 'siswa')
                    {{-- Siswa Menu --}}
                    <x-sidebar-link href="{{ route('siswa.dashboard') }}" icon="📊" :active="request()->routeIs('siswa.dashboard')">
                        Dashboard
                    </x-sidebar-link>
                    <x-sidebar-link href="{{ route('siswa.payments.index') }}" icon="📝" :active="request()->routeIs('siswa.payments.*')">
                        Histori Pembayaran
                    </x-sidebar-link>
                    <x-sidebar-link href="{{ route('siswa.transparency.index') }}" icon="📈" :active="request()->routeIs('siswa.transparency.*')">
                        Transparansi Kas
                    </x-sidebar-link>
                @endif
            @endauth
        </nav>

        {{-- User Info (bottom) --}}
        <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-indigo-800">
            @auth
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-indigo-700 flex items-center justify-center text-sm font-bold">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium truncate">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-indigo-300 truncate capitalize">{{ str_replace('_', ' ', auth()->user()->role) }}</p>
                    </div>
                </div>
            @endauth
        </div>
    </aside>

    {{-- Overlay for mobile --}}
    <div id="sidebar-overlay" class="fixed inset-0 z-20 bg-black/50 hidden lg:hidden" onclick="toggleSidebar()"></div>

    {{-- ─── Main Content Area ───────────────────────────────── --}}
    <div class="flex-1 flex flex-col min-h-screen">

        {{-- Topbar --}}
        <header class="sticky top-0 z-10 bg-white shadow-sm border-b border-gray-200">
            <div class="flex items-center justify-between px-4 sm:px-6 h-16">
                {{-- Hamburger --}}
                <button onclick="toggleSidebar()" class="lg:hidden p-2 rounded-md text-gray-500 hover:bg-gray-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>

                <h1 class="text-lg font-semibold text-gray-800">@yield('header', 'Dashboard')</h1>

                {{-- Right side --}}
                <div class="flex items-center gap-3">
                    <span class="hidden sm:inline text-sm text-gray-600">{{ auth()->user()->name ?? '' }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                                class="px-3 py-1.5 text-sm bg-red-50 text-red-600 rounded-md hover:bg-red-100 transition">
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </header>

        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="mx-4 sm:mx-6 mt-4">
                <x-alert type="success">{{ session('success') }}</x-alert>
            </div>
        @endif
        @if(session('error'))
            <div class="mx-4 sm:mx-6 mt-4">
                <x-alert type="error">{{ session('error') }}</x-alert>
            </div>
        @endif

        {{-- Page Content --}}
        <main class="flex-1 p-4 sm:p-6">
            @yield('content')
        </main>

        {{-- Footer --}}
        <footer class="border-t border-gray-200 bg-white px-6 py-3 text-center text-xs text-gray-400">
            &copy; {{ date('Y') }} Sistem Kas Sekolah. All rights reserved.
        </footer>
    </div>

    {{-- Floating Toast Notification Container (UX Enhancement) --}}
    <div x-data="toastManager()"
         @toast.window="addToast($event.detail.message, $event.detail.type)"
         class="fixed bottom-5 right-5 z-50 flex flex-col gap-2 max-w-sm w-full pointer-events-none">
        <template x-for="toast in toasts" :key="toast.id">
            <div x-show="toast.visible"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform translate-y-3 scale-95"
                 x-transition:enter-end="opacity-100 transform translate-y-0 scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 transform translate-y-0 scale-100"
                 x-transition:leave-end="opacity-0 transform translate-y-3 scale-95"
                 class="pointer-events-auto p-4 rounded-xl shadow-xl border text-sm flex items-start gap-3 bg-white"
                 :class="{
                     'border-emerald-300 shadow-emerald-500/10': toast.type === 'success',
                     'border-rose-300 shadow-rose-500/10': toast.type === 'error',
                     'border-amber-300 shadow-amber-500/10': toast.type === 'warning',
                     'border-indigo-300 shadow-indigo-500/10': toast.type === 'info',
                 }">
                <span class="text-xl flex-shrink-0" x-text="toast.icon"></span>
                <div class="flex-1 min-w-0">
                    <p class="font-bold text-xs uppercase tracking-wider"
                       :class="{
                           'text-emerald-700': toast.type === 'success',
                           'text-rose-700': toast.type === 'error',
                           'text-amber-700': toast.type === 'warning',
                           'text-indigo-700': toast.type === 'info',
                       }" x-text="toast.title"></p>
                    <p class="text-xs text-gray-700 mt-0.5 leading-relaxed" x-text="toast.message"></p>
                </div>
                <button @click="removeToast(toast.id)" class="text-gray-400 hover:text-gray-600 text-base leading-none">&times;</button>
            </div>
        </template>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        }

        function toastManager() {
            return {
                toasts: [],
                init() {
                    @if(session('success'))
                        this.addToast(@js(session('success')), 'success');
                    @endif
                    @if(session('error'))
                        this.addToast(@js(session('error')), 'error');
                    @endif
                    @if(session('info'))
                        this.addToast(@js(session('info')), 'info');
                    @endif
                    @if(session('warning'))
                        this.addToast(@js(session('warning')), 'warning');
                    @endif
                },
                addToast(message, type = 'success') {
                    const id = Date.now() + Math.random();
                    const icons = { success: '✅', error: '❌', warning: '⚠️', info: 'ℹ️' };
                    const titles = { success: 'Berhasil', error: 'Perhatian', warning: 'Peringatan', info: 'Informasi' };
                    const toast = {
                        id,
                        message,
                        type,
                        icon: icons[type] || 'ℹ️',
                        title: titles[type] || 'Notifikasi',
                        visible: true
                    };
                    this.toasts.push(toast);
                    setTimeout(() => this.removeToast(id), 4500);
                },
                removeToast(id) {
                    const index = this.toasts.findIndex(t => t.id === id);
                    if (index !== -1) {
                        this.toasts[index].visible = false;
                        setTimeout(() => {
                            this.toasts = this.toasts.filter(t => t.id !== id);
                        }, 300);
                    }
                }
            };
        }

        window.toast = function(message, type = 'success') {
            window.dispatchEvent(new CustomEvent('toast', { detail: { message, type } }));
        };
    </script>
</body>
</html>
