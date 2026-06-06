<div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-sm flex flex-col md:flex-row gap-4 justify-between items-center">
    <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto">
        <div class="relative w-full sm:w-64">
            <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="text" x-model.debounce.300ms="searchQuery" :disabled="isReorderMode" placeholder="Cari judul poster..." class="w-full pl-9 pr-4 py-2 bg-gray-50 border-none rounded-xl text-sm focus:ring-2 focus:ring-[#B41F2A]/20 transition-all disabled:opacity-50">
        </div>
        
        <select x-model="statusFilter" :disabled="isReorderMode" class="w-full sm:w-40 px-3 py-2 bg-gray-50 border-none rounded-xl text-sm focus:ring-2 focus:ring-[#B41F2A]/20 font-medium text-gray-600 disabled:opacity-50">
            <option value="all">Semua Status</option>
            <option value="active">Aktif</option>
            <option value="inactive">Nonaktif</option>
        </select>
    </div>

    <div class="flex items-center gap-3 w-full md:w-auto">
        <button @click="toggleReorderMode()" :class="isReorderMode ? 'bg-gray-800 text-white hover:bg-gray-900' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'" class="flex-1 md:flex-none flex items-center justify-center gap-2 px-4 py-2 rounded-xl text-sm font-bold transition-all" :disabled="posters.length === 0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            <span x-text="isReorderMode ? 'Batal Urutkan' : 'Urutkan'"></span>
        </button>
        
        <button @click="openForm()" x-show="!isReorderMode" class="flex-1 md:flex-none flex items-center justify-center gap-2 px-4 py-2 bg-[#B41F2A] hover:bg-[#961F23] text-white rounded-xl text-sm font-bold transition-all shadow-sm shadow-red-200">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Upload
        </button>
    </div>
</div>
