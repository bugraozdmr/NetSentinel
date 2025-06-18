<?php
include __DIR__ . '/../../config/ports.php';
?>

<div class="max-w-xl mx-auto mt-16 mb-16 px-6 sm:px-8 py-10 bg-gradient-to-br from-slate-900 to-slate-800 rounded-2xl shadow-2xl border border-slate-700 transition-all">
    <h2 class="text-3xl font-extrabold text-center text-blue-200 mb-8">
        🚀 Sunucu Ekle
    </h2>

    <div id="successMsg" class="hidden mb-6 px-4 py-3 rounded-md text-sm font-medium bg-green-900 text-green-200 border border-green-700">
        Sunucu başarıyla eklendi!
    </div>

    <div id="errorMsg" class="hidden mb-6 px-4 py-3 rounded-md text-sm font-medium bg-red-900 text-red-200 border border-red-700">
    </div>

    <form id="addServerForm" class="space-y-6">
        <div>
            <label for="ip" class="block text-sm font-medium text-slate-300 mb-1">IP Adresi</label>
            <input
                type="text"
                id="ip"
                name="ip"
                placeholder="192.168.1.1"
                class="w-full px-4 py-2 bg-slate-900 border border-slate-700 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:outline-none transition duration-200 placeholder-slate-400 text-slate-100"
                required />
        </div>

        <div>
            <label for="name" class="block text-sm font-medium text-slate-300 mb-1">Sunucu Adı</label>
            <input
                type="text"
                id="name"
                name="name"
                placeholder="Ubuntu VM"
                class="w-full px-4 py-2 bg-slate-900 border border-slate-700 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:outline-none transition duration-200 placeholder-slate-400 text-slate-100"
                required />
        </div>

        <div>
            <label for="assigned_id" class="block text-sm font-medium text-slate-300 mb-1">Atanmış ID</label>
            <input
                type="text"
                id="assigned_id"
                name="assigned_id"
                placeholder="ubuntuvm"
                class="w-full px-4 py-2 bg-slate-900 border border-slate-700 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:outline-none transition duration-200 placeholder-slate-400 text-slate-100"
                required />
        </div>

        <div>
            <label for="location" class="block text-sm font-medium text-slate-300 mb-1">Konum</label>
            <select
                id="location"
                name="location"
                class="w-full px-4 py-2 bg-slate-900 border border-slate-700 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:outline-none transition duration-200 text-slate-100"
                required>
                <option value="" disabled selected>Bir konum seçin</option>
                <option value="mars">Mars</option>
                <option value="hetzner">Hetzner</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-semibold text-blue-200 mb-3">Port Seçimi</label>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                <?php foreach ($ports as $port): ?>
                    <label class="group cursor-pointer transition-transform transform hover:scale-[1.02]">
                        <div class="flex items-center gap-2 px-4 py-2 rounded-xl border border-slate-700 bg-slate-900 group-hover:bg-blue-900 shadow-sm transition-all duration-200">
                            <input
                                type="checkbox"
                                name="ports[]"
                                value="<?= $port ?>"
                                checked
                                class="accent-blue-500 h-4 w-4 rounded border-slate-700 bg-slate-800" />
                            <span class="text-sm font-medium text-slate-200"><?= $port ?></span>
                        </div>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>

        <button
            type="submit"
            class="w-full py-3 px-6 text-white text-lg font-semibold rounded-xl shadow-lg transition-all duration-300 bg-gradient-to-r from-blue-600 to-blue-400 hover:from-blue-700 hover:to-blue-500">
            + Sunucu Ekle
        </button>
    </form>
</div>