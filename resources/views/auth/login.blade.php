<x-layout-public>
    <x-slot:title>Login</x-slot:title>

    <div class="min-h-[calc(100vh-76px)] flex flex-col sm:justify-center items-center pt-6 sm:pt-0 pb-10">
        <div x-data="{ isLoading: false }" class="bg-white w-full max-w-[460px] rounded shadow-[0_8px_30px_rgb(0,0,0,0.04)] overflow-hidden border border-gray-100 mt-8 sm:mt-0">
            {{-- Header --}}
            <div class="text-center py-6 border-b border-brand-light bg-brand-bg">
                <h1 class="text-xl font-medium text-[#34495e] mb-2">
                    Login Admin Tel-U Cup
                </h1>
                <p class="text-[13px] text-brand font-semibold">
                    (*Gunakan akun SSO)
                </p>
            </div>

            {{-- Body --}}
            <div class="p-8 pb-6 bg-white">
                <form id="login-form" method="POST" action="{{ route('login') }}" @submit="isLoading = true">
                    @csrf

                    {{-- Error Messages --}}
                    @if ($errors->any())
                        <div class="mb-4 p-3 bg-red-50 text-red-600 border border-red-200 rounded text-sm text-center">
                            @foreach ($errors->all() as $error)
                                <p>{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif

                    {{-- Session Status --}}
                    <x-auth-session-status class="mb-4" :status="session('status')" />

                    {{-- Email / Username --}}
                    <div class="mb-5">
                        <label for="email" class="block text-[13px] text-[#95a5a6] mb-1.5">
                            Username
                        </label>
                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            placeholder="Enter username SSO"
                            class="w-full border border-gray-200 rounded px-3.5 py-2.5 text-sm text-gray-700 focus:outline-none focus:border-gray-300 placeholder:text-gray-300"
                            required
                            autofocus
                            autocomplete="username"
                        />
                    </div>

                    {{-- Password --}}
                    <div class="mb-2">
                        <label for="password" class="block text-[13px] text-[#95a5a6] mb-1.5">
                            Password
                        </label>
                        <div x-data="{ show: false }" class="flex border border-gray-200 rounded focus-within:border-gray-300 overflow-hidden">
                            <input
                                id="password"
                                :type="show ? 'text' : 'password'"
                                name="password"
                                placeholder="Enter password SSO"
                                class="w-full px-3.5 py-2.5 text-sm text-gray-700 focus:outline-none border-none placeholder:text-gray-300"
                                required
                                autocomplete="current-password"
                            />
                            <button
                                type="button"
                                @click="show = !show"
                                class="px-3.5 flex items-center justify-center bg-gray-50 border-l border-gray-200 text-gray-400 hover:text-gray-600 focus:outline-none transition-colors"
                            >
                                {{-- Eye icon --}}
                                <svg x-show="!show" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                {{-- Eye-off icon --}}
                                <svg x-show="show" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            {{-- Footer --}}
            <div class="py-5 border-t border-brand-light bg-brand-bg flex justify-center">
                <button
                    type="submit"
                    form="login-form"
                    :disabled="isLoading"
                    class="bg-brand hover:bg-brand-hover text-white text-[15px] font-medium py-2 px-10 rounded transition-colors inline-flex items-center gap-2 disabled:opacity-75 disabled:cursor-not-allowed"
                >
                    <svg x-show="isLoading" class="animate-spin -ml-1 mr-1 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span x-text="isLoading ? 'Loading...' : 'Login'"></span>
                </button>
            </div>
        </div>
    </div>
</x-layout-public>
