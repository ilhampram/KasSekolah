@extends('layouts.app')

@section('title', 'Audit Log & Jejak Aktivitas')
@section('header', 'Audit Log')

@section('content')
    <div x-data="{ modalOpen: false, modalTitle: '', modalContent: '' }">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div>
                <h2 class="text-xl font-bold text-gray-800">Jejak Aktivitas & Audit Log</h2>
                <p class="text-sm text-gray-500">Catatan kronologis perubahan data krusial, penghapusan, pembatalan (*void*), dan autentikasi.</p>
            </div>
            <div class="text-xs text-gray-500 bg-gray-100 px-3 py-1.5 rounded-lg border border-gray-200">
                🔒 Khusus Hak Akses Administrator
            </div>
        </div>

        {{-- Filter & Search --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6">
            <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                <div>
                    <input type="text" name="search" value="{{ request('search') }}"
                           class="w-full rounded-lg border-gray-300 text-sm px-4 py-2 border focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"
                           placeholder="Cari aksi, IP, nama...">
                </div>

                <div>
                    <select name="event"
                            class="w-full rounded-lg border-gray-300 text-sm px-4 py-2 border focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                        <option value="">Semua Aksi / Event</option>
                        @foreach($distinctEvents as $ev)
                            <option value="{{ $ev }}" {{ request('event') === $ev ? 'selected' : '' }}>
                                {{ $ev }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <select name="user_id"
                            class="w-full rounded-lg border-gray-300 text-sm px-4 py-2 border focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                        <option value="">Semua Pelaku / User</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>
                                {{ $u->name }} ({{ $u->role }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <input type="date" name="start_date" value="{{ request('start_date') }}"
                           class="w-full rounded-lg border-gray-300 text-sm px-3 py-2 border focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"
                           title="Dari Tanggal">
                </div>

                <div class="flex gap-2">
                    <button type="submit"
                            class="flex-1 px-4 py-2 bg-indigo-600 text-white text-sm font-semibold rounded-lg hover:bg-indigo-700 transition">
                        🔍 Filter
                    </button>
                    @if(request()->hasAny(['search', 'event', 'user_id', 'start_date', 'end_date']))
                        <a href="{{ route('admin.audit-logs.index') }}"
                           class="px-3 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition text-center">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Table --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Waktu</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Pelaku</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi / Event</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Subjek</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Detail / Parameter</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">IP Address</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($logs as $log)
                            @php
                                $isDestructive = str_contains($log->event, 'deleted') || str_contains($log->event, 'voided') || str_contains($log->event, 'rejected') || str_contains($log->event, 'failed');
                                $isSuccess = str_contains($log->event, 'created') || str_contains($log->event, 'verified') || str_contains($log->event, 'succeeded');
                            @endphp
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-xs font-bold text-gray-800">{{ $log->created_at->format('d/m/Y H:i:s') }}</div>
                                    <div class="text-[11px] text-gray-400 mt-0.5">{{ $log->created_at->diffForHumans() }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    @if($log->user)
                                        <div class="font-medium text-gray-800">{{ $log->user->name }}</div>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-gray-100 text-gray-600">
                                            {{ $log->user->role }}
                                        </span>
                                    @else
                                        <span class="text-gray-400 italic text-xs">Sistem / Guest</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $isDestructive ? 'bg-rose-100 text-rose-800' : ($isSuccess ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800') }}">
                                        {{ $log->event }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-xs text-gray-600">
                                    @if($log->subject_type)
                                        <span class="font-mono text-gray-500">{{ class_basename($log->subject_type) }}</span>
                                        <span class="font-bold text-gray-700">#{{ $log->subject_id }}</span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-xs">
                                    @if(!empty($log->properties))
                                        <button type="button"
                                                @click="modalOpen = true; modalTitle = '{{ $log->event }} (ID: {{ $log->id }})'; modalContent = JSON.stringify({{ json_encode($log->properties) }}, null, 2)"
                                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded bg-indigo-50 text-indigo-700 hover:bg-indigo-100 font-medium transition">
                                            📋 Lihat Detail ({{ count($log->properties) }})
                                        </button>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right font-mono text-xs text-gray-500 whitespace-nowrap">
                                    {{ $log->ip_address ?? '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gray-400">
                                    <p class="text-3xl mb-2">📜</p>
                                    <p class="font-medium text-gray-600">Belum ada catatan log aktivitas.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($logs->hasPages())
                <div class="px-6 py-4 border-t border-gray-100">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>

        {{-- JSON Detail Modal --}}
        <div x-show="modalOpen" x-cloak
             class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-black/50"
             @keydown.escape.window="modalOpen = false">
            <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl border border-gray-100" @click.away="modalOpen = false">
                <div class="flex items-center justify-between border-b pb-3 mb-4">
                    <h3 class="font-bold text-gray-800 text-sm" x-text="'Payload Audit: ' + modalTitle"></h3>
                    <button @click="modalOpen = false" class="text-gray-400 hover:text-gray-600 text-lg font-bold">✕</button>
                </div>
                <pre class="bg-gray-900 text-emerald-400 p-4 rounded-xl text-xs overflow-x-auto max-h-96 font-mono leading-relaxed" x-text="modalContent"></pre>
                <div class="mt-4 text-right">
                    <button type="button" @click="modalOpen = false"
                            class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded-lg transition">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection
