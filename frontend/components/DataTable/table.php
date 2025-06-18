<div class="min-h-screen bg-slate-900 text-white p-8">
    <!-- Özet Bar -->
    <div class="flex flex-wrap gap-6 mb-8">
        <div class="bg-slate-800 rounded-xl px-8 py-4 text-center flex-1">
            <div class="text-2xl font-bold" id="totalServers">0</div>
            <div class="text-slate-400 text-sm">Toplam Sunucu</div>
        </div>
        <div class="bg-green-800 rounded-xl px-8 py-4 text-center flex-1">
            <div class="text-2xl font-bold" id="activeServers">0</div>
            <div class="text-green-200 text-sm">Aktif</div>
        </div>
        <div class="bg-red-800 rounded-xl px-8 py-4 text-center flex-1">
            <div class="text-2xl font-bold" id="downServers">0</div>
            <div class="text-red-200 text-sm">Kapalı</div>
        </div>
        <div class="bg-slate-800 rounded-xl px-8 py-4 text-center flex-1">
            <div class="text-lg" id="lastUpdate">Son güncelleme: --:--:--</div>
        </div>
    </div>
    <!-- Lokasyon Seçimi ve Arama -->
    <div class="flex flex-wrap gap-4 mb-8">
        <div class="flex gap-2">
            <button id="locationAllBtn" class="location-filter-btn px-4 py-2 rounded-lg bg-blue-600 text-white font-medium transition-all" data-location="Tümü">Tümü</button>
            <button id="locationMarsBtn" class="location-filter-btn px-4 py-2 rounded-lg bg-slate-800 text-blue-300 font-medium transition-all" data-location="Mars">Mars</button>
            <button id="locationHetznerBtn" class="location-filter-btn px-4 py-2 rounded-lg bg-slate-800 text-blue-300 font-medium transition-all" data-location="Hetzner">Hetzner</button>
        </div>
        <div class="flex-1">
            <select id="panelFilter" class="w-full px-4 py-2 bg-slate-800 border border-slate-700 rounded-lg text-blue-300 font-medium transition-all">
                <option value="all">Tüm Paneller</option>
                <option value="cPanel">cPanel</option>
                <option value="Plesk">Plesk</option>
                <option value="Backup">Backup</option>
                <option value="ESXi">ESXi</option>
                <option value="Yok">Yok</option>
                <option value="Diğer">Diğer</option>
            </select>
        </div>
        <div class="flex-1">
            <input type="text" id="searchInput" placeholder="Sunucu ara..." class="w-full px-4 py-2 bg-slate-800 border border-slate-700 rounded-lg text-blue-300 font-medium transition-all placeholder-blue-300/50" />
        </div>
    </div>
    <!-- Kart Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 gap-8" id="serverPanelGrid">
        <!-- Dinamik kartlar buraya gelecek -->
    </div>
    <div id="loading" class="flex justify-center my-6 hidden">
        <div class="animate-spin rounded-full h-10 w-10 border-t-4 border-blue-500"></div>
    </div>
    <div id="error" class="text-center text-red-600 hidden mt-4">
        Sunucular alınırken bir hata oluştu.
    </div>
    <div id="deleteModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-gray-500/75">
        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex size-12 shrink-0 items-center justify-center rounded-full bg-red-100 sm:mx-0 sm:size-10">
                                <svg class="size-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-base font-semibold text-gray-900" id="dialog-title">Sunucuyu silmek istediğinize emin misiniz?</h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">Bu işlem geri alınamaz. Lütfen emin olun.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                        <button type="button" id="confirmDelete" class="inline-flex w-full justify-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-xs hover:bg-red-500 sm:ml-3 sm:w-auto">Sil</button>
                        <button type="button" id="cancelDelete" class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-xs ring-1 ring-gray-300 ring-inset hover:bg-gray-50 sm:mt-0 sm:w-auto">Vazgeç</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>