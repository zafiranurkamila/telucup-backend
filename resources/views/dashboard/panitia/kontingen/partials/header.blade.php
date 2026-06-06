<div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
            <svg class="w-6 h-6 text-brand" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            Manajemen Kontingen & Pengguna
        </h1>
        <p class="text-gray-500 text-sm mt-1">Kelola kontingen, anggota, dan penugasan PIC untuk Tel-U Cup.</p>
    </div>
    <div class="flex items-center gap-3">
        <button @click="isPlayerModalOpen = true" class="px-4 py-2 bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 text-sm font-medium rounded-lg shadow-sm transition-colors flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg> 
            Tambah Pengguna/Player
        </button>
        <button @click="isCreateModalOpen = true" class="px-4 py-2 bg-brand hover:bg-brand-hover text-white text-sm font-medium rounded-lg shadow-sm transition-colors flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg> 
            Tambah Kontingen
        </button>
    </div>
</div>

{{-- Summary Cards --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    <div class="bg-white rounded-xl p-5 border border-gray-100 flex items-start justify-between shadow-sm">
        <div>
            <p class="text-sm font-medium text-gray-500 mb-1">Total Kontingen</p>
            <h3 class="text-3xl font-bold text-gray-900 mb-1" x-text="contingents.length"></h3>
        </div>
        <div class="w-10 h-10 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
        </div>
    </div>
    <div class="bg-white rounded-xl p-5 border border-gray-100 flex items-start justify-between shadow-sm">
        <div>
            <p class="text-sm font-medium text-gray-500 mb-1">Total Anggota</p>
            <h3 class="text-3xl font-bold text-gray-900 mb-1" x-text="contingents.reduce((acc, curr) => acc + (curr.players_count || 0), 0)"></h3>
        </div>
        <div class="w-10 h-10 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
        </div>
    </div>
    <div class="bg-white rounded-xl p-5 border border-gray-100 flex items-start justify-between shadow-sm">
        <div>
            <p class="text-sm font-medium text-gray-500 mb-1">Punya PIC</p>
            <h3 class="text-3xl font-bold text-gray-900 mb-1" x-text="contingents.filter(c => c.pic).length"></h3>
        </div>
        <div class="w-10 h-10 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
    </div>
    <div class="bg-white rounded-xl p-5 border border-gray-100 flex items-start justify-between shadow-sm">
        <div>
            <p class="text-sm font-medium text-gray-500 mb-1">Belum Ada PIC</p>
            <h3 class="text-3xl font-bold text-gray-900 mb-1" x-text="contingents.length - contingents.filter(c => c.pic).length"></h3>
        </div>
        <div class="w-10 h-10 rounded-full bg-red-50 text-red-600 flex items-center justify-center">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7a4 4 0 11-8 0 4 4 0 018 0zm-4 7a6 6 0 00-6 6v1h12v-1a6 6 0 00-6-6zm10 4v-4m0 0l-2 2m2-2l2 2"/></svg>
        </div>
    </div>
</div>
