<x-layout-public>
    <!-- Header Section -->
    <section class="w-full relative bg-cover bg-center bg-no-repeat pt-16 pb-8 bg-gray-50" style="background-image: url('{{ asset('assets/home_original/bg_primary.png') }}');">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center mt-10">
            <div class="flex flex-col items-center justify-center">
                <img src="{{ asset('img/participants/' . $img) }}" alt="{{ $name }}" class="h-[120px] object-contain drop-shadow-md mb-4">
                <h2 class="text-2xl md:text-3xl font-bold text-gray-500 mb-8">{{ $name }}</h2>
                
                <a href="{{ route('login') }}" class="bg-[#ed1e28] text-white px-6 py-2.5 rounded-md font-medium text-sm inline-flex items-center hover:bg-[#c4151e] transition-colors shadow-md">
                    Registrasi Pemain <span class="ml-2 font-bold">&rarr;</span>
                </a>
            </div>

            <!-- Sport Chips Carousel -->
            <div class="relative w-full max-w-6xl mx-auto mt-12 mb-4 group" x-data="{
                scrollNext() { $refs.slider.scrollBy({ left: 300, behavior: 'smooth' }); },
                scrollPrev() { $refs.slider.scrollBy({ left: -300, behavior: 'smooth' }); }
            }">
                <!-- Left Arrow -->
                <button @click="scrollPrev()" class="absolute -left-4 md:-left-8 top-1/2 transform -translate-y-1/2 z-10 bg-[#ed1e28] border-2 border-white rounded-full shadow-lg p-2 hover:bg-[#c4151e] text-white focus:outline-none hidden md:flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                </button>

                <!-- Container -->
                <div x-ref="slider" class="flex overflow-x-auto gap-3 md:gap-4 scroll-smooth px-2 py-3 snap-x" style="scrollbar-width: none; -ms-overflow-style: none;">
                    <style>
                        .hide-scrollbar::-webkit-scrollbar { display: none; }
                    </style>
                    @php
                        $sportNames = [];
                        foreach($sports as $sport) {
                            if($sport->categories->count() > 0) {
                                foreach($sport->categories as $cat) {
                                    $sName = $sport->name;
                                    if (!in_array(strtolower($cat->name), ['reguler', 'individu', 'team'])) {
                                        $sName .= ' ' . $cat->name;
                                    }
                                    if(!in_array($sName, $sportNames)) $sportNames[] = $sName;
                                }
                            } else {
                                if(!in_array($sport->name, $sportNames)) $sportNames[] = $sport->name;
                            }
                        }
                    @endphp

                    @foreach($sportNames as $sName)
                        <a href="#sport-{{ Str::slug($sName) }}" class="bg-[#ed1e28] text-white px-4 py-2.5 rounded-md text-sm font-medium text-center shadow-sm flex items-center justify-center min-w-[140px] whitespace-nowrap snap-center shrink-0 hover:bg-[#c4151e] transition-colors">
                            {{ $sName }}
                        </a>
                    @endforeach
                </div>

                <!-- Right Arrow -->
                <button @click="scrollNext()" class="absolute -right-4 md:-right-8 top-1/2 transform -translate-y-1/2 z-10 bg-[#ed1e28] border-2 border-white rounded-full shadow-lg p-2 hover:bg-[#c4151e] text-white focus:outline-none hidden md:flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </button>
            </div>
        </div>
    </section>

    <!-- Player Tables Section -->
    <section class="bg-[#242424] w-full py-12 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h3 class="text-xl font-bold text-white text-center mb-12 tracking-wider">DAFTAR PEMAIN {{ strtoupper($name) }}</h3>

            @foreach($sportNames as $sName)
                <div id="sport-{{ Str::slug($sName) }}" class="mb-12 scroll-mt-28">
                    <h4 class="text-white font-semibold text-lg mb-4">{{ $sName }}</h4>
                    <div class="bg-white rounded-md overflow-x-auto shadow-sm">
                        <table class="w-full text-left text-sm whitespace-nowrap min-w-max">
                            <thead class="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="px-6 py-4 font-bold text-gray-800 w-16 text-center">No</th>
                                    <th class="px-6 py-4 font-bold text-gray-800 w-24">Foto</th>
                                    <th class="px-6 py-4 font-bold text-gray-800">Nama Pemain</th>
                                    <th class="px-6 py-4 font-bold text-gray-800">Status Pegawai</th>
                                    <th class="px-6 py-4 font-bold text-gray-800">Lokasi Kerja</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @if(isset($playersBySport[$sName]) && $playersBySport[$sName]->count() > 0)
                                    @foreach($playersBySport[$sName] as $index => $player)
                                        <tr class="hover:bg-gray-50 transition-colors">
                                            <td class="px-6 py-4 text-center text-gray-600 font-medium">{{ $index + 1 }}</td>
                                            <td class="px-6 py-4">
                                                @if($player->photo_path)
                                                    <img src="{{ Storage::url($player->photo_path) }}" alt="Foto" class="w-12 h-12 object-cover rounded shadow-sm border border-gray-100">
                                                @else
                                                    <div class="w-12 h-12 bg-indigo-100 text-indigo-600 rounded flex items-center justify-center font-bold text-lg shadow-sm border border-gray-100">
                                                        {{ substr($player->name, 0, 1) }}
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 text-gray-800 font-medium uppercase">{{ $player->name }}</td>
                                            <td class="px-6 py-4 text-gray-600 uppercase">{{ $player->employee_status ?? '-' }}</td>
                                            <td class="px-6 py-4 text-gray-600 uppercase">{{ $player->work_location ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="5" class="px-6 py-10 text-center text-gray-500 font-medium text-base">
                                            Pemain belum didaftarkan!
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach

        </div>
    </section>
</x-layout-public>
