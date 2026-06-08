@props(['isValid', 'validUntil', 'questionnaireVersion', 'algorithmVersion'])

<section class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
    <h2 class="mb-4 text-xs font-extrabold uppercase tracking-wide text-gray-500">
        Status Assessment
    </h2>
    <div class="divide-y divide-gray-50">
        <div class="flex items-center justify-between py-1.5">
            <span class="text-xs text-gray-500">Status</span>
            <span class="text-xs font-bold {{ $isValid ? 'text-green-600' : 'text-[#B41F2A]' }}">
                {{ $isValid ? 'Masih Berlaku' : 'Sudah Kadaluarsa' }}
            </span>
        </div>
        
        @if($validUntil)
        <div class="flex items-center justify-between py-1.5">
            <span class="text-xs text-gray-500">Berlaku Hingga</span>
            <span class="text-xs font-bold text-gray-700">
                {{ \Carbon\Carbon::parse($validUntil)->translatedFormat('d M Y') }}
            </span>
        </div>
        @endif
        
        <div class="flex items-center justify-between py-1.5">
            <span class="text-xs text-gray-500">Versi Kuesioner</span>
            <span class="text-xs font-bold text-gray-700">v{{ $questionnaireVersion }}</span>
        </div>
        
        <div class="flex items-center justify-between py-1.5">
            <span class="text-xs text-gray-500">Versi Algoritma</span>
            <span class="text-xs font-bold text-gray-700">v{{ $algorithmVersion }}</span>
        </div>
    </div>

    @if(!$isValid)
        <div class="mt-4 rounded-lg border border-amber-100 bg-amber-50 p-3">
            <p class="text-xs text-amber-700">
                Assessment ini sudah kadaluarsa. Silakan isi ulang untuk mendapatkan evaluasi terbaru.
            </p>
            <a href="{{ request()->user()->role === 'player' ? route('dashboard.player.self-assessment.index') : route('dashboard.pic.self-assessment.index') }}" 
               class="mt-2 inline-block text-xs font-bold text-amber-700 underline">
                Isi Ulang Sekarang &rarr;
            </a>
        </div>
    @endif
</section>
