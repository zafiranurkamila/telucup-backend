<div class="fixed inset-0 bg-gray-900/60 z-50 flex items-center justify-center p-4 backdrop-blur-sm" @click.self="closeDetail()">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-5xl max-h-[92vh] overflow-hidden flex flex-col" @keyup.escape.window="closeDetail()">
        
        <!-- Modal header -->
        <div class="p-5 border-b border-gray-100 flex items-start justify-between bg-gray-50 shrink-0">
            <div>
                <h2 class="text-lg font-bold text-gray-800" x-text="`Detail Assessment — ${selectedAssessment.player_name || 'Peserta'}`"></h2>
                <p class="text-sm text-gray-500 mt-0.5">
                    <span x-text="[selectedAssessment.contingent, selectedAssessment.sport_branch].filter(Boolean).join(' · ')"></span>
                    <span class="text-gray-400 mx-1">&middot;</span>
                    <span class="text-gray-400" x-text="`#${String(selectedAssessment.id).padStart(5, '0')}`"></span>
                </p>
            </div>
            <button @click="closeDetail()" class="text-gray-400 hover:text-gray-700 hover:bg-gray-200 p-1.5 rounded-full transition-colors mt-0.5">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <!-- Modal body -->
        <div class="flex-1 overflow-y-auto">
            <div class="grid lg:grid-cols-[1.6fr_1fr]">
                <!-- LEFT: Assessment data -->
                <div class="p-6 space-y-5 border-r border-gray-100">
                    
                    <!-- Risk summary card -->
                    <div class="rounded-xl border border-gray-100 bg-white p-5 overflow-hidden relative shadow-sm">
                        <div class="absolute inset-x-0 top-0 h-1" :class="selectedAssessment.risk_label === 'high' ? 'bg-[#B41F2A]' : (selectedAssessment.risk_label === 'medium' ? 'bg-amber-500' : 'bg-green-600')"></div>
                        <div class="flex items-center gap-4 mt-1">
                            <div class="flex-1 min-w-0">
                                <div class="flex flex-wrap items-center gap-2 mb-1.5">
                                    <span :class="getRiskBadgeClass(selectedAssessment.risk_label)" class="px-2.5 py-1 rounded-full text-xs font-bold border" x-text="getRiskLabel(selectedAssessment.risk_label)"></span>
                                </div>
                                <template x-if="selectedAssessment.requires_clearance">
                                    <div class="inline-flex items-center gap-1.5 text-xs font-bold text-[#B41F2A] bg-red-50 border border-red-200 rounded-lg px-2.5 py-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg> Memerlukan clearance medis
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- Domain scores -->
                    <template x-if="selectedAssessment.domain_scores">
                        <div class="rounded-xl border border-gray-100 bg-white p-5">
                            <h3 class="text-sm font-bold text-gray-700 mb-4 flex items-center gap-2">
                                <svg class="w-3.5 h-3.5 text-[#B41F2A]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                Skor Per Domain
                            </h3>
                            <div class="space-y-3.5">
                                <template x-for="(label, key) in {'cardiovascular': 'Kardiovaskular (35%)', 'musculoskeletal': 'Muskuloskeletal (30%)', 'acute_readiness': 'Kesiapan Akut (20%)', 'psychosocial': 'Psikososial (15%)'}" :key="key">
                                    <div>
                                        <div class="flex items-center justify-between mb-1.5">
                                            <span class="text-xs font-medium text-gray-600" x-text="label"></span>
                                            <span class="text-xs font-bold text-gray-700" x-text="(selectedAssessment.domain_scores[key] || 0).toFixed(1)"></span>
                                        </div>
                                        <div class="h-2 w-full rounded-full bg-gray-100 overflow-hidden">
                                            <div class="h-full rounded-full" :class="getDomainBarColor(selectedAssessment.domain_scores[key] || 0)" :style="`width: ${Math.min(selectedAssessment.domain_scores[key] || 0, 100)}%`"></div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                            <p class="mt-3 text-[10px] text-gray-400">0–25 Rendah &middot; 26–50 Sedang &middot; 51–100 Tinggi</p>
                        </div>
                    </template>

                    <!-- Red flags -->
                    <template x-if="(selectedAssessment.red_flags || []).length > 0">
                        <div class="rounded-xl border border-red-200 bg-red-50 p-5">
                            <h3 class="text-sm font-bold text-[#B41F2A] mb-3 flex items-center gap-2">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Red Flag Terdeteksi (<span x-text="selectedAssessment.red_flags.length"></span>)
                            </h3>
                            <div class="space-y-2">
                                <template x-for="f in selectedAssessment.red_flags" :key="f.code">
                                    <div class="bg-white rounded-lg border border-red-100 p-3">
                                        <p class="text-xs font-bold text-gray-800" x-text="f.text"></p>
                                        <template x-if="f.reason">
                                            <p class="text-xs text-[#B41F2A] mt-0.5 leading-relaxed" x-text="f.reason"></p>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>

                    <!-- Yellow flags -->
                    <template x-if="(selectedAssessment.yellow_flags || []).length > 0">
                        <div class="rounded-xl border border-amber-200 bg-amber-50 p-5">
                            <h3 class="text-sm font-bold text-amber-700 mb-3 flex items-center gap-2">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                Yellow Flag (<span x-text="selectedAssessment.yellow_flags.length"></span>)
                            </h3>
                            <div class="space-y-2">
                                <template x-for="f in selectedAssessment.yellow_flags" :key="f.code">
                                    <div class="bg-white rounded-lg border border-amber-100 p-3">
                                        <p class="text-xs font-bold text-gray-800" x-text="f.text"></p>
                                        <template x-if="f.reason">
                                            <p class="text-xs text-amber-700 mt-0.5 leading-relaxed" x-text="f.reason"></p>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>

                    <!-- No flags -->
                    <template x-if="(selectedAssessment.red_flags || []).length === 0 && (selectedAssessment.yellow_flags || []).length === 0">
                        <div class="rounded-xl border border-green-100 bg-green-50 p-4 flex items-center gap-3">
                            <svg class="w-[18px] h-[18px] text-green-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <p class="text-sm font-medium text-green-800">Tidak ada red flag atau yellow flag terdeteksi.</p>
                        </div>
                    </template>

                    <!-- Recommendation -->
                    <template x-if="selectedAssessment.recommendation">
                        <div class="rounded-xl border border-gray-100 bg-gray-50 p-5">
                            <h3 class="text-sm font-bold text-gray-700 mb-2">Rekomendasi Sistem</h3>
                            <p class="text-xs leading-5 text-gray-600 whitespace-pre-line" x-text="selectedAssessment.recommendation"></p>
                        </div>
                    </template>
                </div>

                <!-- RIGHT: Player info + Review form -->
                <div class="p-6 space-y-5">
                    
                    <!-- Player info -->
                    <div class="rounded-xl border border-gray-100 bg-white p-5">
                        <h3 class="text-sm font-bold text-gray-700 mb-4 flex items-center gap-2">
                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            Info Peserta
                        </h3>
                        <div class="grid grid-cols-2 gap-3">
                            <div><p class="text-[10px] font-bold uppercase text-gray-400 mb-0.5">Nama</p><p class="text-xs font-semibold text-gray-800" x-text="selectedAssessment.player_name || '—'"></p></div>
                            <div><p class="text-[10px] font-bold uppercase text-gray-400 mb-0.5">Kontingen</p><p class="text-xs font-semibold text-gray-800" x-text="selectedAssessment.contingent || '—'"></p></div>
                            <div><p class="text-[10px] font-bold uppercase text-gray-400 mb-0.5">Cabang Olahraga</p><p class="text-xs font-semibold text-gray-800" x-text="selectedAssessment.sport_branch || '—'"></p></div>
                            <div><p class="text-[10px] font-bold uppercase text-gray-400 mb-0.5">Usia</p><p class="text-xs font-semibold text-gray-800" x-text="selectedAssessment.snapshot?.age ? `${selectedAssessment.snapshot.age} tahun` : '—'"></p></div>
                            <div><p class="text-[10px] font-bold uppercase text-gray-400 mb-0.5">BMI</p><p class="text-xs font-semibold text-gray-800" x-text="selectedAssessment.snapshot?.bmi != null ? selectedAssessment.snapshot.bmi.toFixed(1) : '—'"></p></div>
                            <div><p class="text-[10px] font-bold uppercase text-gray-400 mb-0.5">Kacamata</p><p class="text-xs font-semibold text-gray-800" x-text="selectedAssessment.snapshot?.is_kacamata ? 'Ya' : 'Tidak'"></p></div>
                        </div>
                        <div class="mt-3 pt-3 border-t border-gray-100 flex flex-wrap gap-x-4 gap-y-1 text-[10px] text-gray-400">
                            <span x-text="`Dibuat: ${formatDate(selectedAssessment.created_at)}`"></span>
                            <template x-if="selectedAssessment.valid_until">
                                <span x-text="`Berlaku: ${formatDateShort(selectedAssessment.valid_until)}`"></span>
                            </template>
                            <span :class="selectedAssessment.is_valid ? 'text-green-600 font-medium' : 'text-red-500 font-medium'" x-text="selectedAssessment.is_valid ? '✓ Masih berlaku' : '✗ Kadaluarsa'"></span>
                        </div>
                    </div>

                    <!-- Current review status (shown if already reviewed) -->
                    <template x-if="selectedAssessment.medical_review?.reviewed_at">
                        <div class="rounded-xl border p-4" :class="selectedAssessment.medical_review.is_allowed_to_play ? 'border-green-200 bg-green-50' : 'border-red-200 bg-red-50'">
                            <p class="text-sm font-bold mb-1" :class="selectedAssessment.medical_review.is_allowed_to_play ? 'text-green-700' : 'text-[#B41F2A]'" x-text="selectedAssessment.medical_review.is_allowed_to_play ? '✓ Review: Diizinkan Bermain' : '✕ Review: Tidak Diizinkan'"></p>
                            <p class="text-[10px] text-gray-500">
                                Direview: <span x-text="formatDate(selectedAssessment.medical_review.reviewed_at)"></span>
                                <template x-if="selectedAssessment.medical_review.pic_confirmed">
                                    <span> &middot; PIC dikonfirmasi</span>
                                </template>
                            </p>
                            <template x-if="selectedAssessment.medical_review.medical_notes">
                                <p class="text-xs text-gray-600 mt-2 italic leading-relaxed" x-text="`&quot;${selectedAssessment.medical_review.medical_notes}&quot;`"></p>
                            </template>
                        </div>
                    </template>

                    <!-- Review form -->
                    <div class="rounded-xl border border-gray-100 bg-white p-5">
                        <h3 class="text-sm font-bold text-gray-700 mb-4 flex items-center gap-2">
                            <svg class="w-3.5 h-3.5 text-[#B41F2A]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" /></svg>
                            <span x-text="selectedAssessment.medical_review?.reviewed_at ? 'Perbarui Review Medis' : 'Berikan Review Medis'"></span>
                        </h3>

                        <!-- Allowed / Disallowed -->
                        <div class="mb-4">
                            <p class="text-[10px] font-bold uppercase tracking-wide text-gray-500 mb-2">Keputusan Medis</p>
                            <div class="flex gap-2">
                                <button @click="reviewAllowed = true" :class="reviewAllowed === true ? 'bg-green-600 text-white border-green-600 shadow-sm' : 'bg-white text-gray-600 border-gray-200 hover:bg-green-50 hover:border-green-300'" class="flex-1 py-2.5 rounded-lg text-sm font-bold border transition-all">
                                    ✓ Izinkan
                                </button>
                                <button @click="reviewAllowed = false" :class="reviewAllowed === false ? 'bg-red-600 text-white border-red-600 shadow-sm' : 'bg-white text-gray-600 border-gray-200 hover:bg-red-50 hover:border-red-300'" class="flex-1 py-2.5 rounded-lg text-sm font-bold border transition-all">
                                    ✕ Larang
                                </button>
                            </div>
                        </div>

                        <!-- Medical notes -->
                        <div class="mb-4">
                            <p class="text-[10px] font-bold uppercase tracking-wide text-gray-500 mb-2">Catatan Medis</p>
                            <textarea x-model="reviewNotes" placeholder="Tuliskan hasil observasi klinis dan rekomendasi tindak lanjut..." rows="4" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm text-gray-800 focus:outline-none focus:border-[#B41F2A] focus:ring-1 focus:ring-[#B41F2A] resize-none"></textarea>
                        </div>

                        <!-- PIC confirmed -->
                        <label class="flex items-center gap-2.5 text-sm text-gray-600 cursor-pointer mb-4 select-none">
                            <input type="checkbox" x-model="reviewPicConfirmed" class="h-4 w-4 text-[#B41F2A] focus:ring-[#B41F2A] border-gray-300 rounded">
                            PIC Kontingen sudah diinformasikan hasil review
                        </label>

                        <!-- Feedback -->
                        <template x-if="reviewFeedback">
                            <div class="mb-4 rounded-lg p-3 text-sm font-medium" :class="reviewFeedback.type === 'success' ? 'bg-green-50 border border-green-200 text-green-700' : 'bg-red-50 border border-red-200 text-red-700'">
                                <span x-text="reviewFeedback.type === 'success' ? '✓ ' : '✕ '"></span>
                                <span x-text="reviewFeedback.msg"></span>
                            </div>
                        </template>

                        <!-- Submit -->
                        <button @click="handleSubmitReview()" :disabled="reviewAllowed === null || isSubmittingReview" class="w-full py-3 bg-gray-900 text-white rounded-lg text-sm font-bold transition-colors hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                            <template x-if="isSubmittingReview">
                                <div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                            </template>
                            <span x-text="isSubmittingReview ? 'Menyimpan...' : 'Simpan Review &rarr;'"></span>
                        </button>

                        <template x-if="reviewAllowed === null">
                            <p class="text-[11px] text-center text-gray-400 mt-2">Pilih keputusan "Izinkan" atau "Larang" terlebih dahulu</p>
                        </template>
                    </div>

                </div>
            </div>
        </div>

        <!-- Modal footer -->
        <div class="p-4 border-t border-gray-100 bg-gray-50 flex justify-end shrink-0">
            <button @click="closeDetail()" class="px-5 py-2.5 rounded-lg font-medium text-gray-600 hover:bg-gray-200 transition-colors text-sm">
                Tutup
            </button>
        </div>
    </div>
</div>
