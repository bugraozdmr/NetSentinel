# NetSentinel Real-Time System

Bu dokümantasyon, NetSentinel'in yeni real-time güncelleme sistemini açıklar.

## 🚀 Yeni Özellikler

### 1. Real-Time Frontend Updates
- **Sayfa yenilenmesi yok**: Artık sayfa tamamen yenilenmiyor
- **AJAX ile güncelleme**: Sadece gerekli veriler güncelleniyor
- **5 dakikalık interval**: Daha sık güncelleme
- **Bildirim sayısı güncelleme**: Real-time bildirim sayısı

### 2. Optimized Backend Worker
- **Gelişmiş logging**: Detaylı log kayıtları
- **Error handling**: Hata yönetimi
- **Performance monitoring**: Performans takibi
- **Cron job desteği**: Cron ile çalıştırma

## 📋 Kurulum

### 1. Frontend Ayarları
`frontend/assets/js/config.js` dosyasında:

```javascript
// Real-time update interval (5 minutes)
export const REAL_TIME_INTERVAL = 300000;

// Page refresh interval (30 minutes) - only for fallback
export const PAGE_REFRESH_INTERVAL = 1800000;

// Enable real-time updates instead of page refresh
export const ENABLE_REAL_TIME_UPDATES = true;
```

### 2. Backend Worker Ayarları

#### Seçenek A: Sürekli Çalışan Worker
```bash
# Worker'ı başlat
nohup php api/app/worker/check-runner.php > check.log 2>&1 &

# Worker'ı durdur
ps aux | grep check-runner.php
kill PID
```

#### Seçenek B: Cron Job (Önerilen)
```bash
# Cron tablosunu düzenle
crontab -e

# Her 5 dakikada bir çalıştır
*/5 * * * * /Applications/XAMPP/xamppfiles/htdocs/NetSentinel/api/cron-check.sh

# Cron job'ları listele
crontab -l
```

### 3. Environment Variables
`.env` dosyasında:

```env
# Worker interval (saniye)
WORKER_INTERVAL=30

# API base URL
API_BASE_URL=http://192.168.1.34/netsentinel/api
```

## 🔧 API Endpoints

### Yeni Real-Time Endpoint
```
GET /api/realtime
```

**Response:**
```json
{
  "servers": [...],
  "notification_count": 5,
  "last_update": "2025-01-20 15:30:00",
  "update_type": "real_time"
}
```

### Mevcut Check Endpoint
```
GET /api/check
```

## 📊 Performans Karşılaştırması

### Eski Sistem
- ❌ Sayfa tamamen yenileniyor
- ❌ 50 dakikalık interval
- ❌ Yüksek kaynak tüketimi
- ❌ Kötü kullanıcı deneyimi

### Yeni Sistem
- ✅ Sadece veri güncelleniyor
- ✅ 5 dakikalık interval
- ✅ Düşük kaynak tüketimi
- ✅ Mükemmel kullanıcı deneyimi

## 🛠️ Hata Ayıklama

### Frontend Hataları
```javascript
// Browser console'da kontrol et
console.log('Performing real-time update...');
```

### Backend Hataları
```bash
# Worker loglarını kontrol et
tail -f check.log

# Cron loglarını kontrol et
tail -f api/logs/cron-check-$(date +%Y-%m-%d).log
```

### API Test
```bash
# Real-time endpoint test
curl "http://localhost/netsentinel/api/realtime"

# Check endpoint test
curl "http://localhost/netsentinel/api/check"
```

## 🔄 Geçiş Rehberi

### 1. Eski Sistemi Durdur
```bash
# Eski worker'ı durdur
ps aux | grep check-runner.php
kill PID
```

### 2. Yeni Sistemi Başlat
```bash
# Cron job ekle
crontab -e
# */5 * * * * /Applications/XAMPP/xamppfiles/htdocs/NetSentinel/api/cron-check.sh
```

### 3. Frontend'i Güncelle
- `config.js` dosyasını güncelle
- `ENABLE_REAL_TIME_UPDATES = true` yap

### 4. Test Et
- Browser'da sayfayı aç
- Console'da real-time güncellemeleri kontrol et
- Network sekmesinde AJAX isteklerini izle

## 📈 Monitoring

### Log Dosyaları
- `api/logs/cron-check-YYYY-MM-DD.log`: Cron job logları
- `check.log`: Worker logları (sürekli çalışan worker için)

### Performance Metrics
- **Response Time**: API yanıt süresi
- **Update Frequency**: Güncelleme sıklığı
- **Error Rate**: Hata oranı
- **Server Count**: Kontrol edilen sunucu sayısı

## 🚨 Troubleshooting

### Real-Time Updates Çalışmıyor
1. Browser console'u kontrol et
2. Network sekmesinde AJAX isteklerini kontrol et
3. API endpoint'inin çalıştığını doğrula
4. `ENABLE_REAL_TIME_UPDATES` ayarını kontrol et

### Worker Çalışmıyor
1. Log dosyalarını kontrol et
2. PHP path'ini doğrula
3. Cron job'ın aktif olduğunu kontrol et
4. Dosya izinlerini kontrol et

### Yüksek CPU Kullanımı
1. Worker interval'ini artır
2. Cron job sıklığını azalt
3. Sunucu sayısını kontrol et
4. Database query'lerini optimize et

## 🎯 Best Practices

1. **Cron Job Kullan**: Sürekli çalışan worker yerine cron job tercih et
2. **Log Rotation**: Log dosyalarını düzenli olarak temizle
3. **Monitoring**: Sistem performansını sürekli izle
4. **Backup**: Konfigürasyon dosyalarını yedekle
5. **Testing**: Değişiklikleri test ortamında dene

## 📞 Destek

Herhangi bir sorun yaşarsanız:
1. Log dosyalarını kontrol edin
2. Browser console'unu inceleyin
3. API endpoint'lerini test edin
4. Bu dokümantasyonu tekrar gözden geçirin 