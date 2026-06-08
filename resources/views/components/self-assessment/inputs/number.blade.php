@props(['question'])

<input type="number" 
       name="{{ $question['code'] }}" 
       x-model.number="answers['{{ $question['code'] }}']"
       class="w-full rounded-lg border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 shadow-sm focus:border-[#B41F2A] focus:outline-none focus:ring-1 focus:ring-[#B41F2A] max-w-xs transition-shadow"
       placeholder="Masukkan angka..." />
