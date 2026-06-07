<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Tel-U Cup' }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,900&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-[#f0f4f8] flex flex-col min-h-screen">
    
    <!-- Navbar -->
    <nav x-data="{ open: false }" class="bg-[#b6252a] fixed w-full z-50 top-0 shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-20">
                <div class="flex items-center">
                    <a href="/" class="flex-shrink-0 flex items-center">
                        <img src="{{ asset('assets/home_original/logo_telyu_putih.png') }}" class="h-12" alt="Logo">
                    </a>
                </div>

                <!-- Desktop Menu -->
                <div class="hidden lg:flex lg:items-center lg:space-x-8">
                    <a href="/" class="text-white hover:text-gray-200 font-medium {{ request()->is('/') ? 'text-[#ed1e28]' : '' }}">Home</a>
                    <a href="/matches" class="text-white hover:text-gray-200 font-medium">Pertandingan</a>
                    <a href="/bagan" class="text-white hover:text-gray-200 font-medium {{ request()->is('bagan') ? 'text-[#ed1e28]' : '' }}">Bagan</a>
                    <a href="/participants" class="text-white hover:text-gray-200 font-medium {{ request()->is('participants') ? 'text-[#ed1e28]' : '' }}">Peserta</a>
                    <a href="/galeri" class="text-white hover:text-gray-200 font-medium">Galeri</a>
                    
                    @auth
                        @php
                            $dashPath = match(Auth::user()->role) {
                                'admin', 'panitia' => '/dashboard/panitia',
                                'player' => '/dashboard/player',
                                'pic_kontingen', 'pic' => '/dashboard/pic-kontingen',
                                default => '/',
                            };
                        @endphp
                        <a href="{{ $dashPath }}" class="text-white hover:text-gray-200 font-medium">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="text-white hover:text-gray-200 font-medium">Log In</a>
                    @endauth
                </div>

                <!-- Mobile menu button -->
                <div class="flex items-center lg:hidden">
                    <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-white hover:text-gray-200 focus:outline-none">
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div :class="{'block': open, 'hidden': ! open}" class="hidden lg:hidden bg-[#9c1f24]">
            <div class="pt-2 pb-3 space-y-1">
                <a href="/" class="block pl-3 pr-4 py-2 border-l-4 border-transparent text-base font-medium text-white hover:bg-[#851a1e]">Home</a>
                <a href="/matches" class="block pl-3 pr-4 py-2 border-l-4 border-transparent text-base font-medium text-white hover:bg-[#851a1e]">Pertandingan</a>
                <a href="/bagan" class="block pl-3 pr-4 py-2 border-l-4 border-transparent text-base font-medium text-white hover:bg-[#851a1e]">Bagan</a>
                <a href="/participants" class="block pl-3 pr-4 py-2 border-l-4 {{ request()->is('participants') ? 'border-[#ed1e28] text-white bg-[#851a1e]' : 'border-transparent text-white hover:bg-[#851a1e]' }} text-base font-medium">Peserta</a>
                <a href="/galeri" class="block pl-3 pr-4 py-2 border-l-4 border-transparent text-base font-medium text-white hover:bg-[#851a1e]">Galeri</a>
                @auth
                    <a href="{{ $dashPath ?? '/' }}" class="block pl-3 pr-4 py-2 border-l-4 border-transparent text-base font-medium text-white hover:bg-[#851a1e]">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="block pl-3 pr-4 py-2 border-l-4 border-transparent text-base font-medium text-white hover:bg-[#851a1e]">Log In</a>
                @endauth
            </div>
        </div>
    </nav>
    
    <div style="height: 80px;"></div> <!-- Spacer for fixed navbar -->

    <main class="w-full flex-grow">
        {{ $slot }}
    </main>

    <!-- Footer -->
    <footer class="bg-[#b6252a] text-white pt-10 pb-5 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div>
                    <h3 class="text-lg font-bold mb-4">Direktorat SDM Telkom University</h3>
                    <div class="mb-3">Get In Touch</div>
                    <div class="flex items-start mb-4">
                        <svg class="w-5 h-5 mr-3 mt-1 flex-shrink-0" fill="currentColor" viewBox="0 0 320 512"><path d="M320 144C320 223.5 255.5 288 176 288C96.47 288 32 223.5 32 144C32 64.47 96.47 0 176 0C255.5 0 320 64.47 320 144zM192 64C192 55.16 184.8 48 176 48C122.1 48 80 90.98 80 144C80 152.8 87.16 160 96 160C104.8 160 112 152.8 112 144C112 108.7 140.7 80 176 80C184.8 80 192 72.84 192 64zM144 480V317.1C154.4 319 165.1 319.1 176 319.1C186.9 319.1 197.6 319 208 317.1V480C208 497.7 193.7 512 176 512C158.3 512 144 497.7 144 480z"></path></svg>
                        <a href="https://goo.gl/maps/dGCpmcxN4QbHSZe16?coh=178571&amp;entry=tt" class="hover:underline">
                            Gedung E (Kultubai Utara) Lt.2 Jl. Telekomunikasi, Terusan Buah Batu Bandung - 40257 <br>Jawa Barat - Indonesia
                        </a>
                    </div>
                    <div class="flex space-x-4">
                        <a href="mailto:pranbudsdm@telkomuniversity.ac.id?" class="hover:text-gray-300">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 512 512"><path d="M464 64C490.5 64 512 85.49 512 112C512 127.1 504.9 141.3 492.8 150.4L275.2 313.6C263.8 322.1 248.2 322.1 236.8 313.6L19.2 150.4C7.113 141.3 0 127.1 0 112C0 85.49 21.49 64 48 64H464zM217.6 339.2C240.4 356.3 271.6 356.3 294.4 339.2L512 176V384C512 419.3 483.3 448 448 448H64C28.65 448 0 419.3 0 384V176L217.6 339.2z"></path></svg>
                        </a>
                        <a href="https://wa.me/6285173386900" class="hover:text-gray-300">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 448 512"><path d="M380.9 97.1C339 55.1 283.2 32 223.9 32c-122.4 0-222 99.6-222 222 0 39.1 10.2 77.3 29.6 111L0 480l117.7-30.9c32.4 17.7 68.9 27 106.1 27h.1c122.3 0 224.1-99.6 224.1-222 0-59.3-25.2-115-67.1-157zm-157 341.6c-33.2 0-65.7-8.9-94-25.7l-6.7-4-69.8 18.3L72 359.2l-4.4-7c-18.5-29.4-28.2-63.3-28.2-98.2 0-101.7 82.8-184.5 184.6-184.5 49.3 0 95.6 19.2 130.4 54.1 34.8 34.9 56.2 81.2 56.1 130.5 0 101.8-84.9 184.6-186.6 184.6zm101.2-138.2c-5.5-2.8-32.8-16.2-37.9-18-5.1-1.9-8.8-2.8-12.5 2.8-3.7 5.6-14.3 18-17.6 21.8-3.2 3.7-6.5 4.2-12 1.4-32.6-16.3-54-29.1-75.5-66-5.7-9.8 5.7-9.1 16.3-30.3 1.8-3.7 .9-6.9-.5-9.7-1.4-2.8-12.5-30.1-17.1-41.2-4.5-10.8-9.1-9.3-12.5-9.5-3.2-.2-6.9-.2-10.6-.2-3.7 0-9.7 1.4-14.8 6.9-5.1 5.6-19.4 19-19.4 46.3 0 27.3 19.9 53.7 22.6 57.4 2.8 3.7 39.1 59.7 94.8 83.8 35.2 15.2 49 16.5 66.6 13.9 10.7-1.6 32.8-13.4 37.4-26.4 4.6-13 4.6-24.1 3.2-26.4-1.3-2.5-5-3.9-10.5-6.6z"></path></svg>
                        </a>
                        <a href="https://www.instagram.com/heitelkomuniversity/" class="hover:text-gray-300">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 448 512"><path d="M224.1 141c-63.6 0-114.9 51.3-114.9 114.9s51.3 114.9 114.9 114.9S339 319.5 339 255.9 287.7 141 224.1 141zm0 189.6c-41.1 0-74.7-33.5-74.7-74.7s33.5-74.7 74.7-74.7 74.7 33.5 74.7 74.7-33.6 74.7-74.7 74.7zm146.4-194.3c0 14.9-12 26.8-26.8 26.8-14.9 0-26.8-12-26.8-26.8s12-26.8 26.8-26.8 26.8 12 26.8 26.8zm76.1 27.2c-1.7-35.9-9.9-67.7-36.2-93.9-26.2-26.2-58-34.4-93.9-36.2-37-2.1-147.9-2.1-184.9 0-35.8 1.7-67.6 9.9-93.9 36.1s-34.4 58-36.2 93.9c-2.1 37-2.1 147.9 0 184.9 1.7 35.9 9.9 67.7 36.2 93.9s58 34.4 93.9 36.2c37 2.1 147.9 2.1 184.9 0 35.9-1.7 67.7-9.9 93.9-36.2 26.2-26.2 34.4-58 36.2-93.9 2.1-37 2.1-147.8 0-184.8zM398.8 388c-7.8 19.6-22.9 34.7-42.6 42.6-29.5 11.7-99.5 9-132.1 9s-102.7 2.6-132.1-9c-19.6-7.8-34.7-22.9-42.6-42.6-11.7-29.5-9-99.5-9-132.1s-2.6-102.7 9-132.1c7.8-19.6 22.9-34.7 42.6-42.6 29.5-11.7 99.5-9 132.1-9s102.7-2.6 132.1 9c19.6 7.8 34.7 22.9 42.6 42.6 11.7 29.5 9 99.5 9 132.1s2.7 102.7-9 132.1z"></path></svg>
                        </a>
                    </div>
                </div>
            </div>
            
            <hr class="border-red-400 my-8">
            
            <div class="flex flex-col md:flex-row justify-between items-center text-sm">
                <div class="mb-4 md:mb-0">
                    Copyright © Direktorat Sumber Daya Manusia 2026
                </div>
                <div class="flex space-x-4">
                    <a href="#" class="hover:underline">Privacy Policy</a>
                    <span>·</span>
                    <a href="#" class="hover:underline">Terms & Conditions</a>
                    <span>·</span>
                    <a href="/" class="hover:underline">Contact Us</a>
                </div>
            </div>
        </div>
    </footer>

</body>
</html>