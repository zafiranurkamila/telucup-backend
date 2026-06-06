{{-- Kontingen Summary Card --}}
<div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5 relative overflow-hidden">
    <div class="absolute top-0 right-0 w-32 h-32 bg-red-50 rounded-full -mr-10 -mt-10 opacity-50"></div>
    <h2 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-4 relative z-10">Informasi Kontingen</h2>
    
    <div class="space-y-3 relative z-10">
        <div>
            <p class="text-xs text-gray-400">Nama Kontingen</p>
            <p class="font-semibold text-gray-800">{{ $contingent->name ?? 'Nama Kontingen' }}</p>
        </div>
        <div>
            <p class="text-xs text-gray-400">PIC Utama</p>
            <p class="font-medium text-gray-700">{{ $user->name ?? '-' }}</p>
        </div>
        <div>
            <p class="text-xs text-gray-400">Email</p>
            <p class="font-medium text-gray-700">{{ $user->email ?? '-' }}</p>
        </div>
    </div>
</div>

{{-- Registrasi Tim Card --}}
<div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden flex flex-col">
    <div class="p-4 border-b border-gray-100 flex items-center justify-between">
        <h2 class="text-[15px] font-bold text-gray-800 flex items-center gap-2">
            <svg class="w-4 h-4 text-brand" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Status Registrasi
        </h2>
    </div>
    
    <div class="overflow-x-auto flex-1 max-h-96">
        <table class="w-full text-left text-sm text-gray-600">
            <thead class="bg-gray-50 text-gray-700 text-xs uppercase font-semibold border-b border-gray-200 sticky top-0">
                <tr>
                    <th class="px-4 py-3">Tim / Cabor</th>
                    <th class="px-4 py-3 text-right">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($teamRegistrations as $item)
                    @php
                        // Format Status
                        $statusClass = 'bg-gray-100 text-gray-700 border-gray-200';
                        $statusText = 'Draft';

                        switch (strtolower($item->status)) {
                            case 'verified':
                                $statusText = 'Terverifikasi';
                                $statusClass = 'bg-green-100 text-green-700 border-green-200';
                                break;
                            case 'submitted':
                            case 'pending':
                                $statusText = 'Menunggu Verifikasi';
                                $statusClass = 'bg-yellow-100 text-yellow-700 border-yellow-200';
                                break;
                            case 'rejected':
                                $statusText = 'Ditolak';
                                $statusClass = 'bg-red-100 text-red-700 border-red-200';
                                break;
                        }
                    @endphp
                    <tr class="hover:bg-gray-50/50">
                        <td class="px-4 py-3">
                            <div class="font-semibold text-gray-800 text-[13px]">{{ $item->sport->name ?? '' }} - {{ $item->sportCategory->name ?? '' }}</div>
                            <div class="text-xs text-gray-500 mt-0.5">{{ $item->current_members ?? 0 }} Anggota</div>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <span class="inline-flex items-center justify-center text-[10px] font-bold px-2 py-1 rounded-md border {{ $statusClass }}">
                                {{ $statusText }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2" class="px-4 py-8 text-center text-gray-500 text-sm">Belum ada tim yang didaftarkan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-3 border-t border-gray-100 bg-gray-50 text-center">
        <a href="{{ url('/dashboard/pic-kontingen/registrasi') }}" class="text-sm text-gray-600 hover:text-gray-900 font-medium transition-colors flex items-center justify-center gap-1">
            Kelola Semua Tim 
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
        </a>
    </div>
</div>
