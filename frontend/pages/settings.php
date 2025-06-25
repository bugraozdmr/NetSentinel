<?php
require_once __DIR__ . '/../templates/main.php';
?>

<div class="min-h-screen bg-slate-900 text-white">
    <!-- Header -->
    <div class="bg-slate-800 border-b border-slate-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div>
                    <h1 class="text-3xl font-bold text-white">Ayarlar</h1>
                    <p class="text-slate-400 mt-1">Sistem konfigürasyon ayarları</p>
                </div>
                <a href="/netsentinel/" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                    Ana Sayfa
                </a>
            </div>
        </div>
    </div>

    <!-- Settings Form -->
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-slate-800 rounded-xl shadow-xl p-8">
            <form id="settingsForm" class="space-y-6">
                <!-- API Settings -->
                <div class="border-b border-slate-700 pb-6">
                    <h2 class="text-xl font-semibold text-white mb-4">API Ayarları</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="apiBaseUrl" class="block text-sm font-medium text-slate-300 mb-2">
                                API Base URL
                            </label>
                            <input type="url" id="apiBaseUrl" name="apiBaseUrl" 
                                   class="w-full bg-slate-700 border border-slate-600 rounded-lg px-4 py-3 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   placeholder="http://192.168.1.34/netsentinel/api">
                            <p class="text-xs text-slate-400 mt-1">API endpoint'inin tam URL'i</p>
                        </div>
                        
                        <div>
                            <label for="appName" class="block text-sm font-medium text-slate-300 mb-2">
                                Uygulama Adı
                            </label>
                            <input type="text" id="appName" name="appName" 
                                   class="w-full bg-slate-700 border border-slate-600 rounded-lg px-4 py-3 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   placeholder="netsentinel">
                            <p class="text-xs text-slate-400 mt-1">Uygulamanın URL'deki adı</p>
                        </div>
                    </div>
                </div>

                <!-- Update Settings -->
                <div class="border-b border-slate-700 pb-6">
                    <h2 class="text-xl font-semibold text-white mb-4">Güncelleme Ayarları</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="updateMode" class="block text-sm font-medium text-slate-300 mb-2">
                                Güncelleme Modu
                            </label>
                            <select id="updateMode" name="updateMode" 
                                    class="w-full bg-slate-700 border border-slate-600 rounded-lg px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="page_refresh">Sayfa Yenileme</option>
                                <option value="real_time">Real-time Güncelleme</option>
                            </select>
                            <p class="text-xs text-slate-400 mt-1">Veri güncelleme yöntemi</p>
                        </div>
                        
                        <div>
                            <label for="updateInterval" class="block text-sm font-medium text-slate-300 mb-2">
                                Güncelleme Aralığı (saniye)
                            </label>
                            <input type="number" id="updateInterval" name="updateInterval" min="5" max="3600"
                                   class="w-full bg-slate-700 border border-slate-600 rounded-lg px-4 py-3 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   placeholder="300">
                            <p class="text-xs text-slate-400 mt-1">5-3600 saniye arası</p>
                        </div>
                    </div>
                </div>

                <!-- Advanced Settings -->
                <div class="border-b border-slate-700 pb-6">
                    <h2 class="text-xl font-semibold text-white mb-4">Gelişmiş Ayarlar</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="timezone" class="block text-sm font-medium text-slate-300 mb-2">
                                Zaman Dilimi
                            </label>
                            <select id="timezone" name="timezone" 
                                    class="w-full bg-slate-700 border border-slate-600 rounded-lg px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="Europe/Istanbul">İstanbul (UTC+3)</option>
                                <option value="UTC">UTC</option>
                                <option value="Europe/London">Londra (UTC+0)</option>
                                <option value="America/New_York">New York (UTC-5)</option>
                            </select>
                        </div>
                        
                        <div>
                            <label for="language" class="block text-sm font-medium text-slate-300 mb-2">
                                Dil
                            </label>
                            <select id="language" name="language" 
                                    class="w-full bg-slate-700 border border-slate-600 rounded-lg px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="tr">Türkçe</option>
                                <option value="en">English</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-end space-x-4 pt-6">
                    <button type="button" id="resetBtn" 
                            class="bg-slate-600 hover:bg-slate-700 text-white px-6 py-3 rounded-lg transition-colors">
                        Varsayılana Döndür
                    </button>
                    <button type="submit" 
                            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg transition-colors">
                        Ayarları Kaydet
                    </button>
                </div>
            </form>
        </div>

        <!-- Settings Info -->
        <div class="mt-8 bg-slate-800 rounded-xl shadow-xl p-6">
            <h3 class="text-lg font-semibold text-white mb-4">Ayarlar Hakkında</h3>
            <div class="text-sm text-slate-400 space-y-2">
                <p><strong>API Base URL:</strong> Backend API'nin çalıştığı adres</p>
                <p><strong>Uygulama Adı:</strong> URL'de kullanılan uygulama adı</p>
                <p><strong>Güncelleme Modu:</strong> Sayfa yenileme veya AJAX ile güncelleme</p>
                <p><strong>Güncelleme Aralığı:</strong> Verilerin ne sıklıkla güncelleneceği</p>
                <p><strong>Zaman Dilimi:</strong> Tarih ve saat gösterim formatı</p>
                <p><strong>Dil:</strong> Arayüz dili</p>
            </div>
        </div>
    </div>
</div>

<script type="module" src="/netsentinel/assets/js/settings.js"></script> 