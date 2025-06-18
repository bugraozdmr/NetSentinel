<div id="notifications-container" class="min-h-screen py-10 px-2 flex justify-center items-start">
  <div class="w-full max-w-2xl">
    <h1 class="text-3xl md:text-4xl font-extrabold text-center bg-gradient-to-r from-blue-400 to-blue-200 bg-clip-text text-transparent drop-shadow mb-8 flex items-center justify-center gap-2">
      <span class="text-2xl md:text-3xl">🔔</span> Bildirimler
    </h1>

    <div id="notifications-list" class="space-y-4">
      <!-- Notifications -->
      <div class="flex flex-col items-center justify-center bg-slate-800/80 rounded-2xl shadow-xl p-6 text-slate-300">
        <svg class="w-8 h-8 mb-2 text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M12 20a8 8 0 100-16 8 8 0 000 16z"/></svg>
        <span>Bildirimler yükleniyor...</span>
      </div>
    </div>

    <div class="mt-10 flex flex-col sm:flex-row justify-center items-center gap-4">
      <button 
        id="mark-read-btn"
        class="bg-gradient-to-r from-green-600 to-green-500 text-white px-6 py-3 rounded-full shadow hover:from-green-700 hover:to-green-600 transition text-sm font-semibold"
      >
        Tümünü okundu olarak işaretle
      </button>

      <a href="<?= $baseUrl ?>" class="bg-gradient-to-r from-blue-600 to-blue-400 text-white px-6 py-3 rounded-full shadow hover:from-blue-700 hover:to-blue-500 transition text-sm font-semibold">
        Ana sayfaya dön
      </a>
    </div>
  </div>
</div>
