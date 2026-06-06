<table class="w-full text-left text-sm text-gray-600">
    <thead class="bg-gray-50 text-gray-500 text-xs uppercase font-semibold border-b border-gray-200">
        <tr>
            <th class="px-6 py-4">Nama Kontingen</th>
            <th class="px-6 py-4">PIC Kontingen</th>
            <th class="px-6 py-4 text-center">Anggota</th>
            <th class="px-6 py-4">Status PIC</th>
            <th class="px-6 py-4 text-right">Kelola</th>
        </tr>
    </thead>
    <tbody class="divide-y divide-gray-100">
        <template x-if="isLoading">
            <template x-for="i in 3">
                <tr class="animate-pulse">
                    <td colspan="5" class="px-6 py-4"><div class="h-4 bg-gray-200 rounded w-full"></div></td>
                </tr>
            </template>
        </template>
        <template x-if="!isLoading && filteredContingents.length === 0">
            <tr>
                <td colspan="5" class="px-6 py-12 text-center text-gray-500">Data tidak ditemukan</td>
            </tr>
        </template>
        <template x-if="!isLoading && filteredContingents.length > 0">
            <template x-for="item in filteredContingents" :key="item.id">
                <tr class="hover:bg-gray-50 transition-colors group">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-gray-100 border border-gray-200 overflow-hidden shrink-0 flex items-center justify-center text-gray-400">
                                <template x-if="item.image_url">
                                    <img :src="item.image_url" :alt="item.name" class="w-full h-full object-cover">
                                </template>
                                <template x-if="!item.image_url">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                </template>
                            </div>
                            <div class="font-semibold text-gray-800" x-text="item.name"></div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <template x-if="item.pic">
                            <div>
                                <div class="font-medium text-gray-700" x-text="item.pic.name"></div>
                                <div class="text-xs text-gray-400" x-text="item.pic.email"></div>
                            </div>
                        </template>
                        <template x-if="!item.pic">
                            <span class="text-gray-400 italic text-xs">Belum ditugaskan</span>
                        </template>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="inline-flex items-center justify-center bg-gray-100 text-gray-700 text-xs font-bold px-2.5 py-1 rounded-full" x-text="item.players_count || 0"></span>
                    </td>
                    <td class="px-6 py-4">
                        <template x-if="item.pic">
                            <span class="inline-flex items-center gap-1 bg-green-50 text-green-700 text-xs font-medium px-2.5 py-1 rounded-full border border-green-100">
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                PIC Aktif
                            </span>
                        </template>
                        <template x-if="!item.pic">
                            <span class="inline-flex items-center gap-1 bg-orange-50 text-orange-700 text-xs font-medium px-2.5 py-1 rounded-full border border-orange-100">
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Belum ada PIC
                            </span>
                        </template>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex justify-end items-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button @click="detailContingentId = item.id" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded" title="Detail">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </button>
                            <button @click="openAssignPic(item)" class="p-1.5 text-indigo-600 hover:bg-indigo-50 rounded" title="Assign PIC">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                            </button>
                            <button @click="openEditContingent(item)" class="p-1.5 text-gray-600 hover:bg-gray-100 rounded" title="Edit">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </button>
                            <button @click="deleteContingentData = item" class="p-1.5 text-red-600 hover:bg-red-50 rounded" title="Hapus">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </td>
                </tr>
            </template>
        </template>
    </tbody>
</table>
