<div class="border-b border-gray-200">
    <nav class="-mb-px flex space-x-8">
        <button 
            @click="activeTab = 'kontingen'"
            :class="activeTab === 'kontingen' ? 'border-brand text-brand' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
            class="whitespace-nowrap pb-4 px-1 border-b-2 font-medium text-sm transition-colors"
        >
            Kontingen
        </button>
        <button 
            @click="activeTab = 'pic'"
            :class="activeTab === 'pic' ? 'border-brand text-brand' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
            class="whitespace-nowrap pb-4 px-1 border-b-2 font-medium text-sm transition-colors"
        >
            Daftar PIC
        </button>
        <button 
            @click="activeTab = 'player'"
            :class="activeTab === 'player' ? 'border-brand text-brand' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
            class="whitespace-nowrap pb-4 px-1 border-b-2 font-medium text-sm transition-colors"
        >
            Daftar Player
        </button>
    </nav>
</div>
