<?php
$ports = [1, 22, 23, 25, 110, 143, 1433, 6379, 27017, 123, 53, 80, 443, 3389, 3306];
?>
<div id="editFormWrapper" data-server-id="<?= htmlspecialchars($id ?? '') ?>">
  <div id="loading" class="max-w-xl mx-auto mt-16 px-6 py-10 bg-white rounded-2xl shadow-xl border border-gray-200 text-center text-gray-600 font-semibold text-xl">
    Yükleniyor...
  </div>

  <div id="editFormContainer" class="max-w-xl mx-auto mt-16 px-6 py-10 bg-white rounded-2xl shadow-xl border border-gray-200 hidden">
    <h2 class="text-3xl font-extrabold text-center text-gray-800 mb-8">
      🛠️ Sunucu Düzenle
    </h2>

    <div id="updateSuccessMsg" class="mb-6 px-4 py-3 rounded-md text-sm font-medium bg-green-100 text-green-800 border border-green-300 hidden">
      Sunucu başarıyla güncellendi.
    </div>
    <div id="updateErrorMsg" class="mb-6 px-4 py-3 rounded-md text-sm font-medium bg-red-100 text-red-800 border border-red-300 hidden">
      Sunucu güncellenemedi.
    </div>

    <form id="updateServerForm" class="space-y-6">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1" for="ip">IP Adresi</label>
        <input
          type="text"
          id="ip"
          name="ip"
          required
          class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:outline-none" />
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1" for="name">Sunucu Adı</label>
        <input
          type="text"
          id="name"
          name="name"
          required
          class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:outline-none" />
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1" for="assigned_id">Atanmış ID</label>
        <input
          type="text"
          id="assigned_id"
          name="assigned_id"
          required
          class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:outline-none" />
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1" for="location">Konum</label>
        <input
          type="text"
          id="location"
          name="location"
          required
          class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:outline-none" />
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
                  class="port-checkbox accent-blue-600 h-4 w-4 rounded border-gray-300" />
                <span class="text-sm font-medium text-gray-700"><?= $port ?></span>
              </div>
            </label>
          <?php endforeach; ?>
        </div>
      </div>


      <button
        type="submit"
        class="w-full py-3 px-6 text-white text-lg font-semibold rounded-xl shadow-lg bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600">
        Güncelle
      </button>
    </form>
  </div>
</div>