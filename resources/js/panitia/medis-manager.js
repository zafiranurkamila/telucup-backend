document.addEventListener('alpine:init', () => {
    Alpine.data('medisManager', () => ({
        // State
        assessments: [],
        loading: true,
        page: 1,
        totalPages: 1,
        totalItems: 0,
        
        // Filters
        riskFilter: '',
        clearanceFilter: false,
        searchQuery: '',
        
        // Tabs
        activeTab: 'list', // 'list', 'contingent', 'sport'
        
        // Summaries
        contingentSummary: [],
        sportSummary: [],
        loadingSummary: false,
        
        // Modal State
        selectedAssessment: null,
        reviewAllowed: null,
        reviewNotes: '',
        reviewPicConfirmed: false,
        isSubmittingReview: false,
        reviewFeedback: null,
        
        // Init
        init() {
            this.fetchAssessments();
            this.fetchSummaries();
            
            this.$watch('page', () => this.fetchAssessments());
            this.$watch('riskFilter', () => { this.page = 1; this.fetchAssessments(); });
            this.$watch('clearanceFilter', () => { this.page = 1; this.fetchAssessments(); });
        },
        
        get filteredData() {
            const query = this.searchQuery.toLowerCase();
            if (!query) return this.assessments;
            return this.assessments.filter(a => {
                return (a.player_name || '').toLowerCase().includes(query) ||
                       (a.contingent || '').toLowerCase().includes(query) ||
                       (a.sport_branch || '').toLowerCase().includes(query);
            });
        },
        
        async api(url, options = {}) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            const headers = {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                ...(csrfToken && { 'X-CSRF-TOKEN': csrfToken }),
                ...(options.headers || {})
            };
            
            const res = await fetch(url, { ...options, headers });
            const json = await res.json();
            if (!res.ok) throw new Error(json.message || 'Request failed');
            return json;
        },
        
        async fetchAssessments() {
            this.loading = true;
            try {
                const params = new URLSearchParams({ page: this.page, per_page: 20 });
                if (this.riskFilter) params.set('risk_label', this.riskFilter);
                if (this.clearanceFilter) params.set('requires_clearance', 'true');
                
                const json = await this.api(`/api/self-assessment?${params}`);
                if (json.status === 'success') {
                    this.assessments = json.data.data || [];
                    this.totalPages = json.data.last_page || 1;
                    this.totalItems = json.data.total || 0;
                }
            } catch (e) {
                console.error("Failed to fetch assessments", e);
            } finally {
                this.loading = false;
            }
        },
        
        async fetchSummaries() {
            this.loadingSummary = true;
            try {
                const [contingentJson, sportJson] = await Promise.all([
                    this.api('/api/self-assessment/summary/contingent'),
                    this.api('/api/self-assessment/summary/sport')
                ]);
                
                if (contingentJson.status === 'success') this.contingentSummary = contingentJson.data || [];
                if (sportJson.status === 'success') this.sportSummary = sportJson.data || [];
            } catch (e) {
                console.error("Failed to fetch summaries", e);
            } finally {
                this.loadingSummary = false;
            }
        },
        
        openDetail(assessment) {
            this.selectedAssessment = assessment;
            this.reviewAllowed = assessment.medical_review?.is_allowed_to_play ?? null;
            this.reviewNotes = assessment.medical_review?.medical_notes || '';
            this.reviewPicConfirmed = assessment.medical_review?.pic_confirmed || false;
            this.reviewFeedback = null;
        },
        
        closeDetail() {
            this.selectedAssessment = null;
            this.reviewFeedback = null;
        },
        
        async handleSubmitReview() {
            if (!this.selectedAssessment || this.reviewAllowed === null) return;
            
            this.isSubmittingReview = true;
            this.reviewFeedback = null;
            
            try {
                const res = await this.api(`/api/self-assessment/review/${this.selectedAssessment.id}`, {
                    method: 'POST',
                    body: JSON.stringify({
                        is_allowed_to_play: this.reviewAllowed,
                        medical_notes: this.reviewNotes,
                        pic_confirmed: this.reviewPicConfirmed
                    })
                });
                
                if (res.status === 'success') {
                    this.reviewFeedback = { type: 'success', msg: 'Review medis berhasil disimpan.' };
                    // Update in list
                    const idx = this.assessments.findIndex(a => a.id === this.selectedAssessment.id);
                    if (idx !== -1) {
                        this.assessments[idx] = res.data;
                    }
                    this.selectedAssessment = res.data; // update modal data
                }
            } catch (e) {
                this.reviewFeedback = { type: 'error', msg: e.message || 'Gagal menyimpan review' };
            } finally {
                this.isSubmittingReview = false;
            }
        },
        
        // Utils
        formatDate(dateString) {
            if (!dateString) return '—';
            return new Date(dateString).toLocaleDateString('id-ID', {
                day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit'
            });
        },
        
        formatDateShort(dateString) {
            if (!dateString) return '—';
            return new Date(dateString).toLocaleDateString('id-ID', {
                day: 'numeric', month: 'short', year: 'numeric'
            });
        },
        
        getDomainBarColor(score) {
            if (score >= 51) return "bg-[#B41F2A]";
            if (score >= 26) return "bg-amber-500";
            return "bg-green-600";
        },
        
        getRiskLabel(risk) {
            switch(risk) {
                case 'high': return 'High Risk';
                case 'medium': return 'Medium';
                case 'low': return 'Low Risk';
                default: return risk;
            }
        },

        getRiskBadgeClass(risk) {
            switch(risk) {
                case 'high': return 'bg-red-100 text-red-700 border-red-200';
                case 'medium': return 'bg-amber-100 text-amber-700 border-amber-200';
                case 'low': return 'bg-green-100 text-green-700 border-green-200';
                default: return 'bg-gray-100 text-gray-700 border-gray-200';
            }
        }
    }));
});
