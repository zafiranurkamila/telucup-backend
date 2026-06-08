document.addEventListener('alpine:init', () => {
    Alpine.data('galeriManager', (isViewer = false) => ({
        isViewer,
        currentFolderId: null,
        folders: [],
        photos: [],
        breadcrumbs: [],
        
        loading: true,
        uploading: false,
        error: null,
        toast: null,
        
        searchQuery: '',
        sortOrder: 'Terbaru', // 'Terbaru', 'Terlama', 'Nama'
        
        // Modals
        isFolderModalOpen: false,
        folderToEdit: null,
        isUploadModalOpen: false,
        folderToDelete: null,
        photoToDelete: null,
        photoToPreview: null,
        photoToMove: null,

        // For move modal
        allFolders: [], // list of all folders to move to

        init() {
            this.fetchData();
        },

        showToast(type, message) {
            this.toast = { type, message };
            setTimeout(() => { this.toast = null; }, 3000);
        },

        get filteredFolders() {
            let result = this.folders.filter(f => f.name.toLowerCase().includes(this.searchQuery.toLowerCase()));
            result.sort((a, b) => {
                if (this.sortOrder === 'Nama') return a.name.localeCompare(b.name);
                return this.sortOrder === 'Terbaru' 
                    ? new Date(b.created_at) - new Date(a.created_at)
                    : new Date(a.created_at) - new Date(b.created_at);
            });
            return result;
        },

        get filteredPhotos() {
            let result = this.photos.filter(p =>
                !this.searchQuery || p.id.toString().includes(this.searchQuery)
            );
            result.sort((a, b) =>
                (this.sortOrder === 'Terbaru' || this.sortOrder === 'Nama')
                    ? new Date(b.created_at) - new Date(a.created_at)
                    : new Date(a.created_at) - new Date(b.created_at)
            );
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

        async fetchData() {
            this.loading = true;
            this.error = null;
            try {
                if (this.currentFolderId === null) {
                    const [resFolders, resPhotos] = await Promise.all([
                        this.api('/api/gallery-folders').catch(() => ({ data: [] })),
                        this.api('/api/event-photos').catch(() => ({ data: [] }))
                    ]);
                    this.folders = resFolders.data || [];
                    this.photos = (resPhotos.data || []).filter(p => p.gallery_folder_id === null);
                    this.breadcrumbs = [];
                } else {
                    const [resFolder, resPhotos] = await Promise.all([
                        this.api(`/api/gallery-folders/${this.currentFolderId}`),
                        this.api(`/api/gallery-folders/${this.currentFolderId}/photos`)
                    ]);
                    
                    const currentFolder = resFolder.data;
                    
                    if (currentFolder.children) {
                        this.folders = currentFolder.children;
                    } else {
                        const sub = await this.api(`/api/gallery-folders?parent_id=${this.currentFolderId}`).catch(() => ({ data: [] }));
                        this.folders = sub.data || [];
                    }
                    
                    this.photos = resPhotos.data || [];
                    this.breadcrumbs = currentFolder.breadcrumb || [currentFolder];
                }
            } catch (e) {
                this.error = e.message;
            } finally {
                this.loading = false;
            }
        },

        openFolder(id) {
            this.currentFolderId = id;
            this.searchQuery = '';
            this.fetchData();
        },

        goToRoot() {
            this.currentFolderId = null;
            this.searchQuery = '';
            this.fetchData();
        },

        goToFolder(id) {
            this.currentFolderId = id;
            this.searchQuery = '';
            this.fetchData();
        },

        openFolderModal(folder = null) {
            if (this.isViewer) return;
            this.folderToEdit = folder;
            this.isFolderModalOpen = true;
            // The modal will bind to an inner state using x-data, we don't manage its inner state here to keep it clean
        },

        async saveFolder(name) {
            try {
                if (this.folderToEdit) {
                    await this.api(`/api/gallery-folders/${this.folderToEdit.id}`, {
                        method: 'PATCH',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ name })
                    });
                    this.showToast('success', 'Folder berhasil diubah.');
                } else {
                    await this.api('/api/gallery-folders', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ name, parent_id: this.currentFolderId })
                    });
                    this.showToast('success', 'Folder berhasil dibuat.');
                }
                this.isFolderModalOpen = false;
                this.fetchData();
            } catch (e) {
                this.showToast('error', e.message);
            }
        },

        confirmDeleteFolder(folder) {
            if (this.isViewer) return;
            this.folderToDelete = folder;
        },

        async deleteFolder() {
            try {
                await this.api(`/api/gallery-folders/${this.folderToDelete.id}`, { method: 'DELETE' });
                this.showToast('success', 'Folder berhasil dihapus.');
                this.folderToDelete = null;
                this.fetchData();
            } catch (e) {
                this.showToast('error', e.message);
            }
        },

        confirmDeletePhoto(photo) {
            if (this.isViewer) return;
            this.photoToDelete = photo;
        },

        async deletePhoto() {
            try {
                await this.api(`/api/event-photos/${this.photoToDelete.id}`, { method: 'DELETE' });
                this.showToast('success', 'Foto berhasil dihapus.');
                this.photoToDelete = null;
                this.fetchData();
            } catch (e) {
                this.showToast('error', e.message);
            }
        },

        async uploadPhoto(file) {
            if (this.isViewer) return;
            this.uploading = true;
            try {
                const formData = new FormData();
                formData.append('image', file);
                if (this.currentFolderId) {
                    formData.append('gallery_folder_id', this.currentFolderId);
                }
                await this.api('/api/event-photos', {
                    method: 'POST',
                    body: formData
                });
                this.showToast('success', 'Foto berhasil diunggah.');
                this.isUploadModalOpen = false;
                this.fetchData();
            } catch (e) {
                this.showToast('error', e.message);
            } finally {
                this.uploading = false;
            }
        },

        async openMoveModal(photo) {
            if (this.isViewer) return;
            this.photoToMove = photo;
            try {
                // Fetch all folders for moving
                const res = await this.api('/api/gallery-folders');
                this.allFolders = res.data || [];
            } catch (e) {
                this.allFolders = [];
            }
        },

        async downloadPhoto(url) {
            try {
                const resp = await fetch(url);
                const blob = await resp.blob();
                const ext  = blob.type.split('/')[1] || 'jpg';
                const name = url.split('/').pop().split('?')[0] || `foto-telucup.${ext}`;
                const a    = document.createElement('a');
                a.href     = URL.createObjectURL(blob);
                a.download = name.includes('.') ? name : `${name}.${ext}`;
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                URL.revokeObjectURL(a.href);
            } catch (e) {
                window.open(url, '_blank');
            }
        },

        async movePhoto(targetFolderId) {
            try {
                await this.api(`/api/event-photos/${this.photoToMove.id}/move`, {
                    method: 'PATCH',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ gallery_folder_id: targetFolderId })
                });
                this.showToast('success', 'Foto berhasil dipindahkan.');
                this.photoToMove = null;
                this.fetchData();
            } catch (e) {
                this.showToast('error', e.message);
            }
        }
    }));
});
