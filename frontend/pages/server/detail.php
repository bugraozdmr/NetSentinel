<div id="loading-detail" class="text-center text-lg font-semibold text-slate-400 py-6 animate-pulse">
    <svg class="mx-auto mb-2 w-7 h-7 animate-spin text-blue-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
    </svg>
    Yükleniyor...
</div>

<div id="serverDetail" class="max-w-5xl mx-auto bg-slate-900/80 shadow-2xl rounded-3xl p-6 md:p-10 space-y-10 border border-slate-800 backdrop-blur-md my-8 md:my-12">
    <div class="mb-6 text-center">
        <h1 class="text-3xl md:text-4xl font-extrabold bg-gradient-to-r from-blue-400 to-blue-200 bg-clip-text text-transparent tracking-tight flex items-center justify-center gap-2">
            <span class="text-2xl md:text-3xl">🌐</span> Sunucu Detayı
        </h1>
        <p class="text-slate-400 text-lg mt-2">Sunucu hakkında ayrıntılı bilgiler</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
        <div class="bg-slate-800/80 p-5 rounded-2xl shadow-lg border border-slate-700 flex flex-col items-start">
            <div class="flex items-center gap-2 mb-1 text-blue-400"><svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5.121 17.804A13.937 13.937 0 0112 15c2.5 0 4.847.655 6.879 1.804"/></svg> <span class="text-xs text-slate-400">İsim</span></div>
            <p id="name" class="text-lg font-semibold text-slate-100 mt-1"></p>
        </div>
        <div class="bg-slate-800/80 p-5 rounded-2xl shadow-lg border border-slate-700 flex flex-col items-start">
            <div class="flex items-center gap-2 mb-1 text-blue-400"><svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" stroke-linejoin="round" d="M2 12h20"/></svg> <span class="text-xs text-slate-400">IP Adresi</span></div>
            <p id="ip" class="text-lg font-semibold text-slate-100 mt-1"></p>
        </div>
        <div class="bg-slate-800/80 p-5 rounded-2xl shadow-lg border border-slate-700 flex flex-col items-start">
            <div class="flex items-center gap-2 mb-1 text-blue-400"><svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 12.414a4 4 0 10-1.414 1.414l4.243 4.243a1 1 0 001.414-1.414z"/></svg> <span class="text-xs text-slate-400">Konum</span></div>
            <p id="location" class="text-lg font-semibold text-slate-100 mt-1"></p>
        </div>
        <div class="bg-slate-800/80 p-5 rounded-2xl shadow-lg border border-slate-700 flex flex-col items-start">
            <div class="flex items-center gap-2 mb-1 text-blue-400"><svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 12.414a4 4 0 10-1.414 1.414l4.243 4.243a1 1 0 001.414-1.414z"/></svg> <span class="text-xs text-slate-400">Panel</span></div>
            <p id="panel" class="text-lg font-semibold text-slate-100 mt-1"></p>
        </div>
        <div class="bg-slate-800/80 p-5 rounded-2xl shadow-lg border border-slate-700 flex flex-col items-start">
            <div class="flex items-center gap-2 mb-1 text-blue-400"><svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3"/></svg> <span class="text-xs text-slate-400">Durum</span></div>
            <p id="is_active" class="text-lg font-semibold text-slate-100 mt-1"></p>
        </div>
        <div class="bg-slate-800/80 p-5 rounded-2xl shadow-lg border border-slate-700 flex flex-col items-start">
            <div class="flex items-center gap-2 mb-1 text-blue-400"><svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3"/></svg> <span class="text-xs text-slate-400">Son Kontrol</span></div>
            <p id="last_check_at" class="text-lg font-semibold text-slate-100 mt-1"></p>
        </div>
    </div>

    <!-- SON KONTROLLER (YENİ TASARIM) -->
    <section class="bg-slate-900/90 rounded-2xl shadow-2xl border border-blue-900 px-6 py-8 my-8">
      <h2 class="flex items-center gap-3 text-2xl font-extrabold bg-gradient-to-r from-blue-400 to-blue-200 bg-clip-text text-transparent mb-6">
        <svg class="w-7 h-7 text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2a4 4 0 118 0v2"/></svg>
        Son Kontroller
      </h2>
      <div id="checkList" class="flex flex-wrap gap-6 justify-center">
      </div>
    </section>

    <!-- PORT DURUMU (YENİ TASARIM) -->
    <section class="bg-slate-900/90 rounded-2xl shadow-2xl border border-blue-900 px-6 py-8 my-8">
      <h2 class="flex items-center gap-3 text-2xl font-extrabold bg-gradient-to-r from-blue-400 to-blue-200 bg-clip-text text-transparent mb-6">
        <svg class="w-7 h-7 text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" stroke-linejoin="round" d="M2 12h20"/></svg>
        Port Durumu
      </h2>
      <div id="ports" class="flex flex-wrap gap-6 justify-center">
      </div>
    </section>

    <div id="chartContainer" class="bg-slate-800/80 p-5 rounded-2xl shadow-lg border border-slate-700 max-w-xl mx-auto">
        <h2 class="text-xl font-semibold bg-gradient-to-r from-blue-400 to-blue-200 bg-clip-text text-transparent mb-3 flex items-center gap-2"><svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3"/></svg> Ping Süreleri (ms)</h2>
        <canvas id="pingChart" style="max-height: 300px;"></canvas>
    </div>

    <div id="notifications" class="bg-slate-800/80 p-5 rounded-2xl shadow-lg border border-slate-700 max-w-5xl mx-auto mt-10 hidden">
        <h2 class="text-xl font-semibold bg-gradient-to-r from-blue-400 to-blue-200 bg-clip-text text-transparent mb-3 flex items-center gap-2"><svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M12 20a8 8 0 100-16 8 8 0 000 16z"/></svg> Bildirimler</h2>
        <div id="notifications-loading" class="text-center text-lg font-semibold text-slate-400 py-6 animate-pulse">
            <svg class="mx-auto mb-2 w-7 h-7 animate-spin text-blue-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
            </svg>
            Yükleniyor...
        </div>
        <ul id="notifications-list" class="space-y-4"></ul>
        
        <!-- Load More Button for Server Notifications -->
        <div class="text-center py-4">
            <button id="load-more-server-notifications" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg transition-colors hidden">
                <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
                Daha Fazla Yükle
            </button>
        </div>
    </div>
</div>