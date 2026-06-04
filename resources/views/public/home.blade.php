<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Tel-U Cup — Platform pertandingan olahraga Telkom University">

    <title>Tel-U Cup — Telkom University</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-white">
    {{-- Navbar --}}
    <nav class="bg-white border-b border-gray-100">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <span class="text-xl font-bold text-brand tracking-wide">TEL-U CUP</span>
                </div>
                <div class="flex items-center gap-4">
                    <a href="/galeri" class="text-sm text-gray-600 hover:text-gray-900 font-medium transition-colors">Galeri</a>
                    @auth
                        @php
                            $dashPath = match(Auth::user()->role) {
                                'admin', 'panitia' => '/dashboard/panitia',
                                'player' => '/dashboard/player',
                                'pic_kontingen', 'pic' => '/dashboard/pic-kontingen',
                                default => '/',
                            };
                        @endphp
                        <a href="{{ $dashPath }}" class="btn-brand text-sm">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="btn-brand text-sm">Login</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    {{-- Hero Section --}}
    <section class="relative bg-gradient-to-br from-brand via-[#d32f2f] to-[#e53935] text-white overflow-hidden">
        <div class="absolute inset-0 opacity-10">
            <div class="absolute top-10 left-10 w-40 h-40 bg-white rounded-full blur-3xl"></div>
            <div class="absolute bottom-10 right-10 w-60 h-60 bg-white rounded-full blur-3xl"></div>
        </div>

        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-24 sm:py-32 relative">
            <div class="text-center max-w-3xl mx-auto">
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight mb-6">
                    Tel-U Cup
                </h1>
                <p class="text-lg sm:text-xl text-red-100 mb-10 leading-relaxed max-w-2xl mx-auto">
                    Platform pertandingan olahraga antar fakultas & unit Telkom University.
                    Ikuti jadwal, lihat hasil pertandingan, dan dukung kontingen kamu!
                </p>

                <div class="flex flex-col sm:flex-row justify-center gap-4">
                    <a href="/galeri" class="inline-flex items-center justify-center px-8 py-3 bg-white text-brand font-semibold rounded-lg hover:bg-red-50 transition-colors text-sm">
                        Lihat Galeri
                    </a>
                    @guest
                        <a href="{{ route('login') }}" class="inline-flex items-center justify-center px-8 py-3 border-2 border-white text-white font-semibold rounded-lg hover:bg-white/10 transition-colors text-sm">
                            Login Peserta
                        </a>
                    @endguest
                </div>
            </div>
        </div>
    </section>

    {{-- Features Section --}}
    <section class="py-20 bg-gray-50">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-3">Fitur Utama</h2>
                <p class="text-gray-600 max-w-xl mx-auto">Platform terintegrasi untuk mengelola seluruh aspek pertandingan</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="bg-white rounded-2xl p-8 border border-gray-100 shadow-sm">
                    <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center mb-5">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Jadwal & Bracket</h3>
                    <p class="text-sm text-gray-600 leading-relaxed">
                        Lihat jadwal pertandingan real-time, bagan turnamen, dan hasil pertandingan langsung.
                    </p>
                </div>

                <div class="bg-white rounded-2xl p-8 border border-gray-100 shadow-sm">
                    <div class="w-12 h-12 bg-emerald-50 rounded-xl flex items-center justify-center mb-5">
                        <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Self Assessment</h3>
                    <p class="text-sm text-gray-600 leading-relaxed">
                        Sistem penilaian risiko cedera untuk memastikan keselamatan seluruh peserta.
                    </p>
                </div>

                <div class="bg-white rounded-2xl p-8 border border-gray-100 shadow-sm">
                    <div class="w-12 h-12 bg-purple-50 rounded-xl flex items-center justify-center mb-5">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Smart Gallery</h3>
                    <p class="text-sm text-gray-600 leading-relaxed">
                        Galeri foto event dengan teknologi AI untuk menemukan foto kamu secara otomatis.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- Footer --}}
    <footer class="bg-white border-t border-gray-100 py-8">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <p class="text-sm text-gray-500">
                &copy; {{ date('Y') }} Tel-U Cup &mdash; Telkom University. All rights reserved.
            </p>
        </div>
    </footer>
</body>
</html>
