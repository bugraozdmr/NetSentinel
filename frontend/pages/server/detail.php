<div id="loading-detail" class="text-center text-lg font-semibold text-slate-600 py-6 animate-pulse">
    <svg class="mx-auto mb-2 w-6 h-6 animate-spin text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
    </svg>
    Yükleniyor...
</div>


<div id="serverDetail" class="max-w-5xl mx-auto bg-white shadow-2xl rounded-3xl p-8 space-y-10 border border-slate-200">
    <div class="mb-6 text-center">
        <h1 class="text-4xl font-extrabold text-slate-800 tracking-tight">🌐 Sunucu Detayı</h1>
        <p class="text-slate-500 text-lg mt-2">Sunucu hakkında ayrıntılı bilgiler</p>
    </div>


    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
        <div class="bg-slate-50 p-4 rounded-xl shadow-inner border">
            <p class="text-sm text-slate-500">İsim</p>
            <p id="name" class="text-lg font-semibold text-slate-800 mt-1"></p>
        </div>

        <div class="bg-slate-50 p-4 rounded-xl shadow-inner border">
            <p class="text-sm text-slate-500">IP Adresi</p>
            <p id="ip" class="text-lg font-semibold text-slate-800 mt-1"></p>
        </div>
        <div class="bg-slate-50 p-4 rounded-xl shadow-inner border">
            <p class="text-sm text-slate-500">Konum</p>
            <p id="location" class="text-lg font-semibold text-slate-800 mt-1"></p>
        </div>
        <div class="bg-slate-50 p-4 rounded-xl shadow-inner border">
            <p class="text-sm text-slate-500">Atanmış ID</p>
            <p id="assigned_id" class="text-lg font-semibold text-slate-800 mt-1"></p>
        </div>
        <div class="bg-slate-50 p-4 rounded-xl shadow-inner border">
            <p class="text-sm text-slate-500">Durum</p>
            <p id="is_active" class="text-lg font-semibold text-slate-800 mt-1"></p>
        </div>
        <div class="bg-slate-50 p-4 rounded-xl shadow-inner border">
            <p class="text-sm text-slate-500">Son Kontrol</p>
            <p id="last_check_at" class="text-lg font-semibold text-slate-800 mt-1"></p>
        </div>
    </div>

    <div class="bg-slate-50 p-4 rounded-2xl shadow-inner border border-slate-200">
        <h2 class="text-xl font-semibold text-slate-800 mb-3">Son Kontroller</h2>
        <div id="checkList" class="flex flex-wrap gap-2"></div>
    </div>


    <div class="bg-slate-50 text-slate-800 rounded-2xl px-4 py-2 shadow-md border border-slate-200">
        <h2 class="text-xl font-semibold text-slate-800 mb-3">Port Durumu</h2>
        <div id="ports" class="grid grid-cols-2 sm:grid-cols-4 gap-4 justify-items-center"></div>
    </div>


    <div id="chartContainer" class="bg-slate-50 p-4 rounded-2xl shadow-inner border border-slate-200 max-w-xl mx-auto">
        <h2 class="text-xl font-semibold text-slate-800 mb-3">Ping Süreleri (ms)</h2>
        <canvas id="pingChart" style="max-height: 300px;"></canvas>
    </div>



</div>