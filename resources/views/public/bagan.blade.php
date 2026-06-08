<x-layout-public>
    <x-slot:title>Kelola Bagan</x-slot:title>

    

    {{-- Alpine.js state container --}}
    <div
        x-data="publicBracketManager(@js($sports))"
        class="space-y-6 pb-10 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto"
    >
        {{-- Header --}}
        <div class="text-center mb-6 pt-4">
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white border border-gray-100 shadow-sm text-xs font-bold text-brand uppercase tracking-widest mb-4">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
                TURNAMEN TAHUNAN
            </div>
            <h1 class="text-3xl md:text-5xl font-black text-gray-900 tracking-tight mb-4">
                Bagan <span class="text-brand">Tel-U Cup</span>
            </h1>
            <p class="text-gray-500 max-w-2xl mx-auto text-sm md:text-base">
                Jelajahi peta persaingan olahraga terbesar di kampus. Pantau tim favorit Anda dari babak penyisihan hingga mencapai puncak kejayaan di Grand Final.
            </p>
        </div>

        {{-- ============================================================
             Filter Bar
             ============================================================ --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 md:p-6">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                {{-- Sport select --}}
                <div class="md:col-span-5">
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">Cabang Olahraga</label>
                    <div class="relative">
                        <select
                            x-model="selectedSportId"
                            @change="onSportChange()"
                            class="w-full appearance-none bg-white border border-gray-200 rounded-lg py-2.5 pl-4 pr-10 text-sm font-medium text-gray-700 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500 transition-colors"
                        >
                            <option value="" disabled>Pilih cabang olahraga...</option>
                            @foreach($sports as $s)
                                <option value="{{ $s->id }}">{{ $s->name }}</option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>
                </div>

                {{-- Category select --}}
                <div class="md:col-span-4">
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">Sub-Kategori</label>
                    <div class="relative">
                        <select
                            x-model="selectedCategoryId"
                            :disabled="!hasCategories"
                            x-html="categoryOptionsHtml"
                            class="w-full appearance-none bg-white border border-gray-200 rounded-lg py-2.5 pl-4 pr-10 text-sm font-medium text-gray-700 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500 transition-colors disabled:bg-gray-50 disabled:text-gray-400 disabled:cursor-not-allowed"
                        >
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>
                </div>

                {{-- Cari button --}}
                <div class="md:col-span-3 flex flex-col items-start md:items-center justify-end">
                    <button 
                        @click="handleSearch()"
                        :disabled="!isReady"
                        class="w-full bg-[#b6252a] text-white py-2.5 px-4 rounded-lg font-bold hover:bg-[#9a1e22] transition-colors disabled:bg-gray-300 disabled:text-gray-500 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        Cari
                    </button>
                </div>
            </div>

            
        </div>

        {{-- ============================================================
             Bracket Board
             ============================================================ --}}

        {{-- Empty state --}}
        <template x-if="!bracketData && !selectedSportId">
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-12 text-center">
                <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-5">
                    <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-700 mb-2">Pilih Cabang Olahraga</h3>
                <p class="text-sm text-gray-500 max-w-md mx-auto">Pilih cabang olahraga dari filter di atas untuk melihat atau membuat bagan pertandingan.</p>
            </div>
        </template>

        {{-- Loading --}}
        <template x-if="isLoading">
            <div class="flex items-center justify-center py-16">
                <div class="h-8 w-8 animate-spin rounded-full border-4 border-gray-200 border-t-brand"></div>
                <span class="ml-3 text-sm text-gray-500">Memuat bagan...</span>
            </div>
        </template>

        

        {{-- Bracket rounds & Results --}}
        <template x-if="bracketData && !isLoading">
            <x-bracket.board role="public" />
        </template>

        {{-- Match Detail Modal --}}
        <x-bracket.detail-modal />

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('publicBracketManager', (initialSports) => ({
        sports: initialSports,
        selectedSportId: sessionStorage.getItem('public_bagan_sport_id') || '',
        selectedCategoryId: sessionStorage.getItem('public_bagan_category_id') || '',
        registrations: [],
        bracketData: null,
        isLoading: false,
        detailMatch: null,

        openMatchDetail(match) {
            this.detailMatch = JSON.parse(JSON.stringify(match));
            document.body.style.overflow = 'hidden';
        },
        
        closeMatchDetail() {
            this.detailMatch = null;
            document.body.style.overflow = '';
        },

        init() {
            this.$watch('selectedSportId', val => {
                sessionStorage.setItem('public_bagan_sport_id', val);
            });
            this.$watch('selectedCategoryId', val => {
                sessionStorage.setItem('public_bagan_category_id', val || '');
            });
            
            if (this.selectedSportId) {
                this.loadBracket();
            }
        },

        get selectedSport() {
            return this.sports.find(s => s.id == this.selectedSportId) || null;
        },
        get selectedSportCategories() {
            return this.selectedSport?.categories || [];
        },
        get hasCategories() {
            return this.selectedSportCategories.length > 0;
        },
        get categoryOptionsHtml() {
            if (!this.hasCategories) {
                return '<option value="">Tidak ada sub-kategori</option>';
            }
            let html = '<option value="" disabled selected>Pilih sub-kategori...</option>';
            this.selectedSportCategories.forEach(c => {
                html += `<option value="${c.id}">${c.name}</option>`;
            });
            return html;
        },
        get isReady() {
            return this.selectedSportId && (!this.hasCategories || this.selectedCategoryId);
        },

        async api(method, url) {
            const opts = {
                method,
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
            };
            const res = await fetch('/api' + url, opts);
            const json = await res.json();
            if (!res.ok) throw new Error(json.message || 'Request failed');
            return json;
        },

        async loadBracket() {
            if (!this.selectedSportId) return;
            this.isLoading = true;
            try {
                let url = `/bracket?sport_id=${this.selectedSportId}`;
                if (this.selectedCategoryId) {
                    url += `&sport_category_id=${this.selectedCategoryId}`;
                }
                const res = await this.api('GET', url);
                this.bracketData = res.data;
            } catch (err) {
                console.error(err);
                this.bracketData = null;
            } finally {
                this.isLoading = false;
            }
        },

        onSportChange() {
            this.selectedCategoryId = '';
            this.bracketData = null;
        },

        onCategoryChange() {
            this.bracketData = null;
        },

        handleSearch() {
            if (this.isReady) {
                this.loadBracket();
            }
        }
    }));
    });
</script>
</x-layout-public>
    