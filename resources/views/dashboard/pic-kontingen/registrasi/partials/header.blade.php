<div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Registrasi Tim</h1>
        <p class="text-gray-500 text-sm mt-1">Daftarkan tim kontingen Anda ke cabang olahraga yang tersedia.</p>
    </div>
    <button 
        @click="isRegisterModalOpen = true"
        class="flex items-center justify-center gap-2 px-4 py-2 bg-brand hover:bg-brand-hover text-white rounded-lg text-sm font-medium transition-colors shadow-sm"
    >
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Daftarkan Tim Baru
    </button>
</div>
