<div class="overflow-x-auto">
    <table class="w-full text-left text-sm text-gray-600">
        <thead class="bg-gray-50 text-gray-500 text-xs uppercase font-semibold border-b border-gray-200">
            <tr>
                <th class="px-6 py-4 w-16 text-center">Icon</th>
                <th class="px-6 py-4">Nama Cabang Olahraga</th>
                <th class="px-6 py-4 w-[50%]">Kategori & Kuota Pemain</th>
                <th class="px-6 py-4 text-right">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            <template x-if="isLoading">
                <template x-for="i in 3">
                    <tr class="animate-pulse">
                        <td colspan="4" class="px-6 py-4"><div class="h-4 bg-gray-200 rounded w-full"></div></td>
                    </tr>
                </template>
            </template>
            <template x-if="!isLoading && filteredSports.length === 0">
                <tr><td colspan="4" class="px-6 py-12 text-center text-gray-500">Data tidak ditemukan</td></tr>
            </template>
            <template x-if="!isLoading && filteredSports.length > 0">
                <template x-for="item in filteredSports" :key="item.id">
                    <tr class="hover:bg-gray-50 transition-colors group">
                        <td class="px-6 py-4 text-center align-top">
                            <div class="w-10 h-10 rounded-lg bg-gray-100 border border-gray-200 flex items-center justify-center overflow-hidden mx-auto">
                                <template x-if="item.icon_path">
                                    <img :src="item.icon_path" :alt="item.name" class="w-full h-full object-cover" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                </template>
                                <svg x-show="!item.icon_path" class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <svg style="display: none;" class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                        </td>
                        <td class="px-6 py-4 align-top">
                            <div class="font-bold text-gray-800 text-base" x-text="item.name"></div>
                        </td>
                        <td class="px-6 py-4 align-top">
                            <div class="flex flex-wrap gap-2.5">
                                <template x-if="item.categories && item.categories.length > 0">
                                    <template x-for="cat in item.categories" :key="cat.id">
                                        <span class="inline-flex items-center bg-red-50 text-brand border border-red-200 px-3 py-1.5 rounded-md text-xs font-bold shadow-sm">
                                            <span x-text="cat.name"></span>
                                            <template x-if="cat.max_members">
                                                <span class="ml-1.5 pl-1.5 border-l border-red-200" x-text="cat.max_members + ' Pemain'"></span>
                                            </template>
                                            <template x-if="!cat.max_members">
                                                <span class="ml-1.5 pl-1.5 border-l border-red-200">Tidak dibatasi</span>
                                            </template>
                                        </span>
                                    </template>
                                </template>
                                <template x-if="(!item.categories || item.categories.length === 0)">
                                    <template x-if="item.max_members">
                                        <span class="inline-flex items-center bg-red-50 text-brand border border-red-200 px-3 py-1.5 rounded-md text-xs font-bold shadow-sm" x-text="'Kuota Total: ' + item.max_members + ' Pemain'"></span>
                                    </template>
                                    <template x-if="!item.max_members">
                                        <span class="inline-flex items-center bg-gray-50 text-gray-600 border border-gray-200 px-3 py-1.5 rounded-md text-xs font-bold shadow-sm">
                                            Kuota Total: Tidak dibatasi
                                        </span>
                                    </template>
                                </template>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-right align-top">
                            <div class="flex justify-end items-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity mt-1">
                                <button @click="handleEditSport(item)" class="p-1.5 text-gray-600 hover:bg-gray-200 rounded transition-colors" title="Edit">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                                <button @click="sportToDelete = item" class="p-1.5 text-red-600 hover:bg-red-100 rounded transition-colors" title="Hapus">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                </template>
            </template>
        </tbody>
    </table>
</div>
