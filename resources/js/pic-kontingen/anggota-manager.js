document.addEventListener('alpine:init', () => {
    Alpine.data('anggotaManager', () => ({
    members: [],
    isLoading: true,
    searchTerm: '',
    statusFilter: 'Semua',
    
    isAddModalOpen: false,
    isSubmitting: false,
    newMember: {
        name: '',
        email: '',
        password: ''
    },

    init() {
        this.fetchMembers();
    },

    async fetchMembers() {
        this.isLoading = true;
        try {
            const response = await fetch('/api/contingents/my/players', {
                headers: {
                    'Accept': 'application/json'
                }
            });
            const data = await response.json();
            this.members = data.data || [];
        } catch (error) {
            console.error("Failed to load contingent members", error);
        } finally {
            this.isLoading = false;
        }
    },

    get filteredMembers() {
        return this.members.filter(member => {
            const searchString = `${member.name} ${member.nim_nip || ''}`.toLowerCase();
            const matchesSearch = searchString.includes(this.searchTerm.toLowerCase());
            
            // Default ke draft jika tidak ada
            const currentStatus = member.verification_status || "draft";
            const matchesFilter = this.statusFilter === "Semua" || 
                (this.statusFilter === "Terverifikasi" && currentStatus === "verified") ||
                (this.statusFilter === "Pending" && currentStatus === "pending") ||
                (this.statusFilter === "Draft" && currentStatus === "draft") ||
                (this.statusFilter === "Ditolak" && currentStatus === "rejected");
                
            return matchesSearch && matchesFilter;
        });
    },

    async handleAddMember() {
        this.isSubmitting = true;
        try {
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const response = await fetch('/api/contingents/my/players/register', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify({
                    name: this.newMember.name,
                    email: this.newMember.email,
                    password: this.newMember.password || "1301234567"
                })
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || "Gagal menambahkan anggota");
            }

            this.isAddModalOpen = false;
            this.newMember = { name: '', email: '', password: '' };
            await this.fetchMembers();

            // Pilihan opsional: toast notification
        } catch (error) {
            alert(error.message);
        } finally {
            this.isSubmitting = false;
        }
    },

    async handleRemoveMember(id) {
        if(confirm("Apakah Anda yakin ingin menghapus anggota ini dari kontingen?")) {
            try {
                const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const response = await fetch(`/api/contingents/my/players/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token
                    }
                });

                if (!response.ok) {
                    const data = await response.json();
                    throw new Error(data.message || "Gagal menghapus anggota");
                }

                await this.fetchMembers();
            } catch (error) {
                alert(error.message);
            }
        }
    }
    }));
});
