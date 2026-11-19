{{-- ponytail: static landing view; ceiling is hardcoded content, upgrade to CMS/DB models if school needs dynamic news & announcement editing --}}
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sekolah Muhammadiyah Setiabudi Pamulang (TK - SD - SMP - SMA)</title>
    <meta name="description" content="Perguruan Muhammadiyah Setiabudi Pamulang - Lembaga pendidikan Islam terpadu jenjang TK ABA, SD, SMP, dan SMA di Pamulang, Tangerang Selatan.">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 text-slate-800 antialiased font-sans selection:bg-emerald-500 selection:text-white">

    <!-- Top Bar Kontak & Lokasi -->
    <div class="bg-emerald-950 text-emerald-200 text-xs py-2 px-4 border-b border-emerald-900">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-2 text-center sm:text-left">
            <div class="flex items-center gap-4 flex-wrap justify-center sm:justify-start">
                <span class="inline-flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Jl. Suryakencana No.29, Pamulang Barat, Kota Tangerang Selatan, Banten 15417
                </span>
                <span class="hidden md:inline text-emerald-700">•</span>
                <a href="tel:0217405324" class="inline-flex items-center gap-1.5 hover:text-white transition">
                    <svg class="w-3.5 h-3.5 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                    (021) 7405324
                </a>
            </div>
            <div class="inline-flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span>Buka Senin – Jumat 07.00 – 15.30 WIB</span>
            </div>
        </div>
    </div>

    <!-- Navigation Header -->
    <header class="sticky top-0 z-40 bg-white/95 backdrop-blur border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between gap-4">
            <a href="{{ url('/') }}" class="flex items-center gap-3 group">
                <div class="w-11 h-11 rounded-xl bg-gradient-to-tr from-emerald-700 to-teal-500 text-white flex items-center justify-center font-bold text-xl shadow-md group-hover:scale-105 transition-transform">
                    M
                </div>
                <div class="flex flex-col">
                    <span class="text-xs uppercase tracking-widest text-emerald-700 font-semibold">Perguruan Muhammadiyah</span>
                    <span class="text-base sm:text-lg font-bold text-slate-900 leading-tight">Setiabudi Pamulang</span>
                </div>
            </a>

            <nav class="hidden lg:flex items-center gap-8 text-sm font-medium text-slate-600">
                <a href="#beranda" class="hover:text-emerald-700 transition">Beranda</a>
                <a href="#jenjang" class="hover:text-emerald-700 transition">Jenjang Pendidikan</a>
                <a href="#keunggulan" class="hover:text-emerald-700 transition">Keunggulan</a>
                <a href="#lokasi" class="hover:text-emerald-700 transition">Lokasi & Peta</a>
            </nav>

            <div class="flex items-center gap-3">
                @auth
                    <a href="{{ url('/dashboard') }}" class="inline-flex items-center gap-2 bg-emerald-700 hover:bg-emerald-800 text-white text-sm font-semibold px-4 py-2.5 rounded-xl shadow-sm transition">
                        <span>Dashboard</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                @else
                    <a href="{{ route('login') }}" class="inline-flex items-center gap-2 bg-slate-900 hover:bg-slate-800 text-white text-sm font-semibold px-4 py-2.5 rounded-xl shadow-sm transition">
                        <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                        <span>Portal Kas Siswa</span>
                    </a>
                @endauth
            </div>
        </div>
    </header>

    <main>
        <!-- Hero Section -->
        <section id="beranda" class="relative overflow-hidden bg-gradient-to-br from-slate-900 via-emerald-950 to-slate-900 text-white py-20 lg:py-28">
            <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#10b981_1px,transparent_1px)] [background-size:16px_16px]"></div>
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <div class="grid lg:grid-cols-12 gap-12 items-center">
                    <div class="lg:col-span-7 space-y-6">
                        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-300 text-xs font-semibold tracking-wide uppercase">
                            <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                            Islami • Unggul • Berkemajuan
                        </div>
                        <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight leading-tight text-white">
                            Sekolah Muhammadiyah <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-400 to-teal-300">Setiabudi Pamulang</span>
                        </h1>
                        <p class="text-base sm:text-lg text-slate-300 leading-relaxed max-w-2xl">
                            Mewujudkan generasi cerdas, berakhlak mulia, dan berdaya saing global melalui pendidikan Islam terpadu jenjang TK, SD, SMP, hingga SMA di Pamulang, Kota Tangerang Selatan.
                        </p>
                        <div class="flex flex-wrap items-center gap-4 pt-2">
                            <a href="#jenjang" class="px-6 py-3 bg-emerald-600 hover:bg-emerald-500 text-white font-semibold rounded-xl shadow-lg shadow-emerald-900/40 transition">
                                Lihat Jenjang Pendidikan
                            </a>
                            <a href="#lokasi" class="px-6 py-3 bg-white/10 hover:bg-white/20 text-white font-semibold rounded-xl border border-white/15 backdrop-blur transition inline-flex items-center gap-2">
                                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                Peta & Lokasi
                            </a>
                            <a href="https://maps.app.goo.gl/5W7e8pw3rLMQ3pZb9" target="_blank" rel="noopener noreferrer" class="px-4 py-3 text-emerald-300 hover:text-white text-sm font-semibold inline-flex items-center gap-1.5 transition">
                                <span>Buka Google Maps</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            </a>
                        </div>
                    </div>

                    <div class="lg:col-span-5">
                        <div class="bg-white/5 border border-white/10 rounded-2xl p-6 sm:p-8 backdrop-blur-md shadow-2xl space-y-6">
                            <h3 class="text-lg font-bold text-white flex items-center gap-2">
                                <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                Profil Singkat Kampus
                            </h3>
                            <div class="space-y-4 text-sm text-slate-300">
                                <div class="flex items-start gap-3">
                                    <span class="p-2 rounded-lg bg-emerald-500/10 text-emerald-400 shrink-0">📍</span>
                                    <div>
                                        <p class="font-semibold text-white">Alamat Kampus</p>
                                        <p class="text-xs text-slate-300 mt-0.5">Jl. Suryakencana No.29, Pamulang Barat, Kec. Pamulang, Kota Tangerang Selatan, Banten 15417</p>
                                    </div>
                                </div>
                                <div class="flex items-start gap-3">
                                    <span class="p-2 rounded-lg bg-emerald-500/10 text-emerald-400 shrink-0">📞</span>
                                    <div>
                                        <p class="font-semibold text-white">Kontak Kantor</p>
                                        <p class="text-xs text-slate-300 mt-0.5">(021) 7405324</p>
                                    </div>
                                </div>
                                <div class="flex items-start gap-3">
                                    <span class="p-2 rounded-lg bg-emerald-500/10 text-emerald-400 shrink-0">🎓</span>
                                    <div>
                                        <p class="font-semibold text-white">4 Jenjang Pendidikan Terpadu</p>
                                        <p class="text-xs text-slate-300 mt-0.5">TK 'Aisyiyah (ABA), SD Muhammadiyah, SMP Muhammadiyah 22, SMA Muhammadiyah 25</p>
                                    </div>
                                </div>
                            </div>
                            <div class="pt-2 border-t border-white/10">
                                <a href="{{ route('login') }}" class="w-full inline-flex items-center justify-center gap-2 py-3 px-4 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold text-sm transition">
                                    <span>Masuk ke Portal Kas & Keuangan Siswa</span>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Jenjang Pendidikan Section -->
        <section id="jenjang" class="py-20 bg-slate-50 border-b border-slate-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-2xl mx-auto mb-14">
                    <span class="text-xs font-bold text-emerald-700 uppercase tracking-widest">Pendidikan Berkelanjutan</span>
                    <h2 class="text-3xl font-extrabold text-slate-900 mt-2">Jenjang Pendidikan Terpadu</h2>
                    <p class="text-slate-600 text-sm mt-3">Mendampingi proses tumbuh kembang peserta didik dari usia dini hingga kesiapan memasuki bangku perkuliahan.</p>
                </div>

                <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <!-- TK ABA -->
                    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm hover:shadow-md transition flex flex-col justify-between">
                        <div>
                            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center font-bold text-xl mb-4">
                                TK
                            </div>
                            <h3 class="text-lg font-bold text-slate-900">TK 'Aisyiyah Bustanul Athfal</h3>
                            <p class="text-xs font-medium text-emerald-700 mt-1 mb-3">Taman Kanak-Kanak (TK ABA)</p>
                            <p class="text-slate-600 text-sm leading-relaxed">
                                Pembiasaan adab islami, stimulasi motorik, keceriaan belajar, dan pengenalan huruf hijaiyah serta doa sehari-hari.
                            </p>
                        </div>
                        <div class="mt-6 pt-4 border-t border-slate-100 text-xs font-semibold text-slate-500">
                            Fokus: Karakter & Kreativitas Usia Dini
                        </div>
                    </div>

                    <!-- SD Muhammadiyah -->
                    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm hover:shadow-md transition flex flex-col justify-between">
                        <div>
                            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center font-bold text-xl mb-4">
                                SD
                            </div>
                            <h3 class="text-lg font-bold text-slate-900">SD Muhammadiyah</h3>
                            <p class="text-xs font-medium text-emerald-700 mt-1 mb-3">Setiabudi Pamulang</p>
                            <p class="text-slate-600 text-sm leading-relaxed">
                                Penguatan literasi, numerasi, sholat berjamaah, tahfidz Juz 'Amma, serta penumbuhan rasa ingin tahu sains.
                            </p>
                        </div>
                        <div class="mt-6 pt-4 border-t border-slate-100 text-xs font-semibold text-slate-500">
                            Fokus: Fondasi Akademik & Adab
                        </div>
                    </div>

                    <!-- SMP Muhammadiyah 22 -->
                    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm hover:shadow-md transition flex flex-col justify-between">
                        <div>
                            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center font-bold text-xl mb-4">
                                SMP
                            </div>
                            <h3 class="text-lg font-bold text-slate-900">SMP Muhammadiyah 22</h3>
                            <p class="text-xs font-medium text-emerald-700 mt-1 mb-3">Pamulang</p>
                            <p class="text-slate-600 text-sm leading-relaxed">
                                Penguatan karakter mandiri, kepanduan Hizbul Wathan, seni bela diri Tapak Suci, dan kemampuan teknologi digital.
                            </p>
                        </div>
                        <div class="mt-6 pt-4 border-t border-slate-100 text-xs font-semibold text-slate-500">
                            Fokus: Disiplin, Sains & Kepanduan
                        </div>
                    </div>

                    <!-- SMA Muhammadiyah 25 -->
                    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm hover:shadow-md transition flex flex-col justify-between">
                        <div>
                            <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-700 flex items-center justify-center font-bold text-xl mb-4">
                                SMA
                            </div>
                            <h3 class="text-lg font-bold text-slate-900">SMA Muhammadiyah 25</h3>
                            <p class="text-xs font-medium text-emerald-700 mt-1 mb-3">Setiabudi Pamulang</p>
                            <p class="text-slate-600 text-sm leading-relaxed">
                                Kesiapan perguruan tinggi, kepemimpinan IPM, riset karya ilmiah, dan pengembangan wawasan global.
                            </p>
                        </div>
                        <div class="mt-6 pt-4 border-t border-slate-100 text-xs font-semibold text-slate-500">
                            Fokus: Prestasi & Kesiapan Kampus
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Keunggulan Section -->
        <section id="keunggulan" class="py-20 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-2xl mx-auto mb-14">
                    <span class="text-xs font-bold text-emerald-700 uppercase tracking-widest">Keunggulan Perguruan</span>
                    <h2 class="text-3xl font-extrabold text-slate-900 mt-2">Mengapa Memilih Kami?</h2>
                    <p class="text-slate-600 text-sm mt-3">Komitmen menghadirkan ekosistem belajar yang asri, islami, aman, dan relevan dengan kompetensi abad 21.</p>
                </div>

                <div class="grid md:grid-cols-3 gap-8">
                    <div class="p-6 rounded-2xl border border-slate-100 bg-slate-50/70 hover:bg-slate-50 transition">
                        <div class="w-10 h-10 rounded-lg bg-emerald-600 text-white flex items-center justify-center font-bold mb-4">
                            1
                        </div>
                        <h4 class="text-base font-bold text-slate-900 mb-2">Integrasi Nilai AIK & Akademik</h4>
                        <p class="text-sm text-slate-600 leading-relaxed">
                            Kurikulum nasional berpadu harmonis dengan Al-Islam, Kemuhammadiyahan, serta Bahasa Arab untuk membina kecerdasan spiritual dan intelektual.
                        </p>
                    </div>

                    <div class="p-6 rounded-2xl border border-slate-100 bg-slate-50/70 hover:bg-slate-50 transition">
                        <div class="w-10 h-10 rounded-lg bg-emerald-600 text-white flex items-center justify-center font-bold mb-4">
                            2
                        </div>
                        <h4 class="text-base font-bold text-slate-900 mb-2">Ekstrakurikuler Khas & Dinamis</h4>
                        <p class="text-sm text-slate-600 leading-relaxed">
                            Bela diri Tapak Suci Putra Muhammadiyah, Kepanduan Hizbul Wathan (HW), robotika, olahraga, seni musik, dan jurnalisme sekolah.
                        </p>
                    </div>

                    <div class="p-6 rounded-2xl border border-slate-100 bg-slate-50/70 hover:bg-slate-50 transition">
                        <div class="w-10 h-10 rounded-lg bg-emerald-600 text-white flex items-center justify-center font-bold mb-4">
                            3
                        </div>
                        <h4 class="text-base font-bold text-slate-900 mb-2">Sistem Digital Transparan</h4>
                        <p class="text-sm text-slate-600 leading-relaxed">
                            Manajemen iuran dan kas sekolah berbasis digital memudahkan pemantauan keuangan, cetak kuitansi, dan pelaporan yang akuntabel.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Lokasi & Peta Section -->
        <section id="lokasi" class="py-20 bg-slate-100 border-t border-slate-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-2xl mx-auto mb-14">
                    <span class="text-xs font-bold text-emerald-700 uppercase tracking-widest">Akses & Lokasi</span>
                    <h2 class="text-3xl font-extrabold text-slate-900 mt-2">Lokasi Kampus Kami</h2>
                    <p class="text-slate-600 text-sm mt-3">Terletak di jalan utama Pamulang Barat dengan akses yang mudah dijangkau dari berbagai wilayah sekitar.</p>
                </div>

                <div class="grid lg:grid-cols-12 gap-8 items-stretch">
                    <!-- Detail Kontak -->
                    <div class="lg:col-span-5 bg-white rounded-2xl border border-slate-200 p-8 shadow-sm flex flex-col justify-between">
                        <div class="space-y-6">
                            <div>
                                <h3 class="text-xl font-bold text-slate-900">Sekolah Muhammadiyah Setiabudi Pamulang</h3>
                                <p class="text-xs text-emerald-700 font-semibold mt-1">Komplek Pendidikan Terpadu TK - SD - SMP - SMA</p>
                            </div>

                            <div class="space-y-4 text-sm text-slate-600">
                                <div>
                                    <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Alamat Kampus</span>
                                    <p class="text-slate-800 mt-1 font-medium">Jl. Suryakencana No.29, Pamulang Barat, Kec. Pamulang, Kota Tangerang Selatan, Banten 15417</p>
                                </div>

                                <div>
                                    <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Telepon Kantor</span>
                                    <p class="text-slate-800 mt-1 font-medium">
                                        <a href="tel:0217405324" class="text-emerald-700 hover:underline">(021) 7405324</a>
                                    </p>
                                </div>

                                <div>
                                    <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Jam Buka</span>
                                    <p class="text-slate-800 mt-1 font-medium">Senin – Jumat: 07.00 – 15.30 WIB</p>
                                </div>
                            </div>
                        </div>

                        <div class="pt-6 border-t border-slate-100 flex flex-col sm:flex-row gap-3">
                            <a href="https://maps.app.goo.gl/5W7e8pw3rLMQ3pZb9" target="_blank" rel="noopener noreferrer" class="flex-1 inline-flex items-center justify-center gap-2 bg-emerald-700 hover:bg-emerald-800 text-white font-semibold text-sm py-3 px-4 rounded-xl shadow-sm transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                <span>Buka Google Maps</span>
                            </a>
                            <a href="tel:0217405324" class="inline-flex items-center justify-center gap-2 bg-slate-100 hover:bg-slate-200 text-slate-800 font-semibold text-sm py-3 px-4 rounded-xl transition">
                                <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                <span>Telepon</span>
                            </a>
                        </div>
                    </div>

                    <!-- Peta Embed Google Maps -->
                    <div class="lg:col-span-7 bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm min-h-[360px] flex flex-col">
                        <iframe
                            title="Peta Lokasi Sekolah Muhammadiyah Setiabudi Pamulang"
                            src="https://maps.google.com/maps?q=-6.3507178,106.7332995&amp;hl=id&amp;z=17&amp;output=embed"
                            class="w-full h-full min-h-[380px] border-0"
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"
                            allowfullscreen>
                        </iframe>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="bg-slate-900 text-slate-400 text-sm py-12 border-t border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-12 gap-8 mb-8">
                <div class="md:col-span-6 space-y-3">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-emerald-700 text-white flex items-center justify-center font-bold text-base">
                            M
                        </div>
                        <span class="text-white font-bold text-base">Sekolah Muhammadiyah Setiabudi Pamulang</span>
                    </div>
                    <p class="text-xs text-slate-400 max-w-md">
                        Menyelenggarakan pendidikan berkualitas dari jenjang TK, SD, SMP, hingga SMA yang berlandaskan nilai-nilai Islam dan semangat kemuhammadiyahan.
                    </p>
                </div>

                <div class="md:col-span-3 space-y-2">
                    <p class="text-xs font-bold text-white uppercase tracking-wider">Tautan</p>
                    <ul class="space-y-1.5 text-xs">
                        <li><a href="#beranda" class="hover:text-emerald-400 transition">Beranda</a></li>
                        <li><a href="#jenjang" class="hover:text-emerald-400 transition">Jenjang Pendidikan</a></li>
                        <li><a href="#keunggulan" class="hover:text-emerald-400 transition">Keunggulan</a></li>
                        <li><a href="#lokasi" class="hover:text-emerald-400 transition">Lokasi & Peta</a></li>
                    </ul>
                </div>

                <div class="md:col-span-3 space-y-2">
                    <p class="text-xs font-bold text-white uppercase tracking-wider">Sistem Informasi</p>
                    <ul class="space-y-1.5 text-xs">
                        <li><a href="{{ route('login') }}" class="hover:text-emerald-400 transition">Portal Kas Siswa</a></li>
                        <li><a href="{{ route('password.request') }}" class="hover:text-emerald-400 transition">Lupa Kata Sandi</a></li>
                    </ul>
                </div>
            </div>

            <div class="pt-8 border-t border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs">
                <p>&copy; {{ date('Y') }} Sekolah Muhammadiyah Setiabudi Pamulang. Hak cipta dilindungi.</p>
                <a href="https://maps.app.goo.gl/5W7e8pw3rLMQ3pZb9" target="_blank" rel="noopener noreferrer" class="text-emerald-400 hover:underline">
                    Buka di Google Maps
                </a>
            </div>
        </div>
    </footer>

</body>
</html>
