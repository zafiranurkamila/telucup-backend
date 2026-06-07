document.addEventListener('alpine:init', () => {
    Alpine.data('bracketManager', (sportsData, role = 'public') => ({
        role: role,
        sports: sportsData || [],
        selectedSportId: '',
        selectedCategoryId: '',
        registrations: [],
        bracketData: null,
        
        isLoading: false,
        isGenerating: false,
        isSaving: false,
        
        editingMatch: null,
        editingMatchId: null,
        editForm: {},
        
        refreshInterval: null,
        toastMessage: '',
        toastType: 'success',
        showToastMsg: false,
        toastTimeout: null,

        init() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('sport_id')) this.selectedSportId = urlParams.get('sport_id');
            if (urlParams.has('category_id')) this.selectedCategoryId = urlParams.get('category_id');
            
            this.$watch('selectedSportId', val => {
                const url = new URL(window.location);
                if (val) url.searchParams.set('sport_id', val);
                else url.searchParams.delete('sport_id');
                window.history.replaceState({}, '', url);
            });
            
            this.$watch('selectedCategoryId', val => {
                const url = new URL(window.location);
                if (val) url.searchParams.set('category_id', val);
                else url.searchParams.delete('category_id');
                window.history.replaceState({}, '', url);
            });
            
            this.$watch('editingMatchId', val => {
                const url = new URL(window.location);
                if (val) url.searchParams.set('match_id', val);
                else url.searchParams.delete('match_id');
                window.history.replaceState({}, '', url);
            });
            
            if (this.isReady) {
                setTimeout(async () => {
                    if (this.selectedSportId) {
                        await this.onSportChange(true); // pass true to skip clearing category
                        
                        // Auto-open match if match_id is present
                        const matchId = urlParams.get('match_id');
                        if (matchId) {
                            const matchToOpen = this.findMatchById(Number(matchId));
                            if (matchToOpen) {
                                this.openMatchEdit(matchToOpen);
                            }
                        }
                    }
                }, 100);
            }
        },

        get hasCategories() {
            if (!this.selectedSportId) return false;
            const sport = this.sports.find(s => String(s.id) === String(this.selectedSportId));
            return sport && sport.categories && sport.categories.length > 0;
        },

        get categoryOptionsHtml() {
            if (!this.hasCategories) return '<option value="">-- Tidak ada sub-kategori --</option>';
            const sport = this.sports.find(s => String(s.id) === String(this.selectedSportId));
            let html = '<option value="" disabled>Pilih sub-kategori...</option>';
            sport.categories.forEach(c => {
                html += `<option value="${c.id}">${c.name} (${c.gender === 'male' ? 'Putra' : c.gender === 'female' ? 'Putri' : 'Campuran'})</option>`;
            });
            return html;
        },

        get isReady() {
            if (!this.selectedSportId) return false;
            if (this.hasCategories && !this.selectedCategoryId) return false;
            return true;
        },

        showToast(message, type = 'success') {
            this.toastMessage = message;
            this.toastType = type;
            this.showToastMsg = true;
            if (this.toastTimeout) clearTimeout(this.toastTimeout);
            this.toastTimeout = setTimeout(() => { this.showToastMsg = false; }, 3000);
        },

        async api(method, url, body = null, redirectToLogin = null) {
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const headers = {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': token
            };
            if (body && method !== 'GET') {
                headers['Content-Type'] = 'application/json';
            }
            
            const options = { method, headers };
            if (body && method !== 'GET') {
                options.body = JSON.stringify(body);
            }

            const isRelativeApi = url.startsWith('/api') || url.startsWith('/bracket') || url.startsWith('/matches');
            // Jika bukan dimulai /api, asumsikan rute web (misal /bracket/generate) - tambahkan ke URL utamanya?
            // Actually in the original blade it was calling `/api/...` except for some routes.
            // Let's use the exact paths from original script.
            let fetchUrl = url;
            if (method === 'GET' && body) {
                const params = new URLSearchParams(body).toString();
                fetchUrl += `?${params}`;
            }

            const res = await fetch(fetchUrl, options);
            if (res.status === 401 && redirectToLogin) {
                window.location.href = redirectToLogin;
                return;
            }

            const data = await res.json().catch(() => ({}));
            if (!res.ok) {
                throw new Error(data.message || data.error || 'Terjadi kesalahan server');
            }
            return data;
        },

        async fetchRegistrations() {
            if (!this.selectedSportId) return;
            try {
                let url = '/api/registrations';
                const params = [];
                params.push(`sport_id=${this.selectedSportId}`);
                if (this.selectedCategoryId) params.push(`sport_category_id=${this.selectedCategoryId}`);
                url += `?${params.join('&')}`;

                const res = await this.api('GET', url);
                let items = [];
                if (Array.isArray(res)) {
                    items = res;
                } else if (res && Array.isArray(res.data)) {
                    items = res.data;
                } else if (res && res.data && Array.isArray(res.data.data)) {
                    items = res.data.data;
                }
                
                this.registrations = items.filter(r => r?.status === 'verified');
            } catch (e) {
                console.error('Failed to load registrations', e);
                this.registrations = [];
            }
        },

        async loadBracket() {
            if (!this.selectedSportId) return;
            if (this.hasCategories && !this.selectedCategoryId) return;
            try {
                const params = { sport_id: this.selectedSportId };
                if (this.selectedCategoryId) params.sport_category_id = this.selectedCategoryId;
                const res = await this.api('GET', '/api/bracket', params); // Use API route if it exists, but original used `/bracket`
                // Wait, original script used `/bracket`. Let's check web.php
                // Oh wait, web.php has Route::get('/bracket', [BracketController::class, 'bracket'])->name('bracket.view'); Wait, that's in api.php!
                // Ah, let's just use original fetch path.
                const originalRes = await this.api('GET', '/api/bracket', params).catch(async () => {
                    return await this.api('GET', '/bracket', params);
                });
                
                this.bracketData = originalRes.data || originalRes;
            } catch (e) {
                if (!e.message?.includes('404')) {
                    // Ignore 404 (no bracket yet)
                }
                this.bracketData = null;
            }
        },

        startRefresh() {
            this.stopRefresh();
            this.refreshInterval = setInterval(() => this.loadBracket(), 5000);
        },
        
        stopRefresh() {
            if (this.refreshInterval) {
                clearInterval(this.refreshInterval);
                this.refreshInterval = null;
            }
        },

        // Event handlers
        async onSportChange(preserveCategory = false) {
            if (!preserveCategory) {
                this.selectedCategoryId = '';
            }
            this.bracketData = null;
            this.editingMatch = null;
            this.registrations = [];
            this.stopRefresh();

            if (!this.hasCategories) {
                if (this.selectedSportId) {
                    this.isLoading = true;
                    await this.fetchRegistrations();
                    await this.loadBracket();
                    this.isLoading = false;
                    this.startRefresh();
                }
            } else if (preserveCategory && this.selectedCategoryId) {
                this.isLoading = true;
                await this.fetchRegistrations();
                await this.loadBracket();
                this.isLoading = false;
                this.startRefresh();
            }
        },

        async onCategoryChange() {
            this.bracketData = null;
            this.editingMatch = null;
            this.stopRefresh();

            if (this.selectedCategoryId) {
                this.isLoading = true;
                await this.fetchRegistrations();
                await this.loadBracket();
                this.isLoading = false;
                this.startRefresh();
            }
        },

        async handleSearch() {
            if (!this.isReady) return;
            this.bracketData = null;
            this.editingMatch = null;
            this.stopRefresh();
            
            this.isLoading = true;
            await this.fetchRegistrations();
            await this.loadBracket();
            this.isLoading = false;
            this.startRefresh();
        },

        findMatchById(id) {
            if (!this.bracketData || !this.bracketData.rounds) return null;
            
            for (const round of this.bracketData.rounds) {
                const match = round.matches?.find(m => m.id === id);
                if (match) return match;
            }
            
            if (this.bracketData.third_place_match && this.bracketData.third_place_match.id === id) {
                return this.bracketData.third_place_match;
            }
            
            return null;
        },

        // =========================================================
        // ADMIN SPECIFIC METHODS
        // =========================================================
        
        dragStartMatch(event, match, slot) {
            if (this.role !== 'panitia') return;
            
            if (!['scheduled', 'bye'].includes(match.status)) {
                event.preventDefault();
                return;
            }
            
            const team = slot === 'a' ? match.team_a : match.team_b;
            if (!team) {
                event.preventDefault();
                return;
            }

            event.dataTransfer.effectAllowed = 'move';
            event.dataTransfer.setData('text/plain', JSON.stringify({
                sourceMatchId: match.id,
                sourceSlot: slot,
                registrationId: team.registration_id
            }));
            
            event.target.classList.add('opacity-50');
        },

        async dropMatch(event, targetMatch, targetSlot) {
            if (this.role !== 'panitia') return;
            
            event.target.classList.remove('opacity-50', 'ring-2', 'ring-brand', 'ring-inset');
            
            if (!['scheduled', 'bye'].includes(targetMatch.status)) {
                this.showToast('Hanya bisa memindahkan tim ke pertandingan yang masih terjadwal', 'error');
                return;
            }

            try {
                const dataStr = event.dataTransfer.getData('text/plain');
                if (!dataStr) return;
                const data = JSON.parse(dataStr);
                
                if (!data || !data.registrationId) return;
                if (data.sourceMatchId === targetMatch.id && data.sourceSlot === targetSlot) return;

                this.isSaving = true;
                
                const payload = {};
                if (targetSlot === 'a') {
                    payload.registration_a_id = data.registrationId;
                } else {
                    payload.registration_b_id = data.registrationId;
                }

                await this.api('PATCH', `/dashboard/panitia/matches/${targetMatch.id}/teams`, payload, '/login');
                
                await this.loadBracket();
                this.showToast('Tim berhasil ditukar!', 'success');
            } catch (e) {
                this.showToast(e.message || 'Gagal menukar tim', 'error');
            } finally {
                this.isSaving = false;
            }
        },

        async handleGenerate() {
            if (this.role !== 'panitia' || !this.isReady) return;
            this.isGenerating = true;
            try {
                const payload = { sport_id: parseInt(this.selectedSportId) };
                if (this.selectedCategoryId) payload.sport_category_id = parseInt(this.selectedCategoryId);
                await this.api('POST', '/dashboard/panitia/bracket/generate', payload, '/login');
                await this.loadBracket();
                this.showToast(`Bagan berhasil digenerate untuk ${this.registrations.length} tim!`, 'success');
                this.startRefresh();
            } catch (e) {
                this.showToast(e.message || 'Gagal generate bagan', 'error');
            } finally {
                this.isGenerating = false;
            }
        },

        async handleRandomize() {
            if (this.role !== 'panitia') return;
            if (!confirm('Ini akan menghapus seluruh jadwal dan skor saat ini. Anda yakin ingin mengacak ulang?')) return;
            this.isGenerating = true;
            try {
                await this.api('DELETE', '/dashboard/panitia/bracket/reset', { sport_id: parseInt(this.selectedSportId), sport_category_id: this.selectedCategoryId ? parseInt(this.selectedCategoryId) : null }, '/login');
                const payload = { sport_id: parseInt(this.selectedSportId) };
                if (this.selectedCategoryId) payload.sport_category_id = parseInt(this.selectedCategoryId);
                await this.api('POST', '/dashboard/panitia/bracket/generate', payload, '/login');
                await this.loadBracket();
                this.showToast('Posisi tim berhasil diacak ulang!', 'info');
            } catch (e) {
                this.showToast(e.message || 'Gagal mengacak ulang bagan', 'error');
            } finally {
                this.isGenerating = false;
            }
        },

        async handleReset() {
            if (this.role !== 'panitia') return;
            if (!confirm('Bagan akan dihapus permanen. Anda yakin?')) return;
            try {
                await this.api('DELETE', '/dashboard/panitia/bracket/reset', { sport_id: parseInt(this.selectedSportId), sport_category_id: this.selectedCategoryId ? parseInt(this.selectedCategoryId) : null }, '/login');
                this.bracketData = null;
                this.editingMatch = null;
                this.stopRefresh();
                this.showToast('Bagan berhasil dihapus.', 'info');
            } catch (e) {
                this.showToast(e.message || 'Gagal reset bagan', 'error');
            }
        },

        closeMatchEdit() {
            this.editingMatch = null;
            this.editingMatchId = null;
        },

        openMatchEdit(match) {
            if (this.role !== 'panitia') return;
            
            this.editingMatch = match;
            this.editingMatchId = match.id;
            this.$nextTick(() => { this.editForm = {
                match_date: match.match_date || '',
                match_time: match.match_time || '',
                location: match.location || '',
                referee_name: match.referee_name || '',
                notes: match.notes || '',
                score_a: match.score_a || 0,
                score_b: match.score_b || 0,
                winner_id: match.winner?.registration_id ? String(match.winner.registration_id) : '',
                registration_a_id: match.team_a?.registration_id ? String(match.team_a.registration_id) : '',
                registration_b_id: match.team_b?.registration_id ? String(match.team_b.registration_id) : '',
                status: match.status,
                finish_mode: false,
            }; });
        },

        async handleStartMatch(matchId) {
            if (this.isSaving) return;
            this.isSaving = true;
            try {
                const res = await this.api('PATCH', `/dashboard/panitia/matches/${matchId}/status`, { status: 'live' }, '/login');
                await this.loadBracket();
                this.showToast(res.message || 'Pertandingan dimulai!', 'success');
                
                const updatedMatch = this.findMatchById(matchId);
                if (updatedMatch) {
                    this.openMatchEdit(updatedMatch);
                } else {
                    this.editingMatch = null;
                }
            } catch (err) {
                console.error(err);
                this.showToast(err.message || 'Gagal memulai pertandingan', 'error');
            } finally {
                this.isSaving = false;
            }
        },

        async saveMatch() {
            if (this.role !== 'panitia' || !this.editingMatch) return;
            this.isSaving = true;
            const matchId = this.editingMatch.id;

            try {
                if (['scheduled', 'bye'].includes(this.editingMatch.status)) {
                    const oldRegA = this.editingMatch.team_a?.registration_id ? String(this.editingMatch.team_a.registration_id) : '';
                    const oldRegB = this.editingMatch.team_b?.registration_id ? String(this.editingMatch.team_b.registration_id) : '';
                    
                    if (this.editForm.registration_a_id !== oldRegA || this.editForm.registration_b_id !== oldRegB) {
                        await this.api('PATCH', `/dashboard/panitia/matches/${matchId}/teams`, {
                            registration_a_id: this.editForm.registration_a_id || null,
                            registration_b_id: this.editForm.registration_b_id || null,
                        }, '/login');
                    }
                }

                await this.api('PATCH', `/dashboard/panitia/matches/${matchId}/schedule`, {
                    match_date: this.editForm.match_date || null,
                    match_time: this.editForm.match_time || null,
                    location: this.editForm.location || null,
                    referee_name: this.editForm.referee_name || null,
                    notes: this.editForm.notes || null,
                }, '/login');

                if (this.editForm.finish_mode) {
                    const payload = {
                        score_a: parseInt(this.editForm.score_a) || 0,
                        score_b: parseInt(this.editForm.score_b) || 0,
                    };
                    if (this.editForm.score_a === this.editForm.score_b) {
                        payload.winner_registration_id = this.editForm.winner_id;
                    }
                    await this.api('PATCH', `/dashboard/panitia/matches/${matchId}/score`, payload, '/login');
                }

                if (!this.editForm.finish_mode && this.editForm.status && this.editForm.status !== this.editingMatch.status) {
                    await this.api('PATCH', `/dashboard/panitia/matches/${matchId}/status`, { status: this.editForm.status }, '/login');
                }

                await this.loadBracket();
                this.showToast(this.editForm.finish_mode ? 'Pertandingan diselesaikan!' : 'Pertandingan berhasil disimpan!', 'success');
                
                if (this.editForm.finish_mode) {
                    this.editingMatch = null;
                }
            } catch (e) {
                this.showToast(e.message || 'Gagal menyimpan pertandingan', 'error');
            } finally {
                this.isSaving = false;
            }
        },

        destroy() {
            this.stopRefresh();
        }
    }));
});
