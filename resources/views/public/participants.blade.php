<x-layout-public>
    <section class="w-full relative bg-cover bg-center bg-no-repeat py-16 bg-gray-50 min-h-screen" style="background-image: url('{{ asset('assets/home_original/bg_primary.png') }}');">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-10">
            <h2 class="text-3xl md:text-4xl font-bold text-center text-gray-500 mb-16 bg-white/50 inline-block px-8 py-3 rounded-full mx-auto flex w-fit">Peserta Tel-U Cup</h2>
            
            @php
                $participants = [
                    ['name' => 'Bidang I', 'img' => 'logo_Bidang_1.png'],
                    ['name' => 'Bidang II', 'img' => 'logo_Bidang_2.png'],
                    ['name' => 'Bidang III', 'img' => 'logo_Bidang_3.png'],
                    ['name' => 'Bidang IV', 'img' => 'logo_Bidang_4.png'],
                    ['name' => 'CS', 'img' => 'logo_CS.png'],
                    ['name' => 'FEB', 'img' => 'logo_FEB.png'],
                    ['name' => 'FIF', 'img' => 'logo_FIF.png'],
                    ['name' => 'FIK', 'img' => 'logo_FIK.png'],
                    ['name' => 'FIT', 'img' => 'logo_FIT.png'],
                    ['name' => 'FKS', 'img' => 'logo_FKS.png'],
                    ['name' => 'FRI', 'img' => 'logo_FRI.png'],
                    ['name' => 'FTE', 'img' => 'logo_FTE.png'],
                    ['name' => 'PAM', 'img' => 'logo_PAM.png'],
                    ['name' => 'Rektorat', 'img' => 'logo_Rektorat.png'],
                    ['name' => 'TUJ', 'img' => 'logo_TUKJ.png'],
                    ['name' => 'TUP', 'img' => 'logo_TUP.png'],
                    ['name' => 'TUS', 'img' => 'logo_TUS.png'],
                ];
            @endphp

            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-8 pb-10">
                @foreach($participants as $p)
                <div class="flex flex-col items-center justify-center text-center">
                    <a href="{{ route('participants.detail', ['name' => $p['name']]) }}" class="flex flex-col items-center justify-center hover:scale-110 transition-transform duration-300 group">
                        <img src="{{ asset('img/participants/' . $p['img']) }}" alt="{{ $p['name'] }}" class="h-[100px] object-contain filter drop-shadow-md">
                        <span class="mt-3 text-sm font-bold text-gray-500">{{ $p['name'] }}</span>
                    </a>
                </div>
                @endforeach
            </div>
        </div>
    </section>
</x-layout-public>
