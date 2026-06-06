<div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Dashboard PIC Kontingen</h1>
        <p class="text-gray-500 text-sm mt-1">
            Kelola kontingen {{ $contingent->name ?? 'Anda' }} dengan mudah.
        </p>
    </div>
    
    {{-- Quick Actions / Shortcuts --}}
    <div class="flex gap-3">
        <a href="{{ url('/dashboard/pic-kontingen/anggota') }}" class="flex items-center gap-2 px-4 py-2 bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 rounded-lg text-sm font-medium transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
            Tambah Anggota
        </a>
        <a href="{{ url('/dashboard/pic-kontingen/registrasi') }}" class="flex items-center gap-2 px-4 py-2 bg-brand hover:bg-brand-hover text-white rounded-lg text-sm font-medium transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
            Registrasi Tim
        </a>
    </div>
</div>
