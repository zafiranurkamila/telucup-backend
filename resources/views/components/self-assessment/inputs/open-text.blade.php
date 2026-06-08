@props(['question'])

<textarea name="{{ $question['code'] }}"
          x-model="answers['{{ $question['code'] }}']"
          rows="3"
          class="w-full resize-y rounded-md border border-gray-300 bg-gray-50 px-4 py-3 text-sm font-medium text-gray-900 transition-colors focus:border-[#B41F2A] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#B41F2A]/10"
          placeholder="Tuliskan jawaban singkat"></textarea>
