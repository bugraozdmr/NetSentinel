<?php
include __DIR__ . '/../../config/data.php';
?>
<div id="editFormWrapper" data-server-id="<?= htmlspecialchars($id ?? '') ?>">
  <div id="loading" class="max-w-xl mx-auto mt-16 px-6 py-10 bg-gradient-to-br from-slate-900 to-slate-800 rounded-2xl shadow-2xl border border-slate-700 text-center text-blue-200 font-semibold text-xl">
    Yükleniyor...
  </div>

  <div id="editFormContainer" class="max-w-xl mx-auto mt-16 mb-16 px-6 py-10 bg-gradient-to-br from-slate-900 to-slate-800 rounded-2xl shadow-2xl border border-slate-700 hidden">
    <h2 class="text-3xl font-extrabold text-center text-blue-200 mb-8">
      🛠️ Sunucu Düzenle
    </h2>

    <div id="updateSuccessMsg" class="mb-6 px-4 py-3 rounded-md text-sm font-medium bg-green-900 text-green-200 border border-green-700 hidden">
      Sunucu başarıyla güncellendi.
    </div>
    <div id="updateErrorMsg" class="mb-6 px-4 py-3 rounded-md text-sm font-medium bg-red-900 text-red-200 border border-red-700 hidden">
      Sunucu güncellenemedi.
    </div>

    <form id="updateServerForm" class="space-y-6">
      <div>
        <label class="block text-sm font-medium text-slate-300 mb-1" for="ip">IP Adresi</label>
        <input
          type="text"
          id="ip"
          name="ip"
          required
          class="w-full px-4 py-2 bg-slate-900 border border-slate-700 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:outline-none placeholder-slate-400 text-slate-100" />
      </div>

      <div>
        <label class="block text-sm font-medium text-slate-300 mb-1" for="name">Sunucu Adı</label>
        <input
          type="text"
          id="name"
          name="name"
          required
          class="w-full px-4 py-2 bg-slate-900 border border-slate-700 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:outline-none placeholder-slate-400 text-slate-100" />
      </div>

      <div>
        <label class="block text-sm font-medium text-slate-300 mb-1" for="locationUpdate">Konum</label>
        <select
          id="locationUpdate"
          name="location"
          class="w-full px-4 py-2 bg-slate-900 border border-slate-700 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:outline-none transition duration-200 text-slate-100"
          required>
          <option value="" disabled selected>Bir konum seçin</option>
          <option value="mars">Mars</option>
          <option value="hetzner">Hetzner</option>
        </select>
      </div>

      <div>
        <label for="panel" class="block text-sm font-medium text-slate-300 mb-1">Panel</label>
        <select
          id="panel"
          name="panel"
          class="w-full px-4 py-2 bg-slate-900 border border-slate-700 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:outline-none transition duration-200 text-slate-100"
          required>
          <option value="" disabled selected>Bir panel seçin</option>
          <?php foreach ($panels as $panel): ?>
            <option value="<?= htmlspecialchars($panel) ?>"><?= htmlspecialchars($panel) ?></option>
          <?php endforeach; ?>
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
                  class="port-checkbox accent-blue-500 h-4 w-4 rounded border-slate-700 bg-slate-800" />
                <span class="text-sm font-medium text-slate-200"><?= $port ?></span>
              </div>
            </label>
          <?php endforeach; ?>
        </div>
      </div>

      <button
        type="submit"
        class="w-full py-3 px-6 text-white text-lg font-semibold rounded-xl shadow-lg bg-gradient-to-r from-blue-600 to-blue-400 hover:from-blue-700 hover:to-blue-500 transition-all duration-300">
        Güncelle
      </button>
    </form>
  </div>
</div>