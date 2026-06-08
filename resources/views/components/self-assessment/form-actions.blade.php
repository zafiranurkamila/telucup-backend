<div class="mt-16 flex justify-end">
    <button type="submit"
            :disabled="isSubmitting"
            class="flex w-full items-center justify-center rounded-md bg-[#B41F2A] px-8 py-4 text-sm font-black text-white transition-colors hover:bg-[#991924] disabled:cursor-not-allowed disabled:opacity-70 sm:w-64">
        <span x-show="!isSubmitting">Lanjut</span>
        <span x-show="isSubmitting" class="flex items-center gap-2" x-cloak>
            <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Memproses
        </span>
    </button>
</div>
