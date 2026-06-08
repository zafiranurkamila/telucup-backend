<div x-show="showAnnouncementModal" 
     style="display: none;" 
     class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm px-4"
     x-transition:enter="ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0">
    <div class="relative w-full max-w-md scale-100 overflow-hidden rounded-2xl bg-white p-8 text-center shadow-2xl transition-all"
         @click.outside="proceedToResult()">
        <div class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-full bg-green-50">
            <svg class="h-10 w-10 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
        </div>
        <span class="mb-2 inline-block rounded-full bg-blue-50 px-3 py-1 text-xs font-bold tracking-wider text-blue-600 uppercase">
            Self Assessment Selesai
        </span>
        <h2 class="mb-4 text-2xl font-bold text-gray-900">Assessment Berhasil Disimpan!</h2>
        <p class="mb-8 text-sm leading-relaxed text-gray-600">
            Hasil analisis risiko kesehatan Anda telah diproses. Klik tombol di bawah untuk melihat hasil evaluasi lengkap.
        </p>
        <div class="flex flex-col gap-3">
            <button @click="proceedToResult()" class="w-full rounded-xl bg-[#B41F2A] px-6 py-3.5 text-sm font-bold text-white shadow-md transition-all hover:bg-[#8A1520] hover:shadow-lg">
                Lihat Hasil Assessment &rarr;
            </button>
            <button @click="proceedToResult()" class="w-full rounded-xl bg-gray-50 px-6 py-3 text-sm font-bold text-gray-600 transition-all hover:bg-gray-100 hover:text-gray-900">
                Tutup
            </button>
        </div>
    </div>
</div>
