<nav class="bg-white w-full flex items-center justify-between px-3 py-2 sm:px-6 sm:py-4 shadow-sm h-20">
    <a href="<?= $baseUrl ?>" class="inline-flex items-center justify-center 
                px-3 py-2 text-xl sm:text-2xl md:text-3xl font-extrabold tracking-tight 
                bg-black/80 text-white rounded-xl shadow-lg backdrop-blur-sm border border-white/10 
                transition duration-300 hover:shadow-2xl hover:scale-105 hover:bg-black">
        <span class="bg-gradient-to-r from-white via-gray-300 to-white bg-clip-text text-transparent">
            <?= $appName ?>
        </span>
    </a>

    <div class="flex items-center space-x-2 sm:space-x-4">
        <a href="<?= $baseUrl ?>/server/addServer" class="py-2 px-3 sm:py-2.5 sm:px-4 text-sm sm:text-base bg-gray-100 hover:bg-gray-200 font-medium rounded-full transition">
            Sunucu Ekle
        </a>

        <button id="refreshButton" type="button" class="p-2 hover:bg-gray-200 rounded-full transition relative">
            <svg id="spinner" class="hidden animate-spin h-5 w-5 text-gray-700 sm:h-5 sm:w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
            </svg>

            <svg id="refreshIcon" xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-gray-700">
                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                <path d="M20 11a8.1 8.1 0 0 0 -15.5 -2M4 5v4h4" />
                <path d="M4 13a8.1 8.1 0 0 0 15.5 2M20 19v-4h-4" />
            </svg>
        </button>

    </div>
</nav>