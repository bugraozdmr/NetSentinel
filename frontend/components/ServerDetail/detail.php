    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-slate-800 rounded-xl p-6">
            <h3 class="text-lg font-semibold text-blue-300 mb-4">Sunucu Bilgileri</h3>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-400">IP Adresi</label>
                    <p class="mt-1 text-slate-200"><?php echo htmlspecialchars($server['ip']); ?></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-400">Sunucu Adı</label>
                    <p class="mt-1 text-slate-200"><?php echo htmlspecialchars($server['name']); ?></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-400">Lokasyon</label>
                    <p class="mt-1 text-slate-200"><?php echo htmlspecialchars($server['location']); ?></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-400">Panel</label>
                    <p class="mt-1 text-slate-200"><?php echo htmlspecialchars($server['panel']); ?></p>
                </div>
            </div>
        </div>
    </div> 