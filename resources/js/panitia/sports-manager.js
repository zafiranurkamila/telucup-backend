document.addEventListener('alpine:init', () => {
    Alpine.data('sportsManager', () => ({
        sports: [],
        isLoading: true,
        search: '',
        toast: { show: false, message: '', type: 'success' },

        isFormModalOpen: false,
        sportToDelete: null,
        
        // Form State
        sportToEdit: null,
        formData: {
            name: '',
            icon: null,
            max_members: '',
            categories: []
        },
        isSubmitting: false,
        error: null,

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
                const isFormData = options.body instanceof FormData;
                const headers = {
                    'Accept': 'application/json',
                    ...options.headers
                };

                if (options.method && options.method !== 'GET') {
                    if (isFormData) {
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
                if (options.throwError) throw err;
                this.showToast(err.message, 'error');
                throw err;
            }
        },

        async fetchData() {
            this.isLoading = true;
            try {
                const res = await this.fetchApi('/api/sports');
                this.sports = res.data || res || [];
            } catch (e) {
                console.error(e);
            } finally {
                this.isLoading = false;
            }
        },

        get filteredSports() {
            return this.sports.filter(s => s.name.toLowerCase().includes(this.search.toLowerCase()));
        },

        handleAddSport() {
            this.error = null;
            this.sportToEdit = null;
            this.formData = { name: '', icon: null, max_members: '', categories: [] };
            this.isFormModalOpen = true;
        },

        handleEditSport(sport) {
            this.error = null;
            this.sportToEdit = sport;
            this.formData = {
                name: sport.name || '',
                icon: null, // intentionally not pre-filling file
                max_members: sport.max_members ? sport.max_members.toString() : '',
                categories: (sport.categories || []).map(cat => ({
                    id: cat.id,
                    name: cat.name,
                    max_members: cat.max_members ? cat.max_members.toString() : ''
                }))
            };
            this.isFormModalOpen = true;
        },

        addCategory() {
            this.formData.categories.push({ name: '', max_members: '' });
        },

        removeCategory(index) {
            this.formData.categories.splice(index, 1);
        },

        handleIconChange(e) {
            if (e.target.files && e.target.files.length > 0) {
                this.formData.icon = e.target.files[0];
            } else {
                this.formData.icon = null;
            }
        },

        async handleSubmitForm() {
            if (!this.formData.name.trim()) {
                this.error = "Nama Cabang Olahraga wajib diisi.";
                return;
            }

            const payload = new FormData();
            payload.append("name", this.formData.name.trim());
            
            if (this.formData.max_members) {
                payload.append("max_members", this.formData.max_members);
            }
            if (this.formData.icon) {
                payload.append("icon", this.formData.icon);
            }

            this.formData.categories.forEach((c, index) => {
                if (c.id) payload.append(`categories[${index}][id]`, c.id);
                payload.append(`categories[${index}][name]`, c.name.trim());
                if (c.max_members) payload.append(`categories[${index}][max_members]`, c.max_members);
            });

            this.isSubmitting = true;
            this.error = null;

            try {
                if (this.sportToEdit) {
                    payload.append('_method', 'PUT'); // Laravel method spoofing for multipart/form-data
                    await this.fetchApi(`/api/sports/${this.sportToEdit.id}`, {
                        method: 'POST',
                        body: payload,
                        throwError: true
                    });
                } else {
                    await this.fetchApi('/api/sports', {
                        method: 'POST',
                        body: payload,
                        throwError: true
                    });
                }
                this.showToast('Data Cabang Olahraga berhasil disimpan');
                this.isFormModalOpen = false;
                this.fetchData();
            } catch (err) {
                this.error = err.message || "Terjadi kesalahan.";
            } finally {
                this.isSubmitting = false;
            }
        },

        async handleDelete() {
            if (!this.sportToDelete) return;
            this.isSubmitting = true;
            this.error = null;
            try {
                await this.fetchApi(`/api/sports/${this.sportToDelete.id}`, {
                    method: 'DELETE',
                    throwError: true
                });
                this.showToast('Cabang Olahraga berhasil dihapus');
                this.sportToDelete = null;
                this.fetchData();
            } catch (err) {
                this.error = err.message || "Terjadi kesalahan saat menghapus.";
            } finally {
                this.isSubmitting = false;
            }
        }
    }));
});
