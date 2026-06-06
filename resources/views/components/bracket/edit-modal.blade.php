<div
    x-show="editingMatch"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-50 flex items-center justify-center px-4"
    style="display: none;"
>
    <div class="fixed inset-0 bg-black/50" @click="closeMatchEdit()"></div>

    <div
        x-show="editingMatch"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        class="relative bg-white rounded-2xl shadow-xl max-w-lg w-full max-h-[90vh] overflow-y-auto z-10"
    >
        <template x-if="editingMatch">
            <div>
                {{-- Modal header --}}
                <div class="flex items-center justify-between p-5 border-b border-gray-100">
                    <h3 class="text-lg font-bold text-gray-900">
                        Edit Pertandingan <span class="text-brand" x-text="'#' + editingMatch.match_number"></span>
                    </h3>
                    <button @click="closeMatchEdit()" class="p-1 rounded-lg hover:bg-gray-100 transition-colors">
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- Modal body --}}
                <div class="p-5 space-y-5">
                    {{-- Teams --}}
                    <div class="bg-gray-50 rounded-xl p-4">
                        <div class="flex items-center justify-between mb-3 gap-3">
                            <div class="flex-1">
                                <label class="block text-[10px] text-center text-gray-400 uppercase mb-1">Tim A</label>
                                <div x-show="editingMatch && ['scheduled', 'bye'].includes(editingMatch.status)">
                                    <select x-model="editForm.registration_a_id" class="w-full border border-gray-200 rounded-lg px-2 py-1.5 text-sm focus:ring-2 focus:ring-red-500/20 focus:border-red-500 focus:outline-none">
                                        <option value="">-- TBD --</option>
                                        <template x-for="reg in registrations" :key="reg.id">
                                            <option :value="String(reg.id)" x-text="reg.contingent ? reg.contingent.name : 'Unknown'"></option>
                                        </template>
                                    </select>
                                </div>
                                <div x-show="editingMatch && !['scheduled', 'bye'].includes(editingMatch.status)">
                                    <div class="text-sm font-bold text-gray-800 text-center" x-text="editingMatch?.team_a?.contingent?.name ?? 'TBD'"></div>
                                </div>
                            </div>
                            <div class="text-xl font-black text-gray-900 shrink-0">VS</div>
                            <div class="flex-1">
                                <label class="block text-[10px] text-center text-gray-400 uppercase mb-1">Tim B</label>
                                <div x-show="editingMatch && ['scheduled', 'bye'].includes(editingMatch.status)">
                                    <select x-model="editForm.registration_b_id" class="w-full border border-gray-200 rounded-lg px-2 py-1.5 text-sm focus:ring-2 focus:ring-red-500/20 focus:border-red-500 focus:outline-none">
                                        <option value="">-- TBD --</option>
                                        <template x-for="reg in registrations" :key="reg.id">
                                            <option :value="String(reg.id)" x-text="reg.contingent ? reg.contingent.name : 'Unknown'"></option>
                                        </template>
                                    </select>
                                </div>
                                <div x-show="editingMatch && !['scheduled', 'bye'].includes(editingMatch.status)">
                                    <div class="text-sm font-bold text-gray-800 text-center" x-text="editingMatch?.team_b?.contingent?.name ?? 'TBD'"></div>
                                </div>
                            </div>
                        </div>
                        
                        {{-- Tombol Lihat Check-in Pemain --}}
                        <template x-if="editingMatch && editingMatch.status !== 'bye' && (editForm.registration_a_id || editingMatch.team_a?.registration_id) && (editForm.registration_b_id || editingMatch.team_b?.registration_id)">
                            <a
                                :href="'/dashboard/panitia/verifikasi?match_id=' + editingMatch.id + '&sport_id=' + (selectedSportId || '') + '&category_id=' + (selectedCategoryId || '')"
                                class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-blue-600 bg-blue-50 border border-blue-200 rounded-lg hover:bg-blue-100 transition-colors w-full sm:w-auto justify-center"
                            >
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Lihat Check-in Pemain
                            </a>
                        </template>
                    </div>

                    {{-- Schedule fields & Status --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 mb-1">Status Pertandingan</label>
                            <select x-model="editForm.status" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500/20 focus:border-red-500 focus:outline-none" :disabled="['bye'].includes(editingMatch.status)">
                                <option value="scheduled">Terjadwal</option>
                                <option value="live">Sedang Bermain (Live)</option>
                                <option value="finished">Selesai</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 mb-1">Tanggal</label>
                            <input type="date" x-model="editForm.match_date" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500/20 focus:border-red-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 mb-1">Waktu</label>
                            <input type="time" x-model="editForm.match_time" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500/20 focus:border-red-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 mb-1">Lokasi</label>
                            <input type="text" x-model="editForm.location" placeholder="Gedung Sport Center Lt. 2" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500/20 focus:border-red-500 focus:outline-none">
                        </div>
                    </div>

                    {{-- Live Score Input (jika live atau finish) --}}
                    <template x-if="['live', 'finished'].includes(editForm.status)">
                        <div class="bg-red-50 border border-red-100 rounded-xl p-4 mt-4">
                            <label class="block text-xs font-bold text-red-800 uppercase tracking-wider mb-3 text-center">Live Score</label>
                            <div class="flex items-center justify-center gap-6">
                                <div class="text-center">
                                    <div class="text-[10px] text-red-600 font-medium mb-1 truncate w-20" x-text="editingMatch?.team_a?.contingent?.name ?? 'Tim A'"></div>
                                    <input type="number" x-model="editForm.score_a" class="w-20 text-center text-2xl font-black border border-red-200 rounded-lg py-2 focus:outline-none focus:border-red-500 focus:ring-2 focus:ring-red-200" min="0">
                                </div>
                                <div class="text-xl font-black text-red-300">-</div>
                                <div class="text-center">
                                    <div class="text-[10px] text-red-600 font-medium mb-1 truncate w-20" x-text="editingMatch?.team_b?.contingent?.name ?? 'Tim B'"></div>
                                    <input type="number" x-model="editForm.score_b" class="w-20 text-center text-2xl font-black border border-red-200 rounded-lg py-2 focus:outline-none focus:border-red-500 focus:ring-2 focus:ring-red-200" min="0">
                                </div>
                            </div>

                            <template x-if="editForm.score_a == editForm.score_b && editForm.finish_mode">
                                <div class="mt-4 pt-4 border-t border-red-200/50">
                                    <label class="block text-xs font-semibold text-red-800 mb-2 text-center">Karena skor seri, pilih pemenang:</label>
                                    <select x-model="editForm.winner_id" class="w-full border border-red-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500/20 focus:border-red-500 focus:outline-none bg-white">
                                        <option value="">-- Pilih Pemenang --</option>
                                        <template x-if="editingMatch.team_a">
                                            <option :value="String(editingMatch.team_a.registration_id)" x-text="editingMatch.team_a.contingent?.name"></option>
                                        </template>
                                        <template x-if="editingMatch.team_b">
                                            <option :value="String(editingMatch.team_b.registration_id)" x-text="editingMatch.team_b.contingent?.name"></option>
                                        </template>
                                    </select>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>

                {{-- Modal footer --}}
                <div class="p-5 border-t border-gray-100 bg-gray-50 flex flex-col sm:flex-row gap-3 justify-end items-center rounded-b-2xl">
                    <template x-if="editForm.status === 'scheduled'">
                        <button
                            @click="handleStartMatch(editingMatch.id)"
                            :disabled="isSaving"
                            class="w-full sm:w-auto px-4 py-2.5 text-sm font-bold text-white bg-red-600 hover:bg-red-700 rounded-lg transition-colors flex items-center justify-center gap-2"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Mulai Pertandingan
                        </button>
                    </template>
                    
                    <div class="flex items-center gap-3 w-full sm:w-auto mt-2 sm:mt-0">
                        <button @click="closeMatchEdit()" class="w-full sm:w-auto px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                            Batal
                        </button>

                        <template x-if="editForm.status === 'live'">
                            <button
                                @click="editForm.finish_mode = true; saveMatch()"
                                :disabled="isSaving"
                                class="w-full sm:w-auto px-4 py-2.5 text-sm font-bold text-white bg-green-600 hover:bg-green-700 rounded-lg transition-colors"
                            >
                                <span x-show="!isSaving">Selesaikan Pertandingan</span>
                                <span x-show="isSaving">Menyimpan...</span>
                            </button>
                        </template>

                        <button
                            @click="saveMatch()"
                            :disabled="isSaving"
                            class="w-full sm:w-auto px-4 py-2.5 text-sm font-bold text-white bg-brand hover:bg-brand-hover rounded-lg transition-colors"
                        >
                            <span x-show="!isSaving">Simpan Perubahan</span>
                            <span x-show="isSaving">Menyimpan...</span>
                        </button>
                    </div>
                </div>
            </div>
        </template>
    </div>
</div>
