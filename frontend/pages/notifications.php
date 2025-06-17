<div id="notifications-container" class="min-h-screen bg-gradient-to-br py-10 px-4 flex justify-center">
  <div class="w-full max-w-3xl">
    <h1 class="text-4xl font-extrabold text-center text-blue-800 drop-shadow mb-8">
      🔔 Bildirimler
    </h1>

    <div id="notifications-list" class="space-y-4">
      <!-- Notifications -->
      <div class="text-center text-gray-500 py-6">Bildirimler yükleniyor...</div>
    </div>

    <div class="mt-10 text-center space-y-4">
      <button 
        id="mark-read-btn"
        class="bg-green-600 text-white px-6 py-3 rounded-lg shadow-md hover:bg-green-700 transition text-sm"
      >
        Tümünü okundu olarak işaretle
      </button>

      <a href="<?= $baseUrl ?>" class="inline-block bg-blue-600 text-white px-6 py-3 rounded-lg shadow-md hover:bg-blue-700 transition text-sm">
        Ana sayfaya dön
      </a>
    </div>
  </div>
</div>
