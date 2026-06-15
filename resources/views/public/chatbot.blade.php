<x-layout-public title="AI Assistant Tel-U Cup">
    <section class="bg-white">
        <div class="mx-auto max-w-5xl px-4 py-10 sm:px-6 lg:px-8">
            <div class="mb-6">
                <p class="text-sm font-semibold uppercase tracking-wide text-[#b6252a]">Tel-U Cup</p>
                <h1 class="mt-2 text-3xl font-bold text-gray-900 sm:text-4xl">AI Assistant Tel-U Cup</h1>
                <p class="mt-3 max-w-3xl text-base leading-7 text-gray-600">
                    Saya dapat membantu informasi seputar jadwal, lokasi, hasil, bracket, cabang olahraga, dan kontingen Tel-U Cup.
                </p>
            </div>

            <div
                x-data="{
                    input: '',
                    loading: false,
                    messages: [
                        {
                            role: 'assistant',
                            text: 'Halo! Ada yang ingin ditanyakan seputar pertandingan Tel-U Cup?'
                        }
                    ],
                    async ask() {
                        const question = this.input.trim();
                        if (question.length < 3 || this.loading) return;

                        this.messages.push({ role: 'user', text: question });
                        this.input = '';
                        this.loading = true;
                        this.$nextTick(() => this.scrollChat());

                        try {
                            const res = await fetch('/api/public/chatbot/ask', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                    'Accept': 'application/json',
                                },
                                body: JSON.stringify({ question }),
                            });
                            const data = await res.json();

                            this.messages.push({
                                role: 'assistant',
                                text: res.ok ? (data.answer || 'Maaf, jawaban belum tersedia.') : (data.message || 'Terjadi kesalahan. Silakan coba lagi.'),
                            });
                        } catch (error) {
                            this.messages.push({
                                role: 'assistant',
                                text: 'Koneksi bermasalah, silakan coba lagi.',
                            });
                        } finally {
                            this.loading = false;
                            this.$nextTick(() => this.scrollChat());
                        }
                    },
                    scrollChat() {
                        if (this.$refs.chatArea) {
                            this.$refs.chatArea.scrollTop = this.$refs.chatArea.scrollHeight;
                        }
                    }
                }"
                class="overflow-hidden rounded-lg border border-gray-200 bg-gray-50 shadow-sm"
            >
                <div x-ref="chatArea" class="h-[520px] overflow-y-auto px-4 py-5 sm:px-6">
                    <div class="space-y-4">
                        <template x-for="(message, index) in messages" :key="index">
                            <div class="flex" :class="message.role === 'user' ? 'justify-end' : 'justify-start'">
                                <div
                                    class="max-w-[88%] rounded-lg px-4 py-3 text-sm leading-6 shadow-sm sm:max-w-[74%]"
                                    :class="message.role === 'user'
                                        ? 'bg-[#b6252a] text-white'
                                        : 'border border-gray-200 bg-white text-gray-800'"
                                >
                                    <p class="whitespace-pre-line break-words" x-text="message.text"></p>
                                </div>
                            </div>
                        </template>

                        <div x-show="loading" class="flex justify-start">
                            <div class="rounded-lg border border-gray-200 bg-white px-4 py-3 text-sm text-gray-600 shadow-sm">
                                <span class="inline-flex items-center gap-2">
                                    <span class="h-2 w-2 animate-pulse rounded-full bg-[#b6252a]"></span>
                                    Menyiapkan jawaban...
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="border-t border-gray-200 bg-white p-4">
                    <form @submit.prevent="ask" class="flex flex-col gap-3 sm:flex-row">
                        <textarea
                            x-model="input"
                            @keydown.enter.prevent="if (!$event.shiftKey) ask(); else input += '\n'"
                            maxlength="500"
                            rows="2"
                            placeholder="Tanyakan jadwal, hasil, bracket, cabor, atau kontingen..."
                            class="min-h-[52px] flex-1 resize-none rounded-lg border-gray-300 text-sm shadow-sm focus:border-[#b6252a] focus:ring-[#b6252a]"
                        ></textarea>
                        <button
                            type="submit"
                            :disabled="loading || input.trim().length < 3"
                            class="inline-flex h-[52px] items-center justify-center rounded-lg bg-[#b6252a] px-6 text-sm font-semibold text-white shadow-sm transition hover:bg-[#9c1f24] disabled:cursor-not-allowed disabled:bg-gray-300"
                        >
                            <span x-show="!loading">Kirim</span>
                            <span x-show="loading">Memproses</span>
                        </button>
                    </form>
                    <p class="mt-3 text-xs leading-5 text-gray-500">
                        Jawaban dihasilkan oleh AI dan dapat mengandung kesalahan. Konfirmasi info penting ke panitia.
                    </p>
                </div>
            </div>
        </div>
    </section>
</x-layout-public>
