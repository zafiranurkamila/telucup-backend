document.addEventListener('alpine:init', () => {
    Alpine.data('registrasiManager', () => ({
        teams: [],
        availableSports: [],
        contingentMembers: [],
        
        isLoading: true,
        isSubmitting: false,
        searchTerm: '',
        statusFilter: '',
        sportFilter: '',
        
        // Modal States
        isRegisterModalOpen: false,
        isManageModalOpen: false,
        
        // Form States
        selectedSport: '',
        selectedCategory: '',
        activeTeamId: null,
        playerSearchTerm: '',

        init() {
            this.fetchData();
        },

        async fetchApi(url, options = {}) {
            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const headers = {
                    'Accept': 'application/json',
                    ...options.headers
                };

                if (options.method && options.method !== 'GET') {
                    headers['X-CSRF-TOKEN'] = token;
                    headers['Content-Type'] = 'application/json';
                }

                const res = await fetch(url, { ...options, headers });
                const data = await res.json().catch(() => ({}));

                if (!res.ok) {
                    throw new Error(data.message || 'Terjadi kesalahan server.');
                }
                return data;
            } catch (err) {
                alert(err.message);
                throw err;
            }
        },

        async fetchData() {
            this.isLoading = true;
            try {
                const [regRes, sportsRes, playersRes] = await Promise.all([
                    this.fetchApi('/api/registrations/my'),
                    this.fetchApi('/api/sports'),
                    this.fetchApi('/api/contingents/my/players')
                ]);
                this.teams = regRes.data || regRes || [];
                this.availableSports = sportsRes.data || sportsRes || [];
                this.contingentMembers = playersRes.data || playersRes || [];
            } catch (error) {
                console.error("Failed to load data", error);
            } finally {
                this.isLoading = false;
            }
        },

        get filteredTeams() {
            return this.teams.filter(team => {
                const sportName = team.sport?.name || "";
                const catName = team.sport_category?.name || "";
                const fullName = `${sportName} ${catName}`.toLowerCase();
                const matchSearch = fullName.includes(this.searchTerm.toLowerCase());
                
                const matchStatus = this.statusFilter ? team.status === this.statusFilter : true;
                const matchSport = this.sportFilter ? team.sport?.id == this.sportFilter : true;
                return matchSearch && matchStatus && matchSport;
            });
        },

        get activeTeam() {
            return this.teams.find(t => t.id === this.activeTeamId);
        },

        get selectedSportObj() {
            return this.unregisteredSports.find(s => s.id === parseInt(this.selectedSport));
        },

        get unregisteredSports() {
            return this.availableSports.filter(sport => {
                const registeredTeamsForSport = this.teams.filter(t => t.sport?.id === sport.id);
                if (!sport.categories || sport.categories.length === 0) {
                    return registeredTeamsForSport.length === 0;
                } else {
                    return registeredTeamsForSport.length < sport.categories.length;
                }
            }).map(sport => {
                if (sport.categories && sport.categories.length > 0) {
                    const registeredCategoryIds = this.teams
                        .filter(t => t.sport?.id === sport.id)
                        .map(t => t.sport_category?.id);
                    return {
                        ...sport,
                        categories: sport.categories.filter(cat => !registeredCategoryIds.includes(cat.id))
                    };
                }
                return sport;
            });
        },

        get availablePlayers() {
            if (!this.activeTeam) return [];
            return this.contingentMembers.filter(member => {
                const isAlreadyInTeam = this.activeTeam.players.some(p => p.id === member.id);
                if (isAlreadyInTeam) return false;

                const q = this.playerSearchTerm.toLowerCase();
                const matchSearch = (member.name || '').toLowerCase().includes(q) || 
                                    (member.nim_nip || '').toLowerCase().includes(q);
                return matchSearch;
            });
        },

        async handleRegisterTeam() {
            if (!this.selectedSport) return;
            
            this.isSubmitting = true;
            try {
                const payload = {
                    sport_id: parseInt(this.selectedSport),
                    player_ids: []
                };
                if (this.selectedCategory) {
                    payload.sport_category_id = parseInt(this.selectedCategory);
                }

                await this.fetchApi('/api/registrations', {
                    method: 'POST',
                    body: JSON.stringify(payload)
                });
                
                await this.fetchData();
                this.isRegisterModalOpen = false;
                this.selectedSport = '';
                this.selectedCategory = '';
            } catch (error) {
                // error already handled by fetchApi
            } finally {
                this.isSubmitting = false;
            }
        },

        async handleAddPlayer(playerId) {
            if (!this.activeTeamId) return;
            try {
                await this.fetchApi(`/api/registrations/${this.activeTeamId}/players`, {
                    method: 'POST',
                    body: JSON.stringify({ player_ids: [playerId] })
                });
                await this.fetchData();
            } catch (error) {
                // error already handled by fetchApi
            }
        },

        async handleRemovePlayer(teamId, playerId) {
            if (!confirm("Apakah Anda yakin ingin menghapus pemain ini dari tim?")) return;
            try {
                await this.fetchApi(`/api/registrations/${teamId}/players/${playerId}`, {
                    method: 'DELETE'
                });
                await this.fetchData();
            } catch (error) {
                // error already handled by fetchApi
            }
        },

        async handleSubmitTeam(teamId) {
            if (!confirm("Apakah Anda yakin ingin mengajukan pendaftaran tim ini ke panitia? Anda tidak dapat mengubah anggota setelah diajukan.")) return;
            try {
                await this.fetchApi(`/api/registrations/${teamId}/submit`, {
                    method: 'POST' // Note: the backend uses POST for /submit
                });
                await this.fetchData();
                this.isManageModalOpen = false;
            } catch (error) {
                // error already handled by fetchApi
            }
        },

        // UI Helpers
        getStatusDisplay(status) {
            switch (status) {
                case "draft": return "Draft";
                case "submitted": return "Menunggu Verifikasi";
                case "verified": return "Terverifikasi";
                case "rejected": return "Ditolak";
                default: return status;
            }
        },

        getStatusStyle(status) {
            switch (status) {
                case "verified": return "bg-green-100 text-green-700 border-green-200";
                case "submitted": return "bg-yellow-100 text-yellow-700 border-yellow-200";
                case "draft": return "bg-gray-100 text-gray-700 border-gray-200";
                case "rejected": return "bg-red-100 text-red-700 border-red-200";
                default: return "bg-gray-100 text-gray-700 border-gray-200";
            }
        },

        getRiskBadgeStyle(risk_lvl) {
            switch (risk_lvl) {
                case "low": return "bg-emerald-50 text-emerald-700 border-emerald-200";
                case "medium": return "bg-orange-50 text-orange-700 border-orange-200";
                case "high": return "bg-red-50 text-red-700 border-red-200";
                case "not_yet":
                default: return "bg-gray-50 text-gray-500 border-gray-200";
            }
        },

        getRiskBadgeText(risk_lvl) {
            switch (risk_lvl) {
                case "low": return "Risiko Rendah";
                case "medium": return "Risiko Sedang";
                case "high": return "Risiko Tinggi";
                case "not_yet":
                default: return "Belum Mengisi";
            }
        }
    }));
});
