<section class="rounded-xl border border-[#B41F2A]/20 bg-red-50/30 p-6 shadow-sm mb-6">
    <div class="mb-4 flex items-center gap-3">
        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#B41F2A] text-white">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <h2 class="text-lg font-bold text-gray-900">Pernyataan Persetujuan</h2>
    </div>
    <div class="pl-11">
        <label class="flex cursor-pointer items-start gap-3 group">
            <div class="pt-0.5">
                <input type="checkbox" 
                       x-model="answers['consent']" 
                       required 
                       class="h-5 w-5 rounded border-gray-300 accent-[#B41F2A] focus:ring-[#B41F2A] transition-colors" />
            </div>
            <p class="text-sm leading-relaxed text-gray-700 group-hover:text-gray-900 transition-colors">
                Saya menyatakan bahwa semua informasi yang saya berikan dalam formulir ini adalah
                <span class="font-bold text-[#B41F2A]">benar dan akurat</span> sesuai dengan kondisi saya saat ini. Saya memahami bahwa menyembunyikan kondisi medis dapat membahayakan keselamatan saya sendiri selama pertandingan.
            </p>
        </label>
    </div>
</section>
