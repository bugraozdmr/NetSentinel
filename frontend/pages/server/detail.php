<div id="loading" class="text-center text-lg font-semibold text-slate-700">🔄 Yükleniyor...</div>

<div id="serverDetail" class="max-w-4xl mx-auto bg-white shadow-lg rounded-2xl p-6 hidden">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-slate-800">Sunucu Detayı</h1>
        <p class="text-slate-500">Sunucu hakkında ayrıntılı bilgiler</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <div>
            <p class="text-sm text-slate-500">İsim</p>
            <p id="name" class="text-lg font-medium text-slate-800"></p>
        </div>
        <div>
            <p class="text-sm text-slate-500">IP Adresi</p>
            <p id="ip" class="text-lg font-medium text-slate-800"></p>
        </div>
        <div>
            <p class="text-sm text-slate-500">Konum</p>
            <p id="location" class="text-lg font-medium text-slate-800"></p>
        </div>
        <div>
            <p class="text-sm text-slate-500">Atanmış ID</p>
            <p id="assigned_id" class="text-lg font-medium text-slate-800"></p>
        </div>
        <div>
            <p class="text-sm text-slate-500">Durum</p>
            <p id="is_active" class="text-lg font-medium"></p>
        </div>
        <div>
            <p class="text-sm text-slate-500">Son Kontrol</p>
            <p id="last_check_at" class="text-lg font-medium text-slate-800"></p>
        </div>
    </div>

    <div class="mb-8">
        <h2 class="text-xl font-semibold text-slate-800 mb-3">Son Kontroller</h2>
        <div id="checkList" class="flex flex-wrap gap-2"></div>
    </div>

    <div>
        <h2 class="text-xl font-semibold text-slate-800 mb-3">Port Durumu</h2>
        <div id="ports" class="grid grid-cols-2 sm:grid-cols-4 gap-4"></div>
    </div>
</div>