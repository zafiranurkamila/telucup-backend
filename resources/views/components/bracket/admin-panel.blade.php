@props(['sports' => [], 'role' => 'public'])

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
                        <option value="{{ $s->id }}">{{ $s->icon ?? '' }} {{ $s->name }}</option>
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
                    @change="onCategoryChange()"
                    :disabled="!hasCategories"
                    class="w-full appearance-none bg-white border border-gray-200 rounded-lg py-2.5 pl-4 pr-10 text-sm font-medium text-gray-700 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500 transition-colors disabled:bg-gray-50 disabled:text-gray-400 disabled:cursor-not-allowed"
                >
                    <option value="">Pilih sub-kategori...</option>
                    <template x-if="hasCategories">
                        <template x-for="cat in sports.find(s => String(s.id) === String(selectedSportId))?.categories || []" :key="cat.id">
                            <option :value="String(cat.id)" x-text="cat.name + ' (' + (cat.gender === 'male' ? 'Putra' : cat.gender === 'female' ? 'Putri' : 'Campuran') + ')'"></option>
                        </template>
                    </template>
                    <template x-if="!hasCategories">
                        <option value="" disabled>-- Tidak ada sub-kategori --</option>
                    </template>
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </div>
            </div>
        </div>

        {{-- Tim count --}}
        <div class="md:col-span-3 flex flex-col items-start md:items-center justify-end">
            <div class="flex items-center gap-2 bg-gray-50 border border-gray-100 rounded-lg px-4 py-2.5 w-full justify-center">
                <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                <div class="text-center">
                    <span class="text-lg font-extrabold text-gray-900" x-text="registrations.length"></span>
                    <span class="text-[10px] font-bold text-gray-400 block -mt-1">TIM TERVERIFIKASI</span>
                </div>
            </div>
        </div>
    </div>

    @if($role === 'panitia')
    <div class="border-t border-gray-100 my-4"></div>

    {{-- Action buttons --}}
    <div class="flex flex-wrap gap-3 items-center">
        {{-- Generate --}}
        <template x-if="!bracketData">
            <button
                @click="handleGenerate()"
                :disabled="!isReady || isGenerating"
                class="inline-flex items-center gap-2 bg-brand hover:bg-brand-hover disabled:bg-gray-300 disabled:cursor-not-allowed text-white text-sm font-bold py-2.5 px-5 rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-red-500/50 shadow-sm hover:shadow-md"
            >
                <svg :class="isGenerating ? 'animate-spin' : ''" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                </svg>
                <span x-text="isGenerating ? 'Generating...' : 'Generate Bagan'"></span>
            </button>
        </template>

        {{-- Randomize + Reset --}}
        <template x-if="bracketData">
            <div class="flex flex-wrap gap-3">
                <button
                    @click="handleRandomize()"
                    class="inline-flex items-center gap-2 bg-brand hover:bg-brand-hover text-white text-sm font-bold py-2.5 px-5 rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-red-500/50 shadow-sm hover:shadow-md"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    Randomize Posisi
                </button>
                <button
                    @click="handleReset()"
                    class="inline-flex items-center gap-2 bg-white hover:bg-red-50 border border-red-200 text-brand text-sm font-bold py-2.5 px-5 rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-red-200"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Reset Bagan
                </button>
            </div>
        </template>

        {{-- Hints --}}
        <template x-if="!selectedSportId">
            <span class="text-xs text-gray-400 font-medium italic ml-2">← Pilih cabang olahraga terlebih dahulu</span>
        </template>
        <template x-if="selectedSportId && hasCategories && !selectedCategoryId">
            <span class="text-xs text-amber-500 font-medium italic ml-2">⚠ Pilih sub-kategori untuk melanjutkan</span>
        </template>
    </div>
    @endif
</div>
