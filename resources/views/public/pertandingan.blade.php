<x-layout-public>
    <x-slot:title>Pertandingan</x-slot:title>

    <section
        x-data="publicMatchesManager()"
        x-init="init()"
        class="min-h-screen bg-[#f4f7fb] py-8"
    >
        <div class="max-w-6xl mx-auto px-4">
            <h1 class="text-center text-2xl md:text-3xl font-semibold text-gray-500 mb-6">
                Jadwal & Hasil Pertandingan
            </h1>

            {{-- Date Filter --}}
            <div class="flex items-center justify-center gap-3 mb-10 overflow-x-auto pb-3">
                <button
                    type="button"
                    @click="moveDate(-1)"
                    class="w-8 h-8 rounded-full bg-[#c8212d] text-white flex items-center justify-center shrink-0"
                >
                    ‹
                </button>

                <template x-for="date in visibleDates" :key="date.value">
                    <button
                        type="button"
                        @click="selectDate(date.value)"
                        class="shrink-0 px-5 py-3 rounded-full text-xs md:text-sm font-semibold shadow-sm transition"
                        :class="selectedDate === date.value ? 'bg-red-500 text-white' : 'bg-[#b91f2a] text-white hover:bg-red-700'"
                        x-text="date.label"
                    ></button>
                </template>

                <button
                    type="button"
                    @click="moveDate(1)"
                    class="w-8 h-8 rounded-full bg-[#c8212d] text-white flex items-center justify-center shrink-0"
                >
                    ›
                </button>
            </div>

            {{-- Loading --}}
            <div x-show="isLoading" class="text-center py-16">
                <div class="inline-flex items-center gap-3 text-gray-500 font-medium">
                    <div class="w-5 h-5 border-2 border-red-500 border-t-transparent rounded-full animate-spin"></div>
                    Memuat pertandingan...
                </div>
            </div>

            {{-- Empty --}}
            <div x-show="!isLoading && matches.length === 0" class="max-w-3xl mx-auto bg-white rounded-xl border border-gray-100 p-10 text-center text-gray-500">
                Belum ada pertandingan pada tanggal ini.
            </div>

            {{-- Match List --}}
            <div x-show="!isLoading && matches.length > 0" class="bg-white/80 max-w-5xl mx-auto rounded-sm py-8 px-4 md:px-6 space-y-7">
                <template x-for="match in matches" :key="match.id">
                    <div class="rounded-xl overflow-hidden shadow-md bg-[#eef3fb] border border-gray-100">
                        {{-- Red Header --}}
                        <div class="bg-[#bd1f2a] text-white text-center py-4 px-4">
                            <h2 class="font-semibold text-base md:text-lg" x-text="matchTitle(match)"></h2>

                            <p class="text-xs md:text-sm mt-1 font-semibold">
                                🕘 DATE/TIME :
                                <span x-text="formatShortDate(match.match_date)"></span>
                                |
                                <span x-text="match.match_time || 'TBD'"></span>
                            </p>

                            <p class="text-xs md:text-sm mt-1 font-semibold uppercase">
                                📍 :
                                <span x-text="match.location || match.venue || 'TBD'"></span>
                            </p>
                        </div>

                        {{-- Body --}}
                        <div class="relative grid grid-cols-3 items-center px-5 md:px-8 py-6 min-h-[95px]">
                            {{-- Team A --}}
                            <div class="flex items-center gap-3">
                                <div class="w-14 h-14 md:w-16 md:h-16 rounded-full border-2 border-gray-300 bg-white flex items-center justify-center text-[11px] font-black text-[#bd1f2a] shadow-sm overflow-hidden">
                                    <template x-if="teamLogo(match, 'a')">
                                        <img :src="teamLogo(match, 'a')" class="w-full h-full object-cover" alt="">
                                    </template>
                                    <template x-if="!teamLogo(match, 'a')">
                                        <span x-text="teamInitial(teamName(match, 'a'))"></span>
                                    </template>
                                </div>

                                <span class="text-sm md:text-base text-gray-500" x-text="teamName(match, 'a')"></span>
                            </div>

                            {{-- Score --}}
                            <div class="flex items-center justify-center gap-3">
                                <template x-if="isWinner(match, 'a')">
                                    <div class="absolute left-[28%] md:left-[31%] -rotate-12 border-2 border-[#bd1f2a] text-[#bd1f2a] text-[10px] md:text-xs font-black px-2 py-1 uppercase">
                                        Winner
                                    </div>
                                </template>

                                <span class="bg-white rounded px-3 py-2 text-[#bd1f2a] font-bold shadow-sm" x-text="score(match, 'a')"></span>
                                <span class="text-gray-400 text-sm font-semibold">VS</span>
                                <span class="bg-white rounded px-3 py-2 text-[#bd1f2a] font-bold shadow-sm" x-text="score(match, 'b')"></span>

                                <template x-if="isWinner(match, 'b')">
                                    <div class="absolute right-[28%] md:right-[31%] -rotate-12 border-2 border-[#bd1f2a] text-[#bd1f2a] text-[10px] md:text-xs font-black px-2 py-1 uppercase">
                                        Winner
                                    </div>
                                </template>
                            </div>

                            {{-- Team B --}}
                            <div class="flex items-center justify-end gap-3">
                                <span class="text-sm md:text-base text-gray-500 text-right" x-text="teamName(match, 'b')"></span>

                                <div class="w-14 h-14 md:w-16 md:h-16 rounded-full border-2 border-gray-300 bg-white flex items-center justify-center text-[11px] font-black text-[#bd1f2a] shadow-sm overflow-hidden">
                                    <template x-if="teamLogo(match, 'b')">
                                        <img :src="teamLogo(match, 'b')" class="w-full h-full object-cover" alt="">
                                    </template>
                                    <template x-if="!teamLogo(match, 'b')">
                                        <span x-text="teamInitial(teamName(match, 'b'))"></span>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </section>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('publicMatchesManager', () => ({
                matches: [],
                allDates: [],
                selectedDate: '',
                dateWindowStart: 0,
                isLoading: false,

                async init() {
                    await this.loadMatches();
                },

                get visibleDates() {
                    return this.allDates.slice(this.dateWindowStart, this.dateWindowStart + 7);
                },

                async api(url) {
                    const res = await fetch(url, {
                        headers: {
                            'Accept': 'application/json',
                        },
                    });

                    const json = await res.json();

                    if (!res.ok) {
                        throw new Error(json.message || 'Gagal mengambil data pertandingan');
                    }

                    return json;
                },

                async loadMatches(date = '') {
                    this.isLoading = true;

                    try {
                        let url = '/api/matches?per_page=100';

                        if (date) {
                            url += `&date=${date}`;
                        }

                        const res = await this.api(url);
                        this.matches = res.data || [];

                        if (!date) {
                            this.buildDates(this.matches);
                        }
                    } catch (err) {
                        console.error(err);
                        this.matches = [];
                    } finally {
                        this.isLoading = false;
                    }
                },

                buildDates(matches) {
                    const uniqueDates = [...new Set(
                        matches
                            .map(match => match.match_date)
                            .filter(Boolean)
                    )];

                    this.allDates = uniqueDates.map(date => ({
                        value: date,
                        label: this.formatLongDate(date),
                    }));

                    if (this.allDates.length > 0) {
                        this.selectedDate = this.allDates[0].value;
                        this.loadMatches(this.selectedDate);
                    }
                },

                selectDate(date) {
                    this.selectedDate = date;
                    this.loadMatches(date);
                },

                moveDate(direction) {
                    const next = this.dateWindowStart + direction;

                    if (next >= 0 && next <= Math.max(this.allDates.length - 7, 0)) {
                        this.dateWindowStart = next;
                    }
                },

                matchTitle(match) {
                    const sportName = match.sport?.name || match.sport_name || 'Pertandingan';
                    const categoryName = match.sport_category?.name || match.sportCategory?.name || match.category_name || '';

                    return categoryName ? `${sportName} ${categoryName}` : sportName;
                },

                teamName(match, side) {
                    const registration = side === 'a'
                        ? (match.registration_a || match.registrationA || match.team_a)
                        : (match.registration_b || match.registrationB || match.team_b);

                    return registration?.contingent?.name
                        || registration?.contingent_name
                        || registration?.name
                        || 'TBD';
                },

                teamLogo(match, side) {
                    const registration = side === 'a'
                        ? (match.registration_a || match.registrationA || match.team_a)
                        : (match.registration_b || match.registrationB || match.team_b);

                    return registration?.contingent?.image_url
                        || registration?.contingent?.logo
                        || registration?.logo
                        || null;
                },

                teamInitial(name) {
                    if (!name || name === 'TBD') return 'TBD';
                    return name.substring(0, 2).toUpperCase();
                },

                score(match, side) {
                    if (match.status === 'scheduled') return '-';

                    const value = side === 'a'
                        ? (match.score_a ?? match.scoreA)
                        : (match.score_b ?? match.scoreB);

                    return value ?? 0;
                },

                isWinner(match, side) {
                    if (match.status !== 'finished') return false;

                    const winner = match.winner;
                    const registration = side === 'a'
                        ? (match.registration_a || match.registrationA || match.team_a)
                        : (match.registration_b || match.registrationB || match.team_b);

                    return winner?.id && registration?.id && winner.id === registration.id;
                },

                formatShortDate(date) {
                    if (!date) return 'TBD';

                    return new Date(date).toLocaleDateString('en-US', {
                        month: 'short',
                        day: 'numeric',
                        year: 'numeric',
                    });
                },

                formatLongDate(date) {
                    return new Date(date).toLocaleDateString('id-ID', {
                        weekday: 'long',
                        day: 'numeric',
                        month: 'long',
                        year: 'numeric',
                    });
                },
            }));
        });
    </script>
</x-layout-public>