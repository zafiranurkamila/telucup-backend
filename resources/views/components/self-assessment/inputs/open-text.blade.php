@props(['question'])

<textarea name="{{ $question['code'] }}" 
          x-model="answers['{{ $question['code'] }}']"
          rows="3"
          class="w-full rounded-lg border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 shadow-sm focus:border-[#B41F2A] focus:outline-none focus:ring-1 focus:ring-[#B41F2A] transition-shadow resize-y"
          placeholder="Tuliskan jawaban Anda di sini..."></textarea
