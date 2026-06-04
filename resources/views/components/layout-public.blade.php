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
<body class="font-sans antialiased bg-[#f0f4f8]">
    
    <nav x-data="{ isMenuOpen: false, isScrolled: false }"
         @scroll.window="isScrolled = (window.pageYOffset > 10)"
         :class="isScrolled ? 'shadow-md' : ''"
         class="fixed top-0 left-0 right-0 z-[1030] bg-[#b6252a] transition-shadow duration-300">
        <div class="max-w-[1440px] mx-auto px-6 py-3 flex items-center justify-between">
            <!-- Brand -->
            <a href="/" class="flex items-center flex-shrink-0">
                <img src="{{ asset('img/logo_telyu_putih.png') }}" alt="Telkom University" class="h-[48px] w-auto object-contain">
            </a>
            
            <!-- Desktop Menu -->
            <ul class="hidden lg:flex items-center gap-1 m-0 p-0 list-none">
                <li><a href="/" class="block px-4 py-2 text-white text-[15px] font-medium hover:bg-white/10 hover:text-white rounded transition-colors">Home</a></li>
                <li><a href="/matches" class="block px-4 py-2 text-white text-[15px] font-medium hover:bg-white/10 hover:text-white rounded transition-colors">Pertandingan</a></li>
                <li><a href="/bagan" class="block px-4 py-2 text-white text-[15px] font-medium hover:bg-white/10 hover:text-white rounded transition-colors">Bagan</a></li>
                <li><a href="/participants" class="block px-4 py-2 text-white text-[15px] font-medium hover:bg-white/10 hover:text-white rounded transition-colors">Peserta</a></li>
                <li><a href="/galeri" class="block px-4 py-2 text-white text-[15px] font-medium hover:bg-white/10 hover:text-white rounded transition-colors">Galeri</a></li>
                <li>
                    <a href="/login" class="block px-4 py-2 text-white text-[15px] hover:bg-white/10 hover:text-white rounded transition-colors flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </a>
                </li>
            </ul>

            <!-- Mobile Toggle -->
            <button @click="isMenuOpen = !isMenuOpen" 
                    class="lg:hidden flex flex-col justify-center items-center gap-[5px] w-11 h-11 p-2 bg-transparent border-2 border-white/50 rounded-md hover:border-white/85 transition-colors relative z-[1050]">
                <span class="block w-[22px] h-[2px] bg-white rounded-[2px] transition-transform duration-300" :class="isMenuOpen ? 'translate-y-[7px] rotate-45' : ''"></span>
                <span class="block w-[22px] h-[2px] bg-white rounded-[2px] transition-opacity duration-300" :class="isMenuOpen ? 'opacity-0 scale-x-0' : ''"></span>
                <span class="block w-[22px] h-[2px] bg-white rounded-[2px] transition-transform duration-300" :class="isMenuOpen ? '-translate-y-[7px] -rotate-45' : ''"></span>
            </button>
        </div>

        <!-- Mobile Menu -->
        <div x-show="isMenuOpen" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-300"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-2"
             class="lg:hidden fixed inset-0 z-[1040] bg-[#b6252a] flex flex-col justify-center px-8 pt-24 pb-8"
             style="display: none;">
            <ul class="list-none m-0 p-0 flex flex-col gap-4">
                <li><a href="/" class="block pb-2 text-white text-lg font-medium border-b border-white/10 hover:pl-2 transition-all">Home</a></li>
                <li><a href="/matches" class="block pb-2 text-white text-lg font-medium border-b border-white/10 hover:pl-2 transition-all">Pertandingan</a></li>
                <li><a href="/bagan" class="block pb-2 text-white text-lg font-medium border-b border-white/10 hover:pl-2 transition-all">Bagan</a></li>
                <li><a href="/participants" class="block pb-2 text-white text-lg font-medium border-b border-white/10 hover:pl-2 transition-all">Peserta</a></li>
                <li><a href="/galeri" class="block pb-2 text-white text-lg font-medium border-b border-white/10 hover:pl-2 transition-all">Galeri</a></li>
                <li><a href="/login" class="block pb-2 text-white text-lg font-medium border-b border-white/10 flex items-center gap-2 hover:pl-2 transition-all">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> Log In
                </a></li>
            </ul>
        </div>
    </nav>
    <div class="h-[76px]"></div>

    <main class="w-full">
        {{ $slot }}
    </main>

</body>
</html>
