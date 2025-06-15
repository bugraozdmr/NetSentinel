<div class="max-w-6xl mx-auto mt-8 p-8 bg-white shadow-lg rounded-lg">
    <div class="flex flex-col sm:flex-row justify-between items-center mb-4">
        <div>
            <h2 class="text-2xl font-semibold text-center sm:text-left mb-1">Sunucu Durumları</h2>
            <p class="text-slate-500 text-sm">Sunucu bilgilerini filtreleyin.</p>
        </div>
        <div class="mt-3 sm:mt-0 w-full sm:w-auto max-w-xs relative">
            <input
                type="text"
                id="searchInput"
                class="w-full pl-3 pr-10 h-10 text-sm border border-slate-300 rounded-md placeholder:text-slate-400 text-slate-700 focus:outline-none focus:ring-1 focus:ring-slate-400 focus:border-slate-400 transition-shadow shadow-sm"
                placeholder="Ara" />
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 50 50"
                class="w-5 h-5 text-slate-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none">
                <path d="M 21 3 C 11.601563 3 4 10.601563 4 20 C 4 29.398438 11.601563 37 21 37 C 24.355469 37 27.460938 36.015625 30.09375 34.34375 L 42.375 46.625 L 46.625 42.375 L 34.5 30.28125 C 36.679688 27.421875 38 23.878906 38 20 C 38 10.601563 30.398438 3 21 3 Z M 21 7 C 28.199219 7 34 12.800781 34 20 C 34 27.199219 28.199219 33 21 33 C 13.800781 33 8 27.199219 8 20 C 8 12.800781 13.800781 7 21 7 Z"></path>
            </svg>
        </div>
    </div>

    <!-- Tablo -->
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse min-w-[600px]">
            <thead class="bg-slate-100">
                <tr>
                    <th class="p-4 text-left text-sm font-semibold text-slate-600">IP</th>
                    <th class="p-4 text-left text-sm font-semibold text-slate-600">Ad</th>
                    <th class="p-4 text-left text-sm font-semibold text-slate-600">ID</th>
                    <th class="p-4 text-left text-sm font-semibold text-slate-600">Konum</th>
                    <th class="p-4 text-left text-sm font-semibold text-slate-600">Durum</th>
                    <th class="p-4 text-left text-sm font-semibold text-slate-600">Son Kontrol</th>
                    <th class="p-4 text-left text-sm font-semibold text-slate-600">Son Kontroller</th>
                    <th class="p-4 text-left text-sm font-semibold text-slate-600">İşlemler</th>
                </tr>
            </thead>
            <tbody id="serverTableBody">
                <!-- Dinamik -->
            </tbody>
        </table>

        <div id="loading" class="flex justify-center my-6 hidden">
            <div class="animate-spin rounded-full h-10 w-10 border-t-4 border-blue-500"></div>
        </div>

        <div id="error" class="text-center text-red-600 hidden mt-4">
            Sunucular alınırken bir hata oluştu.
        </div>
    </div>

    <!-- Modal -->
    <div id="deleteModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-gray-500/75">
        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex size-12 shrink-0 items-center justify-center rounded-full bg-red-100 sm:mx-0 sm:size-10">
                                <svg class="size-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-base font-semibold text-gray-900" id="dialog-title">Sunucuyu silmek istediğinize emin misiniz?</h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">Bu işlem geri alınamaz. Lütfen emin olun.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                        <button type="button" id="confirmDelete" class="inline-flex w-full justify-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-xs hover:bg-red-500 sm:ml-3 sm:w-auto">Sil</button>
                        <button type="button" id="cancelDelete" class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-xs ring-1 ring-gray-300 ring-inset hover:bg-gray-50 sm:mt-0 sm:w-auto">Vazgeç</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>