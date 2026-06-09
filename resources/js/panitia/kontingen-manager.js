document.addEventListener('alpine:init', () => {
    Alpine.data('kontingenManager', () => ({
        activeTab: 'kontingen',
        isLoading: true,
        toast: { show: false, message: '', type: 'success' },

        contingents: [],
        players: [],
        pics: [],

        search: '',
        filterPic: 'all',
        filterPlayer: 'all',

        // Modals state
        isCreateModalOpen: false,
        editContingentData: null,
        deleteContingentData: null,
        isPlayerModalOpen: false,
        assignPicContingentData: null,
        assignPlayerToPicData: null,
        assignContingentToPlayerData: null,
        detailContingentData: null,

        // Forms state
        contingentForm: { name: '', image: null },
        playerForm: { name: '', email: '', password: '', role: 'player' },
        assignPicForm: { user_id: '' },
        assignContingentForm: { contingent_id: '' },
        
        isSubmitting: false,

        init() {
            this.fetchData();
        },

        showToast(message, type = 'success') {
            this.toast = { show: true, message, type };
            setTimeout(() => { this.toast.show = false; }, 3000);
        },

        async fetchApi(url, options = {}) {
            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const headers = {
                    'Accept': 'application/json',
                    ...options.headers
                };

                // Add CSRF token for mutations
                if (options.method && options.method !== 'GET') {
                    if (options.body instanceof FormData) {
                        options.body.append('_token', token);
                    } else {
                        headers['X-CSRF-TOKEN'] = token;
                        headers['Content-Type'] = 'application/json';
                    }
                }

                const res = await fetch(url, { ...options, headers });
                const data = await res.json().catch(() => ({}));

                if (!res.ok) {
                    throw new Error(data.message || 'Terjadi kesalahan server.');
                }
                return data;
            } catch (err) {
                this.showToast(err.message, 'error');
                throw err;
            }
        },

        async fetchData() {
            this.isLoading = true;
            try {
                const [contRes, playRes, picRes] = await Promise.all([
                    this.fetchApi('/api/contingents'),
                    this.fetchApi('/api/players'),
                    this.fetchApi('/api/pic-kontingen')
                ]);
                this.contingents = contRes.data || contRes || [];
                this.players = Array.isArray(playRes) ? playRes : (playRes.data || []);
                this.pics = Array.isArray(picRes) ? picRes : (picRes.data || []);
            } catch (e) {
                console.error(e);
            } finally {
                this.isLoading = false;
            }
        },

        // --- Derived & Filtered Data ---
        get filteredContingents() {
            return this.contingents.filter(c => {
                const q = this.search.toLowerCase();
                const matchSearch = c.name.toLowerCase().includes(q) || (c.pic?.name || '').toLowerCase().includes(q);
                const matchFilter = this.filterPic === 'all' ? true :
                                    this.filterPic === 'has_pic' ? !!c.pic : !c.pic;
                return matchSearch && matchFilter;
            });
        },

        get filteredPlayers() {
            return this.players.filter(p => {
                const q = this.search.toLowerCase();
                const matchSearch = (p.name || '').toLowerCase().includes(q) || (p.user?.email || '').toLowerCase().includes(q);
                const matchFilter = this.filterPlayer === 'all' ? true :
                                    this.filterPlayer === 'no_contingent' ? !p.contingent_id : !!p.contingent_id;
                return matchSearch && matchFilter;
            });
        },

        get filteredPics() {
            return this.pics.filter(p => {
                const q = this.search.toLowerCase();
                return (p.name || '').toLowerCase().includes(q) || (p.email || '').toLowerCase().includes(q);
            });
        },

        // --- Contingent Actions ---
        async handleCreateContingent() {
            if (!this.contingentForm.name) return;
            this.isSubmitting = true;
            try {
                const res = await this.fetchApi('/api/contingents', {
                    method: 'POST',
                    body: JSON.stringify({ name: this.contingentForm.name })
                });
                
                // If there's an image, upload it
                if (this.contingentForm.image && res.data?.id) {
                    const fd = new FormData();
                    fd.append('image', this.contingentForm.image);
                    await this.fetchApi(`/api/contingents/${res.data.id}/image`, {
                        method: 'POST',
                        body: fd
                    });
                }
                
                this.showToast('Kontingen berhasil ditambahkan');
                this.isCreateModalOpen = false;
                this.contingentForm = { name: '', image: null };
                this.fetchData();
            } finally {
                this.isSubmitting = false;
            }
        },

        openEditContingent(item) {
            this.editContingentData = item;
            this.contingentForm = { name: item.name, image: null };
        },

        async handleEditContingent() {
            if (!this.contingentForm.name || !this.editContingentData) return;
            this.isSubmitting = true;
            try {
                await this.fetchApi(`/api/contingents/${this.editContingentData.id}`, {
                    method: 'PUT',
                    body: JSON.stringify({ name: this.contingentForm.name })
                });
                
                if (this.contingentForm.image) {
                    const fd = new FormData();
                    fd.append('image', this.contingentForm.image);
                    await this.fetchApi(`/api/contingents/${this.editContingentData.id}/image`, {
                        method: 'POST',
                        body: fd
                    });
                }
                
                this.showToast('Kontingen berhasil diupdate');
                this.editContingentData = null;
                this.contingentForm = { name: '', image: null };
                this.fetchData();
            } finally {
                this.isSubmitting = false;
            }
        },

        async handleDeleteContingent() {
            if (!this.deleteContingentData) return;
            this.isSubmitting = true;
            try {
                await this.fetchApi(`/api/contingents/${this.deleteContingentData.id}`, {
                    method: 'DELETE'
                });
                this.showToast('Kontingen berhasil dihapus');
                this.deleteContingentData = null;
                this.fetchData();
            } finally {
                this.isSubmitting = false;
            }
        },

        // --- Assign PIC ---
        openAssignPic(item) {
            this.assignPicContingentData = item;
            this.assignPicForm.user_id = item.pic?.id || '';
        },

        async handleAssignPic() {
            if (!this.assignPicContingentData || !this.assignPicForm.user_id) return;
            this.isSubmitting = true;
            try {
                await this.fetchApi(`/api/contingents/${this.assignPicContingentData.id}/assign-pic`, {
                    method: 'PUT',
                    body: JSON.stringify({ user_id: this.assignPicForm.user_id })
                });
                this.showToast('PIC berhasil ditugaskan');
                this.assignPicContingentData = null;
                this.assignPicForm.user_id = '';
                this.fetchData();
            } finally {
                this.isSubmitting = false;
            }
        },

        // --- Player Actions ---
        async handleCreatePlayer() {
            if (!this.playerForm.name || !this.playerForm.email || !this.playerForm.password) return;
            this.isSubmitting = true;
            try {
                const res = await this.fetchApi('/api/players', {
                    method: 'POST',
                    body: JSON.stringify(this.playerForm)
                });
                this.showToast('Pengguna berhasil ditambahkan');
                this.isPlayerModalOpen = false;
                this.playerForm = { name: '', email: '', password: '', role: 'player' };
                this.fetchData();
                
                // Prompt to assign PIC
                if (confirm(`Berhasil! Apakah Anda ingin menugaskan ${res.user?.name || 'User ini'} sebagai PIC kontingen?`)) {
                    this.showToast(`Silakan klik "Assign PIC" pada tabel Kontingen.`);
                }
            } finally {
                this.isSubmitting = false;
            }
        },

        async handlePromoteToPic(player) {
            if (!confirm(`Promosikan ${player.name} menjadi PIC Kontingen?`)) return;
            
            // Perlu user_id dari player object
            const userId = player.user_id || player.user?.id;
            if (!userId) {
                this.showToast('ID User tidak ditemukan untuk player ini.', 'error');
                return;
            }

            try {
                const res = await this.fetchApi(`/api/admin/users/${userId}/promote-to-pic`, {
                    method: 'PUT'
                });
                this.showToast(res.message || 'User berhasil dipromosikan menjadi PIC');
                this.fetchData();
            } catch (err) {
                // error already shown in fetchApi
            }
        },

        openAssignPlayerContingent(player) {
            this.assignContingentToPlayerData = player;
            this.assignContingentForm.contingent_id = player.contingent_id || '';
        },

        async handleAssignPlayerContingent() {
            if (!this.assignContingentToPlayerData || !this.assignContingentForm.contingent_id) return;
            this.isSubmitting = true;
            try {
                await this.fetchApi(`/api/players/${this.assignContingentToPlayerData.id}/assign-contingent`, {
                    method: 'PUT',
                    body: JSON.stringify({ contingent_id: this.assignContingentForm.contingent_id })
                });
                this.showToast('Kontingen berhasil di-assign ke player');
                this.assignContingentToPlayerData = null;
                this.assignContingentForm.contingent_id = '';
                this.fetchData();
            } finally {
                this.isSubmitting = false;
            }
        },

        // Helpers
        getRiskBadgeClasses(risk_lvl) {
            switch (risk_lvl) {
                case 'low': return 'bg-emerald-50 text-emerald-700 border-emerald-200';
                case 'medium': return 'bg-orange-50 text-orange-700 border-orange-200';
                case 'high': return 'bg-red-50 text-red-700 border-red-200';
                default: return 'bg-gray-50 text-gray-500 border-gray-200';
            }
        },
        
        getRiskBadgeText(risk_lvl) {
            switch (risk_lvl) {
                case 'low': return 'Risiko Rendah';
                case 'medium': return 'Risiko Sedang';
                case 'high': return 'Risiko Tinggi';
                default: return 'Belum Mengisi';
            }
        }
    }));
});
