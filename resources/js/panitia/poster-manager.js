document.addEventListener('alpine:init', () => {
    Alpine.data('posterManager', () => ({
        posters: [],
        loading: true,
        error: null,
        isSaving: false,
        
        searchQuery: '',
        statusFilter: 'all', // 'all', 'active', 'inactive'
        
        isFormOpen: false,
        isDeleteOpen: false,
        isPreviewOpen: false,
        isReorderMode: false,
        
        selectedPoster: null,
        
        formPayload: {
            title: '',
            description: '',
            is_active: true,
            imageFile: null,
            imagePreviewUrl: null
        },

        init() {
            this.fetchPosters();
        },
        
        get filteredPosters() {
            let result = this.posters;
            
            if (this.statusFilter === 'active') {
                result = result.filter(p => p.is_active);
            } else if (this.statusFilter === 'inactive') {
                result = result.filter(p => !p.is_active);
            }
            
            if (this.searchQuery) {
                const q = this.searchQuery.toLowerCase();
                result = result.filter(p => 
                    (p.title || '').toLowerCase().includes(q) || 
                    (p.description || '').toLowerCase().includes(q)
                );
            }
            
            return result;
        },

        async api(url, options = {}) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            const headers = {
                'Accept': 'application/json',
                ...(csrfToken && { 'X-CSRF-TOKEN': csrfToken }),
                ...(options.headers || {})
            };
            
            const res = await fetch(url, { ...options, headers });
            const json = await res.json();
            if (!res.ok) throw new Error(json.message || 'Request failed');
            return json;
        },

        async fetchPosters() {
            this.loading = true;
            this.error = null;
            try {
                const json = await this.api('/api/sportsmanship-posters');
                if (json.status === 'success') {
                    this.posters = json.data || [];
                }
            } catch (e) {
                this.error = e.message;
            } finally {
                this.loading = false;
            }
        },

        openForm(poster = null) {
            this.selectedPoster = poster;
            this.formPayload = {
                title: poster ? poster.title : '',
                description: poster ? (poster.description || '') : '',
                is_active: poster ? poster.is_active : true,
                imageFile: null,
                imagePreviewUrl: poster ? poster.image_url : null
            };
            this.isFormOpen = true;
        },

        handleImageUpload(event) {
            const file = event.target.files[0];
            if (!file) return;
            this.formPayload.imageFile = file;
            const reader = new FileReader();
            reader.onload = (e) => {
                this.formPayload.imagePreviewUrl = e.target.result;
            };
            reader.readAsDataURL(file);
        },

        async savePoster() {
            if (!this.formPayload.title || (!this.selectedPoster && !this.formPayload.imageFile)) return;
            
            this.isSaving = true;
            try {
                const formData = new FormData();
                formData.append('title', this.formPayload.title);
                if (this.formPayload.description) formData.append('description', this.formPayload.description);
                formData.append('is_active', this.formPayload.is_active ? '1' : '0');
                if (this.formPayload.imageFile) formData.append('image', this.formPayload.imageFile);
                
                let url = '/api/sportsmanship-posters';
                if (this.selectedPoster) {
                    url += `/${this.selectedPoster.id}`;
                    formData.append('_method', 'PATCH');
                }
                
                const json = await this.api(url, {
                    method: 'POST',
                    body: formData
                });
                
                if (json.status === 'success') {
                    this.isFormOpen = false;
                    this.fetchPosters();
                }
            } catch (e) {
                alert(e.message);
            } finally {
                this.isSaving = false;
            }
        },

        openDelete(poster) {
            this.selectedPoster = poster;
            this.isDeleteOpen = true;
        },

        async deletePoster() {
            if (!this.selectedPoster) return;
            this.isSaving = true;
            try {
                await this.api(`/api/sportsmanship-posters/${this.selectedPoster.id}`, { method: 'DELETE' });
                this.isDeleteOpen = false;
                this.posters = this.posters.filter(p => p.id !== this.selectedPoster.id);
            } catch (e) {
                alert(e.message);
            } finally {
                this.isSaving = false;
            }
        },

        async toggleStatus(id, currentStatus) {
            try {
                const json = await this.api(`/api/sportsmanship-posters/${id}/toggle`, {
                    method: 'PATCH',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ is_active: !currentStatus })
                });
                if (json.status === 'success') {
                    const idx = this.posters.findIndex(p => p.id === id);
                    if (idx !== -1) this.posters[idx] = json.data;
                }
            } catch (e) {
                alert(e.message);
            }
        },

        openPreview(poster) {
            this.selectedPoster = poster;
            this.isPreviewOpen = true;
        },

        toggleReorderMode() {
            this.isReorderMode = !this.isReorderMode;
            if (this.isReorderMode) {
                this.searchQuery = '';
                this.statusFilter = 'all';
                // Sort array to ensure it's visually ordered by sort_order
                this.posters.sort((a, b) => a.sort_order - b.sort_order);
            }
        },

        moveUp(index) {
            if (index > 0) {
                const temp = this.posters[index];
                this.posters[index] = this.posters[index - 1];
                this.posters[index - 1] = temp;
            }
        },

        moveDown(index) {
            if (index < this.posters.length - 1) {
                const temp = this.posters[index];
                this.posters[index] = this.posters[index + 1];
                this.posters[index + 1] = temp;
            }
        },

        async saveReorder() {
            this.isSaving = true;
            try {
                const items = this.posters.map((p, index) => ({ id: p.id, sort_order: index }));
                const json = await this.api('/api/sportsmanship-posters/reorder', {
                    method: 'PATCH',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ items })
                });
                
                if (json.status === 'success') {
                    this.posters = json.data;
                    this.isReorderMode = false;
                }
            } catch (e) {
                alert(e.message);
            } finally {
                this.isSaving = false;
            }
        }
    }));
});
