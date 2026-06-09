document.addEventListener('alpine:init', () => {
    Alpine.data('fotoSayaGallery', () => ({
        photos: [],
        loading: false,
        loaded: false,
        error: null,
        statusFilter: 'all',
        selectedPhoto: null,
        validating: null,

        filterCfg: {
            all: { label: 'Semua', active: 'bg-gray-900 text-white' },
            pending: { label: 'Pending', active: 'bg-amber-500 text-white' },
            accepted: { label: 'Diterima', active: 'bg-green-600 text-white' },
            rejected: { label: 'Ditolak', active: 'bg-red-600 text-white' },
        },

        get filteredPhotos() {
            if (this.statusFilter === 'all') return this.photos;
            return this.photos.filter((photo) => photo.validation_status === this.statusFilter);
        },

        get counts() {
            return {
                all: this.photos.length,
                pending: this.photos.filter((photo) => photo.validation_status === 'pending').length,
                accepted: this.photos.filter((photo) => photo.validation_status === 'accepted').length,
                rejected: this.photos.filter((photo) => photo.validation_status === 'rejected').length,
            };
        },

        async fetchPhotos() {
            this.loading = true;
            this.error = null;

            try {
                const res = await fetch('/api/my-gallery', {
                    headers: {
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                });
                const body = await res.json();

                if (!res.ok) {
                    this.error = body.message ?? 'Gagal memuat galeri.';
                    return;
                }

                this.photos = body.data ?? [];
                this.loaded = true;
            } catch {
                this.error = 'Koneksi gagal. Periksa jaringan dan coba lagi.';
            } finally {
                this.loading = false;
            }
        },

        async validate(photoFaceId, status) {
            if (this.validating) return;
            this.validating = photoFaceId;

            try {
                const res = await fetch(`/api/my-gallery/${photoFaceId}/validate`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ status }),
                });

                if (res.ok) {
                    const index = this.photos.findIndex((photo) => photo.id === photoFaceId);
                    if (index !== -1) this.photos[index].validation_status = status;
                    if (this.selectedPhoto?.id === photoFaceId) {
                        this.selectedPhoto = { ...this.selectedPhoto, validation_status: status };
                    }
                }
            } finally {
                this.validating = null;
            }
        },

        async downloadPhoto(photo) {
            const url = photo?.event_photo?.image_url;
            if (!url) return;

            try {
                const response = await fetch(url);
                const blob = await response.blob();
                const ext = blob.type.split('/')[1] || 'jpg';
                const name = this.downloadFileName(photo, ext);
                const objectUrl = URL.createObjectURL(blob);
                const anchor = document.createElement('a');

                anchor.href = objectUrl;
                anchor.download = name;
                document.body.appendChild(anchor);
                anchor.click();
                document.body.removeChild(anchor);
                URL.revokeObjectURL(objectUrl);
            } catch {
                window.open(url, '_blank');
            }
        },

        downloadFileName(photo, ext) {
            const fallback = `foto-telucup-${photo.id}.${ext}`;
            const rawName = photo?.event_photo?.image_url?.split('/').pop()?.split('?')[0] || fallback;

            return rawName.includes('.') ? rawName : `${rawName}.${ext}`;
        },

        openModal(photo) {
            this.selectedPhoto = photo;
            document.body.style.overflow = 'hidden';
        },

        closeModal() {
            this.selectedPhoto = null;
            document.body.style.overflow = '';
        },

        statusClass(status) {
            const classes = {
                pending: 'bg-amber-100 text-amber-700 border-amber-200',
                accepted: 'bg-green-100 text-green-700 border-green-200',
                rejected: 'bg-gray-100 text-gray-500 border-gray-200',
            };

            return classes[status] ?? classes.pending;
        },

        statusLabel(status) {
            const labels = { pending: 'Pending', accepted: 'Diterima', rejected: 'Ditolak' };
            return labels[status] ?? status;
        },

        formatDate(value) {
            if (!value) return '-';
            return new Date(value).toLocaleDateString('id-ID', {
                day: 'numeric',
                month: 'short',
                year: 'numeric',
            });
        },
    }));
});
