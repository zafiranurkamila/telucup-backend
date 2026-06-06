<x-layouts.dashboard :roleLabel="'Panitia'">
    <x-slot:title>Kelola Kontingen</x-slot:title>

    <x-slot:sidebar>
        @include('partials.sidebar-panitia')
    </x-slot:sidebar>

    <div x-data="kontingenManager()" class="space-y-6 pb-10">
        
        {{-- Toast Notification --}}
        <div x-show="toast.show"
            x-transition.opacity.duration.300ms
            class="fixed bottom-4 right-4 flex items-center gap-2 px-4 py-3 rounded-lg shadow-lg z-50 text-white"
            :class="toast.type === 'success' ? 'bg-emerald-600' : 'bg-red-600'"
            style="display: none;">
            <svg x-show="toast.type === 'success'" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <svg x-show="toast.type === 'error'" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span class="text-sm font-medium" x-text="toast.message"></span>
            <button @click="toast.show = false" class="ml-2 hover:opacity-75">&times;</button>
        </div>

        @include('dashboard.panitia.kontingen.partials.header')
        @include('dashboard.panitia.kontingen.partials.tabs')

        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden flex flex-col">
            {{-- Toolbar di dalam Tabs --}}
            <div class="p-4 border-b border-gray-100 flex flex-col sm:flex-row gap-4 justify-between bg-gray-50/50">
                <div class="relative w-full sm:w-72">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input type="text" x-model="search" :placeholder="'Cari ' + activeTab + '...'" class="w-full pl-9 pr-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-brand focus:ring-1 focus:ring-red-100 transition-colors">
                </div>
                
                {{-- Filter untuk Tab Kontingen --}}
                <div x-show="activeTab === 'kontingen'" class="flex items-center gap-3">
                    <div class="relative">
                        <select x-model="filterPic" class="appearance-none bg-white border border-gray-200 text-gray-700 py-2 pl-3 pr-8 rounded-lg text-sm focus:outline-none focus:border-brand cursor-pointer">
                            <option value="all">Semua Status</option>
                            <option value="has_pic">Sudah ada PIC</option>
                            <option value="no_pic">Belum ada PIC</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-500">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>
                </div>

                {{-- Filter untuk Tab Player --}}
                <div x-show="activeTab === 'player'" style="display: none;" class="flex items-center gap-3">
                    <div class="relative">
                        <select x-model="filterPlayer" class="appearance-none bg-white border border-gray-200 text-gray-700 py-2 pl-3 pr-8 rounded-lg text-sm focus:outline-none focus:border-brand cursor-pointer">
                            <option value="all">Semua Player</option>
                            <option value="has_contingent">Sudah ada Kontingen</option>
                            <option value="no_contingent">Belum ada Kontingen</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-500">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tables --}}
            <div class="overflow-x-auto">
                <div x-show="activeTab === 'kontingen'">
                    @include('dashboard.panitia.kontingen.partials.table-kontingen')
                </div>
                <div x-show="activeTab === 'pic'" style="display: none;">
                    @include('dashboard.panitia.kontingen.partials.table-pic')
                </div>
                <div x-show="activeTab === 'player'" style="display: none;">
                    @include('dashboard.panitia.kontingen.partials.table-player')
                </div>
            </div>
        </div>

        @include('dashboard.panitia.kontingen.partials.modals')

    </div>
</x-layouts.dashboard>
