<table class="w-full text-left text-sm text-gray-600">
    <thead class="bg-gray-50 text-gray-500 text-xs uppercase font-semibold border-b border-gray-200">
        <tr>
            <th class="px-6 py-4">Nama & Akun PIC</th>
            <th class="px-6 py-4">Status Civitas</th>
            <th class="px-6 py-4">Status Risiko</th>
            <th class="px-6 py-4">Kontingen yang Dikelola</th>
        </tr>
    </thead>
    <tbody class="divide-y divide-gray-100">
        <template x-if="isLoading">
            <tr><td colspan="4" class="px-6 py-4 text-center">Memuat...</td></tr>
        </template>
        <template x-if="!isLoading && filteredPics.length === 0">
            <tr><td colspan="4" class="px-6 py-12 text-center text-gray-500">Tidak ada data PIC</td></tr>
        </template>
        <template x-if="!isLoading && filteredPics.length > 0">
            <template x-for="pic in filteredPics" :key="pic.id">
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-blue-50 border border-blue-100 overflow-hidden shrink-0 flex items-center justify-center text-blue-600">
                                <template x-if="pic.photo_path">
                                    <img :src="pic.photo_path" :alt="pic.name" class="w-full h-full object-cover">
                                </template>
                                <template x-if="!pic.photo_path">
                                    <span class="font-bold text-sm" x-text="pic.name ? pic.name.charAt(0).toUpperCase() : '?'"></span>
                                </template>
                            </div>
                            <div>
                                <div class="font-medium text-gray-800" x-text="pic.name"></div>
                                <div class="text-xs text-gray-400" x-text="pic.email"></div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span x-text="pic.employee_status === 'student' ? 'Mahasiswa' : (pic.employee_status === 'employee' ? 'Karyawan / Dosen' : (pic.employee_status || '-'))"></span>
                    </td>
                    <td class="px-6 py-4">
                        <span :class="getRiskBadgeClasses(pic.risk_lvl)" class="inline-flex items-center text-xs font-semibold px-2.5 py-1 rounded-full border" x-text="getRiskBadgeText(pic.risk_lvl)"></span>
                    </td>
                    <td class="px-6 py-4">
                        <template x-if="pic.managed_contingent">
                            <span class="font-semibold text-brand bg-red-50 px-3 py-1 rounded border border-red-100" x-text="pic.managed_contingent.name"></span>
                        </template>
                        <template x-if="!pic.managed_contingent">
                            <span class="text-gray-400 italic text-xs">Belum diassign</span>
                        </template>
                    </td>
                </tr>
            </template>
        </template>
    </tbody>
</table>
