<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    <div class="bg-white rounded-xl p-5 border border-gray-100 flex items-start justify-between shadow-sm">
        <div>
            <p class="text-sm font-medium text-gray-500 mb-1">Total Anggota</p>
            <h3 class="text-3xl font-bold text-gray-900 mb-1">{{ $playerCount }}</h3>
            <p class="text-xs text-gray-400">Pemain terdaftar</p>
        </div>
        <div class="w-12 h-12 rounded-full flex items-center justify-center bg-blue-50 text-blue-600">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
        </div>
    </div>

    <div class="bg-white rounded-xl p-5 border border-gray-100 flex items-start justify-between shadow-sm">
        <div>
            <p class="text-sm font-medium text-gray-500 mb-1">Tim Terdaftar</p>
            <h3 class="text-3xl font-bold text-gray-900 mb-1">{{ $registrationCount }}</h3>
            <p class="text-xs text-gray-400">Dari semua cabang olahraga</p>
        </div>
        <div class="w-12 h-12 rounded-full flex items-center justify-center bg-purple-50 text-purple-600">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
        </div>
    </div>

    <div class="bg-white rounded-xl p-5 border border-gray-100 flex items-start justify-between shadow-sm">
        <div>
            <p class="text-sm font-medium text-gray-500 mb-1">Pertandingan Hari Ini</p>
            <h3 class="text-3xl font-bold text-gray-900 mb-1">{{ count($todayMatches) }}</h3>
            <p class="text-xs text-gray-400">Jadwal aktif</p>
        </div>
        <div class="w-12 h-12 rounded-full flex items-center justify-center bg-emerald-50 text-emerald-600">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
        </div>
    </div>

    <div class="bg-white rounded-xl p-5 border border-orange-200 ring-1 ring-orange-100 shadow-[0_0_15px_rgba(249,115,22,0.1)] flex items-start justify-between">
        <div>
            <p class="text-sm font-medium text-gray-500 mb-1">Menunggu Verifikasi</p>
            <h3 class="text-3xl font-bold text-gray-900 mb-1">{{ $waitingVerificationCount }}</h3>
            <p class="text-xs text-gray-400">Tim perlu ditinjau</p>
        </div>
        <div class="w-12 h-12 rounded-full flex items-center justify-center bg-orange-50 text-orange-600">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
    </div>
</div>
