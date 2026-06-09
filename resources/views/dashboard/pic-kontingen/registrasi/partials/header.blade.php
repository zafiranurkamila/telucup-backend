<div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Registrasi Tim</h1>
        <p class="text-gray-500 text-sm mt-1">Daftarkan tim kontingen Anda ke cabang olahraga yang tersedia.</p>
    </div>
    <button 
        @click="availableSports.length > 0 ? openModal('createTeamModal') : null" 
        :disabled="availableSports.length === 0"
        :class="availableSports.length === 0 ? 'bg-gray-400 cursor-not-allowed shadow-none' : 'bg-brand hover:bg-red-700 shadow-sm shadow-red-200'"
        class="inline-flex items-center gap-2 px-4 py-2.5 text-white text-sm font-medium rounded-xl transition-colors">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
        </svg>
        Buat Draft Tim Baru
    </button>
</div>
