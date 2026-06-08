<div class="sticky bottom-6 z-10 rounded-xl bg-white/80 p-4 shadow-lg backdrop-blur-md border border-gray-100 flex items-center justify-between mt-8">
    <button type="button" 
            @click="window.history.back()" 
            class="px-6 py-2.5 text-sm font-semibold text-gray-600 hover:text-gray-900 transition-colors">
        Batal
    </button>
    <button type="submit" 
            :disabled="isSubmitting"
            class="flex items-center gap-2 rounded-lg bg-[#B41F2A] px-8 py-2.5 text-sm font-bold text-white shadow-md hover:bg-[#8A1520] hover:shadow-lg transition-all disabled:opacity-70 disabled:cursor-not-allowed">
        <span x-show="!isSubmitting">Kirim Assessment</span>
        <span x-show="isSubmitting" class="flex items-center gap-2" x-cloak>
            <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Memproses...
        </span>
    </button>
</div>
