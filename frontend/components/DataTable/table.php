<div class="min-h-screen bg-slate-900 text-white p-8">
    <!-- Header with Settings Button -->
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-white">Sunucu Durumu</h1>
        <div class="flex items-center gap-4">
            <a href="/netsentinel/pages/notifications.php" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                <i class="fas fa-bell mr-2"></i>Bildirimler
            </a>
            <a href="/netsentinel/pages/settings.php" class="bg-slate-700 hover:bg-slate-600 text-white px-4 py-2 rounded-lg transition-colors">
                <i class="fas fa-cog mr-2"></i>Ayarlar
            </a>
        </div>
    </div>

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
    <!-- Filtreler: Büyük ve Modern Tek Satır -->
    <div class="flex flex-wrap gap-3 mb-8 items-center">
        <!-- Durum Filtreleme -->
        <button id="statusAllBtn" class="chip-btn status-filter-btn bg-blue-600 text-white border border-blue-600 rounded-full px-5 py-2 text-sm font-semibold transition-all shadow" data-status="all">Tümü</button>
        <button id="statusActiveBtn" class="chip-btn status-filter-btn bg-slate-800 text-green-300 border border-slate-700 rounded-full px-5 py-2 text-sm font-semibold transition-all" data-status="active">Aktif</button>
        <button id="statusInactiveBtn" class="chip-btn status-filter-btn bg-slate-800 text-red-300 border border-slate-700 rounded-full px-5 py-2 text-sm font-semibold transition-all" data-status="inactive">Kapalı</button>
        <!-- Lokasyon Filtreleme -->
        <button id="locationAllBtn" class="chip-btn location-filter-btn bg-slate-800 text-blue-200 border border-slate-700 rounded-full px-5 py-2 text-sm font-semibold transition-all" data-location="all">Tümü</button>
        <button id="locationMarsBtn" class="chip-btn location-filter-btn bg-slate-800 text-blue-200 border border-slate-700 rounded-full px-5 py-2 text-sm font-semibold transition-all" data-location="mars">Mars</button>
        <button id="locationHetznerBtn" class="chip-btn location-filter-btn bg-slate-800 text-blue-200 border border-slate-700 rounded-full px-5 py-2 text-sm font-semibold transition-all" data-location="hetzner">Hetzner</button>
        <!-- Panel Filtreleme -->
        <select id="panelFilter" class="ml-2 px-5 py-2 bg-slate-800 border border-slate-700 rounded-full text-blue-200 text-sm font-semibold transition-all h-10 min-w-[120px]">
            <option value="all">Tüm Paneller</option>
            <option value="cPanel">cPanel</option>
            <option value="Plesk">Plesk</option>
            <option value="Backup">Backup</option>
            <option value="ESXi">ESXi</option>
            <option value="Yok">Yok</option>
            <option value="Diğer">Diğer</option>
        </select>
        <!-- Arama -->
        <input type="text" id="searchInput" placeholder="Ara..." class="ml-2 px-5 py-2 bg-slate-800 border border-slate-700 rounded-full text-blue-200 text-sm font-semibold transition-all placeholder-blue-300/50 h-10 w-44" />
        <!-- Filtreleri Temizle: Tek büyük buton, sağda -->
        <button id="clearFiltersBtn" class="ml-auto px-6 py-2 rounded-full bg-red-600 text-white text-base font-semibold transition-all hover:bg-red-700">Filtreleri Temizle</button>
    </div>

    <!-- Pagination ve Eleman Sayısı Kontrolleri -->
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6 p-4 bg-slate-800 rounded-xl max-w-full overflow-x-auto">
        <!-- Sol: Eleman Sayısı Seçici -->
        <div class="flex items-center gap-3">
            <span class="text-slate-300 text-sm font-medium">Sayfa başına:</span>
            <div class="flex gap-2">
                <button id="limit50Btn" class="limit-btn px-4 py-2 rounded-lg bg-slate-700 text-slate-300 border border-slate-600 text-sm font-semibold transition-all hover:bg-slate-600" data-limit="50">50</button>
                <button id="limit100Btn" class="limit-btn px-4 py-2 rounded-lg bg-blue-600 text-white border border-blue-600 text-sm font-semibold transition-all shadow" data-limit="100">100</button>
                <button id="limit200Btn" class="limit-btn px-4 py-2 rounded-lg bg-slate-700 text-slate-300 border border-slate-600 text-sm font-semibold transition-all hover:bg-slate-600" data-limit="200">200</button>
            </div>
        </div>

        <!-- Orta: Sayfa Bilgisi -->
        <div class="flex items-center gap-4">
            <span class="text-slate-300 text-sm">
                <span id="currentPageInfo">Sayfa 1</span> / <span id="totalPagesInfo">1</span>
            </span>
            <span class="text-slate-400 text-sm">
                Toplam <span id="totalItemsInfo">0</span> sunucu
            </span>
        </div>

        <!-- Sağ: Sayfa Navigasyonu -->
        <div class="flex items-center gap-2">
            <button id="firstPageBtn" class="page-nav-btn px-3 py-2 rounded-lg bg-slate-700 text-slate-300 border border-slate-600 text-sm font-semibold transition-all hover:bg-slate-600 disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path>
                </svg>
            </button>
            <button id="prevPageBtn" class="page-nav-btn px-3 py-2 rounded-lg bg-slate-700 text-slate-300 border border-slate-600 text-sm font-semibold transition-all hover:bg-slate-600 disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </button>
            
            <!-- Sayfa Numaraları -->
            <div id="pageNumbers" class="flex gap-1">
                <!-- Dinamik sayfa numaraları buraya gelecek -->
            </div>
            
            <button id="nextPageBtn" class="page-nav-btn px-3 py-2 rounded-lg bg-slate-700 text-slate-300 border border-slate-600 text-sm font-semibold transition-all hover:bg-slate-600 disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </button>
            <button id="lastPageBtn" class="page-nav-btn px-3 py-2 rounded-lg bg-slate-700 text-slate-300 border border-slate-600 text-sm font-semibold transition-all hover:bg-slate-600 disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7"></path>
                </svg>
            </button>
        </div>
    </div>

    <!-- Kart Grid -->
    <div class="w-full px-2">
      <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 gap-8" id="serverPanelGrid">
          <!-- Dinamik kartlar buraya gelecek -->
      </div>
    </div>
    
    <div id="loading" class="flex justify-center my-6 hidden">
        <div class="animate-spin rounded-full h-10 w-10 border-t-4 border-blue-500"></div>
    </div>
    <div id="error" class="text-center text-red-600 hidden mt-4">
        Sunucular alınırken bir hata oluştu.
    </div>
    
    <!-- Alt Pagination -->
    <div class="flex items-center justify-center gap-4 mt-8 p-4 bg-slate-800 rounded-xl max-w-full overflow-x-auto">
        <button id="firstPageBtnBottom" class="page-nav-btn px-4 py-2 rounded-lg bg-slate-700 text-slate-300 border border-slate-600 text-sm font-semibold transition-all hover:bg-slate-600 disabled:opacity-50 disabled:cursor-not-allowed" disabled>
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path>
            </svg>
            İlk
        </button>
        <button id="prevPageBtnBottom" class="page-nav-btn px-4 py-2 rounded-lg bg-slate-700 text-slate-300 border border-slate-600 text-sm font-semibold transition-all hover:bg-slate-600 disabled:opacity-50 disabled:cursor-not-allowed" disabled>
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            Önceki
        </button>
        
        <div id="pageNumbersBottom" class="flex gap-1">
            <!-- Dinamik sayfa numaraları buraya gelecek -->
        </div>
        
        <button id="nextPageBtnBottom" class="page-nav-btn px-4 py-2 rounded-lg bg-slate-700 text-slate-300 border border-slate-600 text-sm font-semibold transition-all hover:bg-slate-600 disabled:opacity-50 disabled:cursor-not-allowed" disabled>
            Sonraki
            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
        </button>
        <button id="lastPageBtnBottom" class="page-nav-btn px-4 py-2 rounded-lg bg-slate-700 text-slate-300 border border-slate-600 text-sm font-semibold transition-all hover:bg-slate-600 disabled:opacity-50 disabled:cursor-not-allowed" disabled>
            Son
            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7"></path>
            </svg>
        </button>
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