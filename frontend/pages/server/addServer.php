<?php
include __DIR__ . '/../../config/ports.php';
?>

<div class="max-w-xl mx-auto mt-16 px-6 sm:px-8 py-10 bg-white rounded-2xl shadow-xl border border-gray-200 transition-all">
    <h2 class="text-3xl font-extrabold text-center text-gray-800 mb-8">
        🚀 Sunucu Ekle
    </h2>

    <div id="successMsg" class="hidden mb-6 px-4 py-3 rounded-md text-sm font-medium bg-green-100 text-green-800 border border-green-300">
        Sunucu başarıyla eklendi!
    </div>

    <div id="errorMsg" class="hidden mb-6 px-4 py-3 rounded-md text-sm font-medium bg-red-100 text-red-800 border border-red-300">
    </div>


    <form id="addServerForm" class="space-y-6">
        <div>
            <label for="ip" class="block text-sm font-medium text-gray-700 mb-1">IP Adresi</label>
            <input
                type="text"
                id="ip"
                name="ip"
                placeholder="192.168.1.1"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:outline-none transition duration-200 placeholder-gray-400"
                required />
        </div>

        <div>
            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Sunucu Adı</label>
            <input
                type="text"
                id="name"
                name="name"
                placeholder="Ubuntu VM"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:outline-none transition duration-200 placeholder-gray-400"
                required />
        </div>

        <div>
            <label for="assigned_id" class="block text-sm font-medium text-gray-700 mb-1">Atanmış ID</label>
            <input
                type="text"
                id="assigned_id"
                name="assigned_id"
                placeholder="ubuntuvm"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:outline-none transition duration-200 placeholder-gray-400"
                required />
        </div>

        <div>
            <label for="location" class="block text-sm font-medium text-gray-700 mb-1">Konum</label>
            <input
                type="text"
                id="location"
                name="location"
                placeholder="İstanbul, TR"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:outline-none transition duration-200 placeholder-gray-400"
                required />
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-800 mb-3">Port Seçimi</label>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                <?php foreach ($ports as $port): ?>
                    <label class="group cursor-pointer transition-transform transform hover:scale-[1.02]">
                        <div class="flex items-center gap-2 px-4 py-2 rounded-xl border border-gray-300 bg-gray-50 group-hover:bg-blue-50 shadow-sm transition-all duration-200">
                            <input
                                type="checkbox"
                                name="ports[]"
                                value="<?= $port ?>"
                                checked
                                class="accent-blue-600 h-4 w-4 rounded border-gray-300" />
                            <span class="text-sm font-medium text-gray-700"><?= $port ?></span>
                        </div>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>


        <button
            type="submit"
            class="w-full py-3 px-6 text-white text-lg font-semibold rounded-xl shadow-lg transition-all duration-300 bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600">
            + Sunucu Ekle
        </button>
    </form>
</div>