<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Dashboard' }} — Tel-U Cup</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-[#f4f7f6]">
    <div x-data="{ sidebarOpen: false }" class="flex flex-col h-screen overflow-hidden">

        {{-- ============================================================
             Top Navbar
             ============================================================ --}}
        <header class="h-[60px] bg-[#a81d22] text-white flex items-center justify-between px-4 lg:px-6 shadow-md shrink-0 z-50 relative">
            <div class="flex items-center gap-3">
                {{-- Mobile hamburger --}}
                <button
                    @click="sidebarOpen = true"
                    class="lg:hidden p-1 rounded hover:bg-white/10 transition-colors"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>

                <span class="font-bold text-[16px] tracking-wide">TEL-U CUP</span>
            </div>

            {{-- User info --}}
            <div class="flex items-center gap-3">
                <div class="text-right hidden sm:block">
                    <div class="text-[13px] font-semibold tracking-wider uppercase">
                        {{ Auth::user()->name }}
                    </div>
                    <div class="text-[11px] text-red-200 capitalize">
                        {{ $roleLabel ?? Auth::user()->role }} &bull; {{ Auth::user()->email }}
                    </div>
                </div>
                <div class="w-8 h-8 rounded bg-[#89a2cc] flex items-center justify-center border border-white/20">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
            </div>
        </header>

        {{-- ============================================================
             Body: Sidebar + Content
             ============================================================ --}}
        <div class="flex flex-1 overflow-hidden relative">

            {{-- Mobile overlay --}}
            <div
                x-show="sidebarOpen"
                x-transition:enter="transition-opacity ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition-opacity ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                @click="sidebarOpen = false"
                class="fixed inset-0 bg-black/50 z-40 lg:hidden"
                style="display: none;"
            ></div>

            {{-- Sidebar --}}
            <aside
                :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
                class="absolute lg:static inset-y-0 left-0 z-40 w-[260px] bg-white shadow-[2px_0_15px_rgba(0,0,0,0.03)] transform transition-transform duration-300 ease-in-out lg:translate-x-0 flex flex-col"
            >
                <nav class="flex-1 overflow-y-auto py-5">
                    <ul class="space-y-1 px-3">
                        {{ $sidebar }}
                    </ul>
                </nav>

                {{-- Logout --}}
                <div class="p-4 border-t border-gray-100">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="flex items-center gap-3 px-3 py-2.5 w-full rounded-lg transition-colors text-red-600 hover:bg-red-50 font-medium">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            <span class="text-sm">Keluar</span>
                        </button>
                    </form>
                </div>
            </aside>

            {{-- Main Content --}}
            <main class="flex-1 overflow-x-hidden overflow-y-auto p-6 lg:p-8">
                {{-- Flash Messages --}}
                @if (session('success'))
                    <x-alert type="success" class="mb-6">{{ session('success') }}</x-alert>
                @endif

                @if (session('error'))
                    <x-alert type="error" class="mb-6">{{ session('error') }}</x-alert>
                @endif

                @if (session('warning'))
                    <x-alert type="warning" class="mb-6">{{ session('warning') }}</x-alert>
                @endif

                {{ $slot }}
            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
