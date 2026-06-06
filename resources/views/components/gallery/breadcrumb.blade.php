<nav class="flex items-center text-sm font-medium overflow-x-auto hide-scrollbar whitespace-nowrap bg-gray-50/50 p-2 rounded-lg border border-gray-100">
    <button @click="goToRoot()" class="flex items-center gap-2 text-gray-500 hover:text-gray-900 transition-colors px-2 py-1 rounded hover:bg-gray-100" :class="currentFolderId === null ? 'text-gray-900 font-bold' : ''">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
        Gallery Event
    </button>
    
    <template x-for="(crumb, index) in breadcrumbs" :key="crumb.id">
        <div class="flex items-center">
            <svg class="w-4 h-4 mx-2 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <button @click="goToFolder(crumb.id)" class="px-2 py-1 rounded hover:bg-gray-100 transition-colors truncate max-w-[150px] sm:max-w-[200px]" :class="index === breadcrumbs.length - 1 ? 'text-gray-900 font-bold' : 'text-gray-500 hover:text-gray-900'">
                <span x-text="crumb.name"></span>
            </button>
        </div>
    </template>
</nav>
