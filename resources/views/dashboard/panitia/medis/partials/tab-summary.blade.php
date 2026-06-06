@php
    $listVar = $type === 'contingent' ? 'contingentSummary' : 'sportSummary';
    $labelName = $type === 'contingent' ? 'Kontingen' : 'Cabang Olahraga';
    $keyName = $type === 'contingent' ? 'contingent' : 'sport_branch';
@endphp

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <template x-if="loadingSummary">
        <div class="flex justify-center items-center h-48 gap-3 text-gray-500">
            <div class="w-6 h-6 border-2 border-[#B41F2A] border-t-transparent rounded-full animate-spin"></div>
            <span class="font-medium">Memuat ringkasan...</span>
        </div>
    </template>
    
    <template x-if="!loadingSummary && {{ $listVar }}.length === 0">
        <div class="p-12 text-center text-gray-500 font-medium">Belum ada data assessment.</div>
    </template>
    
    <template x-if="!loadingSummary && {{ $listVar }}.length > 0">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-gray-50 text-gray-500 text-[11px] uppercase tracking-wider">
                        <th class="p-4 font-bold border-b border-gray-100">{{ $labelName }}</th>
                        <th class="p-4 font-bold border-b border-gray-100 text-center">Total</th>
                        <th class="p-4 font-bold border-b border-gray-100 text-center">High</th>
                        <th class="p-4 font-bold border-b border-gray-100 text-center">Medium</th>
                        <th class="p-4 font-bold border-b border-gray-100 text-center">Low</th>
                        <th class="p-4 font-bold border-b border-gray-100 text-center">Kacamata</th>
                        <th class="p-4 font-bold border-b border-gray-100 min-w-[200px]">Distribusi Risiko</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <template x-for="(row, i) in {{ $listVar }}" :key="i">
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="p-4 font-bold text-gray-800" x-text="row.{{ $keyName }} || '(Tidak diketahui)'"></td>
                            <td class="p-4 text-center font-bold text-gray-800" x-text="row.total_assessed || 0"></td>
                            <td class="p-4 text-center">
                                <span class="px-2 py-0.5 bg-red-100 text-red-700 rounded text-xs font-bold" x-text="row.high_risk_count || 0"></span>
                            </td>
                            <td class="p-4 text-center">
                                <span class="px-2 py-0.5 bg-amber-100 text-amber-700 rounded text-xs font-bold" x-text="row.medium_risk_count || 0"></span>
                            </td>
                            <td class="p-4 text-center">
                                <span class="px-2 py-0.5 bg-green-100 text-green-700 rounded text-xs font-bold" x-text="row.low_risk_count || 0"></span>
                            </td>
                            <td class="p-4 text-center text-gray-600 font-medium" x-text="row.kacamata_count || 0"></td>
                            <td class="p-4">
                                <div class="h-2.5 rounded-full overflow-hidden bg-gray-100 flex w-full">
                                    <div class="bg-[#B41F2A] h-full" :style="`width: ${row.total_assessed ? Math.round((row.high_risk_count / row.total_assessed) * 100) : 0}%`"></div>
                                    <div class="bg-amber-500 h-full" :style="`width: ${row.total_assessed ? Math.round((row.medium_risk_count / row.total_assessed) * 100) : 0}%`"></div>
                                    <div class="bg-green-600 h-full" :style="`width: ${row.total_assessed ? Math.round((row.low_risk_count / row.total_assessed) * 100) : 0}%`"></div>
                                </div>
                                <p class="text-[10px] text-gray-400 mt-1.5 font-medium">
                                    <span x-text="(row.total_assessed ? Math.round((row.high_risk_count / row.total_assessed) * 100) : 0) + '% high'"></span> &middot;
                                    <span x-text="(row.total_assessed ? Math.round((row.medium_risk_count / row.total_assessed) * 100) : 0) + '% med'"></span> &middot;
                                    <span x-text="(row.total_assessed ? Math.round((row.low_risk_count / row.total_assessed) * 100) : 0) + '% low'"></span>
                                </p>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </template>
</div>
