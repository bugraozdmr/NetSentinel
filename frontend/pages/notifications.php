<div id="notifications-container" class="min-h-screen py-10 px-2 flex justify-center items-start bg-gradient-to-br from-slate-900 via-blue-950 to-slate-900">
  <div class="w-full max-w-2xl bg-slate-900/80 rounded-3xl shadow-2xl border border-blue-900/40 p-0 md:p-2">
    <h1 class="text-4xl md:text-5xl font-extrabold text-center bg-gradient-to-r from-blue-400 to-blue-200 bg-clip-text text-transparent drop-shadow-lg mb-4 flex items-center justify-center gap-3 pt-8">
      <span class="text-4xl md:text-5xl animate-bounce">🔔</span> Bildirimler
    </h1>
    <div class="flex justify-center mb-6">
      <div class="h-1 w-32 bg-gradient-to-r from-blue-500 via-blue-300 to-blue-500 rounded-full opacity-60"></div>
    </div>

    <div id="notifications-list" class="space-y-4 animate-fade-in">
      <!-- Notifications -->
      <div class="flex flex-col items-center justify-center bg-slate-800/80 rounded-2xl shadow-xl p-6 text-slate-300">
        <svg class="w-8 h-8 mb-2 text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M12 20a8 8 0 100-16 8 8 0 000 16z"/></svg>
        <span>Bildirimler yükleniyor...</span>
      </div>
    </div>

    <div class="mt-12 flex flex-col sm:flex-row justify-center items-center gap-4 pb-8">
      <button 
        id="mark-read-btn"
        class="bg-gradient-to-r from-green-600 to-green-500 text-white px-7 py-3 rounded-full shadow-lg hover:from-green-700 hover:to-green-600 hover:scale-105 transition-all text-base font-bold tracking-wide border-2 border-green-700/30"
      >
        <span class="mr-2">✔️</span> Tümünü okundu olarak işaretle
      </button>

      <a href="<?= $baseUrl ?>" class="bg-gradient-to-r from-blue-600 to-blue-400 text-white px-7 py-3 rounded-full shadow-lg hover:from-blue-700 hover:to-blue-500 hover:scale-105 transition-all text-base font-bold tracking-wide border-2 border-blue-700/30">
        <span class="mr-2">🏠</span> Ana sayfaya dön
      </a>
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
