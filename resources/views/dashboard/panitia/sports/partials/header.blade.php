<div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Pengaturan Cabang Olahraga</h1>
        <p class="text-gray-500 text-sm mt-1">Kelola data cabang olahraga dan kategori perlombaan untuk Tel-U Cup.</p>
    </div>
    <div class="flex items-center gap-3">
        <button @click="handleAddSport()" class="px-4 py-2 bg-brand hover:bg-brand-hover text-white text-sm font-medium rounded-lg shadow-sm transition-colors flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg> 
            Tambah Cabang Olahraga
        </button>
    </div>
</div>
