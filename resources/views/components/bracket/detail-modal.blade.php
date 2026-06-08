<div
    x-show="detailMatch"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-50 flex items-center justify-center px-4"
    style="display: none;"
>
    <div class="fixed inset-0 bg-black/50" @click="closeMatchDetail()"></div>

    <div
        x-show="detailMatch"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        class="relative bg-white rounded-2xl shadow-xl max-w-lg w-full max-h-[90vh] overflow-y-auto z-10"
    >
        <template x-if="detailMatch">
            <div>
                {{-- Modal header --}}
                <div class="flex items-center justify-between p-5 border-b border-gray-100">
                    <h3 class="text-lg font-bold text-gray-900">
                        Detail Pertandingan <span class="text-brand" x-text="'#' + detailMatch.match_number"></span>
                    </h3>
                    <button @click="closeMatchDetail()" class="p-1 rounded-lg hover:bg-gray-100 transition-colors">
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- Modal body --}}
                <div class="p-5 space-y-5">
                    {{-- Teams --}}
                    <div class="bg-gray-50 rounded-xl p-4">
                        <div class="flex items-center justify-between mb-3 gap-3">
                            <div class="flex-1">
                                <label class="block text-[10px] text-center text-gray-400 uppercase mb-2">Tim A</label>
                                <div class="flex flex-col items-center">
                                    <img :src="detailMatch.team_a?.contingent?.logo_url || 'https://ui-avatars.com/api/?name=' + (detailMatch.team_a?.contingent?.name || 'A') + '&background=f3f4f6&color=9ca3af'" class="w-12 h-12 rounded-full object-cover mb-2 border-2 border-white shadow-sm">
                                    <div class="text-sm font-bold text-gray-800 text-center leading-tight" x-text="detailMatch.team_a?.contingent?.name ?? 'TBD'"></div>
                                </div>
                            </div>
                            <div class="text-xl font-black text-gray-900 shrink-0">VS</div>
                            <div class="flex-1">
                                <label class="block text-[10px] text-center text-gray-400 uppercase mb-2">Tim B</label>
                                <div class="flex flex-col items-center">
                                    <img :src="detailMatch.team_b?.contingent?.logo_url || 'https://ui-avatars.com/api/?name=' + (detailMatch.team_b?.contingent?.name || 'B') + '&background=f3f4f6&color=9ca3af'" class="w-12 h-12 rounded-full object-cover mb-2 border-2 border-white shadow-sm">
                                    <div class="text-sm font-bold text-gray-800 text-center leading-tight" x-text="detailMatch.team_b?.contingent?.name ?? 'TBD'"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Schedule fields & Status --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-white border border-gray-100 rounded-xl p-3 shadow-sm">
                            <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Status</label>
                            <div class="text-sm font-semibold text-gray-800" x-text="detailMatch.status === 'finished' ? 'Selesai' : (detailMatch.status === 'live' ? 'Sedang Bermain' : (detailMatch.status === 'scheduled' ? 'Terjadwal' : 'Bye'))"></div>
                        </div>
                        <div class="bg-white border border-gray-100 rounded-xl p-3 shadow-sm">
                            <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Tanggal</label>
                            <div class="text-sm font-semibold text-gray-800" x-text="detailMatch.match_date ? (new Date(detailMatch.match_date).toLocaleDateString('id-ID', {day:'numeric', month:'long', year:'numeric'})) : '-'"></div>
                        </div>
                        <div class="bg-white border border-gray-100 rounded-xl p-3 shadow-sm">
                            <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Waktu</label>
                            <div class="text-sm font-semibold text-gray-800" x-text="detailMatch.match_time ? detailMatch.match_time : '-'"></div>
                        </div>
                        <div class="bg-white border border-gray-100 rounded-xl p-3 shadow-sm">
                            <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Lokasi</label>
                            <div class="text-sm font-semibold text-gray-800 truncate" :title="detailMatch.location" x-text="detailMatch.location ? detailMatch.location : '-'"></div>
                        </div>
                    </div>

                    {{-- Score --}}
                    <template x-if="['live', 'finished'].includes(detailMatch.status)">
                        <div class="bg-brand/5 border border-brand/20 rounded-xl p-4 mt-4">
                            <label class="block text-xs font-bold text-brand uppercase tracking-wider mb-3 text-center" x-text="detailMatch.status === 'live' ? 'Live Score' : 'Skor Akhir'"></label>
                            <div class="flex items-center justify-center gap-6">
                                <div class="text-center">
                                    <div class="text-[10px] text-gray-500 font-medium mb-1 truncate w-20" x-text="detailMatch.team_a?.contingent?.name ?? 'Tim A'"></div>
                                    <div class="w-20 text-center text-3xl font-black text-gray-900 py-2" x-text="detailMatch.score_a !== null ? detailMatch.score_a : '-'"></div>
                                </div>
                                <div class="text-xl font-black text-gray-300">-</div>
                                <div class="text-center">
                                    <div class="text-[10px] text-gray-500 font-medium mb-1 truncate w-20" x-text="detailMatch.team_b?.contingent?.name ?? 'Tim B'"></div>
                                    <div class="w-20 text-center text-3xl font-black text-gray-900 py-2" x-text="detailMatch.score_b !== null ? detailMatch.score_b : '-'"></div>
                                </div>
                            </div>

                            <template x-if="detailMatch.winner">
                                <div class="mt-4 pt-4 border-t border-brand/20 text-center">
                                    <span class="inline-flex items-center gap-1.5 bg-brand text-white text-xs font-bold px-3 py-1 rounded-full shadow-sm">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        Pemenang: <span x-text="detailMatch.winner.contingent?.name"></span>
                                    </span>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>

                {{-- Modal footer --}}
                <div class="p-5 border-t border-gray-100 bg-gray-50 flex justify-end items-center rounded-b-2xl">
                    <button @click="closeMatchDetail()" class="px-5 py-2.5 text-sm font-bold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors w-full sm:w-auto">
                        Tutup
                    </button>
                </div>
            </div>
        </template>
    </div>
</div>
