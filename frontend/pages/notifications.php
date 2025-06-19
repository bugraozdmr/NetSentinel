<div id="notifications-container" class="min-h-screen py-10 px-2 flex justify-center items-start bg-gradient-to-br from-slate-900 via-blue-950 to-slate-900">
  <div class="w-full max-w-4xl bg-slate-900/80 rounded-3xl shadow-2xl border border-blue-900/40 p-0 md:p-2">
    <h1 class="text-4xl md:text-5xl font-extrabold text-center bg-gradient-to-r from-blue-400 to-blue-200 bg-clip-text text-transparent drop-shadow-lg mb-4 flex items-center justify-center gap-3 pt-8">
      <span class="text-4xl md:text-5xl animate-bounce">🔔</span> Bildirimler
    </h1>
    <div class="flex justify-center mb-6">
      <div class="h-1 w-32 bg-gradient-to-r from-blue-500 via-blue-300 to-blue-500 rounded-full opacity-60"></div>
    </div>

    <!-- Action Buttons -->
    <div class="flex flex-wrap justify-center gap-3 mb-6 px-4">
      <button id="mark-read-btn" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors flex items-center gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
        </svg>
        Tümünü Okundu İşaretle
      </button>
      <button onclick="showDeleteOldModal()" class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg transition-colors flex items-center gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
        </svg>
        Eski Bildirimleri Sil
      </button>
      <button onclick="showDeleteAllModal()" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition-colors flex items-center gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
        </svg>
        Tümünü Sil
      </button>
    </div>

    <div id="notifications-list" class="space-y-6 animate-fade-in px-4 pb-4">
      <!-- Notifications -->
      <div class="flex flex-col items-center justify-center bg-slate-800/80 rounded-2xl shadow-xl p-6 text-slate-300">
        <svg class="w-8 h-8 mb-2 text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M12 20a8 8 0 100-16 8 8 0 000 16z"/></svg>
        <span>Bildirimler yükleniyor...</span>
      </div>
    </div>

    <!-- Load More Button -->
    <div class="text-center py-6 px-4">
      <button id="load-more-notifications" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg transition-colors hidden">
        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
        </svg>
        Daha Fazla Yükle
      </button>
    </div>

    <!-- Loading Indicator -->
    <div id="notifications-loading" class="text-center text-lg font-semibold text-slate-400 py-6 animate-pulse hidden">
      <svg class="mx-auto mb-2 w-7 h-7 animate-spin text-blue-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
      </svg>
      Yükleniyor...
    </div>
  </div>
</div>

<!-- Delete Old Notifications Modal -->
<div id="deleteOldModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
  <div class="bg-slate-800 rounded-2xl p-8 max-w-md w-full mx-4 shadow-2xl border border-slate-700">
    <div class="text-center">
      <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-orange-100 mb-4">
        <svg class="h-6 w-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
        </svg>
      </div>
      <h3 class="text-lg font-medium text-slate-200 mb-2">Eski Bildirimleri Sil</h3>
      <p class="text-sm text-slate-400 mb-4">
        Hangi zaman aralığından eski bildirimleri silmek istiyorsunuz?
      </p>
      
      <!-- Zaman Seçimi -->
      <div class="mb-6">
        <label class="block text-sm font-medium text-slate-300 mb-2">Zaman Aralığı:</label>
        <select id="oldNotificationsDays" class="w-full bg-slate-700 border border-slate-600 text-slate-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
          <option value="7">7 günden eski</option>
          <option value="15">15 günden eski</option>
          <option value="30" selected>30 günden eski</option>
          <option value="60">60 günden eski</option>
          <option value="90">90 günden eski</option>
          <option value="180">6 aydan eski</option>
          <option value="365">1 yıldan eski</option>
        </select>
      </div>
      
      <div class="flex justify-center space-x-3">
        <button onclick="hideDeleteOldModal()" class="px-4 py-2 text-sm font-medium text-slate-300 bg-slate-700 hover:bg-slate-600 rounded-lg transition-colors">
          İptal
        </button>
        <button onclick="deleteOldNotifications()" class="px-4 py-2 text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 rounded-lg transition-colors">
          Sil
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Delete All Notifications Modal -->
<div id="deleteAllModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
  <div class="bg-slate-800 rounded-2xl p-8 max-w-md w-full mx-4 shadow-2xl border border-slate-700">
    <div class="text-center">
      <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
        <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
        </svg>
      </div>
      <h3 class="text-lg font-medium text-slate-200 mb-2">Tüm Bildirimleri Sil</h3>
      <p class="text-sm text-slate-400 mb-6">
        Tüm bildirimleri silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.
      </p>
      <div class="flex justify-center space-x-3">
        <button onclick="hideDeleteAllModal()" class="px-4 py-2 text-sm font-medium text-slate-300 bg-slate-700 hover:bg-slate-600 rounded-lg transition-colors">
          İptal
        </button>
        <button onclick="deleteAllNotifications()" class="px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-lg transition-colors">
          Sil
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Success Toast Notification -->
<div id="successToast" class="fixed top-4 right-4 bg-green-600 text-white px-6 py-3 rounded-lg shadow-lg transform translate-x-full transition-transform duration-300 z-50 max-w-sm hidden">
  <div class="flex items-center">
    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
    </svg>
    <span id="successToastMessage">İşlem başarılı!</span>
  </div>
</div>

<!-- Error Toast Notification -->
<div id="errorToast" class="fixed top-4 right-4 bg-red-600 text-white px-6 py-3 rounded-lg shadow-lg transform translate-x-full transition-transform duration-300 z-50 max-w-sm hidden">
  <div class="flex items-center">
    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
    </svg>
    <span id="errorToastMessage">Bir hata oluştu!</span>
  </div>
</div>

<!-- Delete Single Notification Modal -->
<div id="deleteSingleModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
  <div class="bg-slate-800 rounded-2xl p-8 max-w-md w-full mx-4 shadow-2xl border border-slate-700">
    <div class="text-center">
      <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
        <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
        </svg>
      </div>
      <h3 class="text-lg font-medium text-slate-200 mb-2">Bildirim Sil</h3>
      <p class="text-sm text-slate-400 mb-6">
        Bu bildirimi silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.
      </p>
      <div class="flex justify-center space-x-3">
        <button onclick="hideDeleteSingleModal()" class="px-4 py-2 text-sm font-medium text-slate-300 bg-slate-700 hover:bg-slate-600 rounded-lg transition-colors">
          İptal
        </button>
        <button onclick="confirmDeleteSingle()" class="px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-lg transition-colors">
          Sil
        </button>
      </div>
    </div>
  </div>
</div>

<style>
@keyframes fade-in {
  from { opacity: 0; transform: translateY(16px); }
  to { opacity: 1; transform: none; }
}
.animate-fade-in { animation: fade-in 0.7s cubic-bezier(.4,0,.2,1) both; }
</style>
