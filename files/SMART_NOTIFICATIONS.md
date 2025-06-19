# Akıllı Bildirim Sistemi

NetSentinel'in yeni akıllı bildirim sistemi, sürekli kapalı olan sunucular için spam bildirimlerini önler ve kullanıcı deneyimini iyileştirir.

## 🎯 Amaç

Eski sistemde, bir sunucu kapandığında her kontrol döngüsünde yeni bir bildirim oluşturuluyordu. Bu durum:
- Kullanıcı deneyimini kötüleştiriyordu
- Gereksiz bildirim spam'i yaratıyordu
- Önemli bildirimlerin gözden kaçmasına neden oluyordu

## 🚀 Yeni Sistem Nasıl Çalışır?

### Bildirim Türleri

1. **status_change** - Normal durum değişiklikleri (yeşil)
2. **first_down** - İlk düşüş bildirimi (kırmızı)
3. **repeated_down** - Tekrar düşüş bildirimi (turuncu)
4. **long_term_down** - Uzun süreli düşüş bildirimi (kırmızı)

### Zaman Stratejisi

| Durum | Süre | Bildirim Türü | Açıklama |
|-------|------|---------------|----------|
| İlk düşüş | Anında | `first_down` | Sunucu ilk kez kapandığında |
| 30 dakika | Bekleme | - | Bildirim gönderilmez |
| 30 dakika - 2 saat | Her 30 dakikada | `repeated_down` | "Hala kapalı" bildirimi |
| 2+ saat | Her 2 saatte | `long_term_down` | "X saattir kapalı" bildirimi |
| 24+ saat | Her 6 saatte | `long_term_down` | Acil müdahale bildirimi |

### Sunucu Açıldığında

Sunucu tekrar açıldığında:
- Notification state sıfırlanır
- Normal "açıldı" bildirimi gönderilir
- Gelecek düşüşler için sayaç yeniden başlar

## 📊 Veritabanı Yapısı

### notifications tablosu (güncellendi)
```sql
ALTER TABLE notifications 
ADD COLUMN notification_type ENUM('status_change', 'first_down', 'repeated_down', 'long_term_down') DEFAULT 'status_change',
ADD COLUMN down_count INT DEFAULT 0;
```

### server_notification_states tablosu (yeni)
```sql
CREATE TABLE server_notification_states (
    id INT AUTO_INCREMENT PRIMARY KEY,
    server_id INT NOT NULL,
    last_down_notification_at TIMESTAMP NULL,
    consecutive_down_count INT DEFAULT 0,
    last_notification_type ENUM('status_change', 'first_down', 'repeated_down', 'long_term_down') DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (server_id) REFERENCES servers(id) ON DELETE CASCADE,
    UNIQUE KEY server_notification_state_unique (server_id)
);
```

## 🎨 Frontend Görünümü

### Bildirim Türlerine Göre Renkler
- **status_change**: Yeşil (✅) - Sunucu açıldı/kapandı
- **first_down**: Kırmızı (⚠️) - İlk düşüş
- **repeated_down**: Turuncu (🔄) - Tekrar düşüş
- **long_term_down**: Kırmızı (🚨) - Uzun süreli düşüş

### Badge'ler
Her bildirim türü için özel badge'ler gösterilir:
- İlk Düşüş
- Tekrar Düşüş  
- Uzun Süreli Düşüş
- Durum Değişikliği

## 🔧 Teknik Detaylar

### NotificationService Sınıfı

```php
// Akıllı bildirim işleme
public function processSmartNotification(array $server, int $previousStatus, int $newStatus): void

// Sunucu kapandığında akıllı bildirim
private function processDownNotification(int $serverId, string $serverName): void

// Bildirim türlerine göre metodlar
private function createFirstDownNotification(int $serverId, string $serverName): void
private function createRepeatedDownNotification(int $serverId, string $serverName, int $count): void
private function createLongTermDownNotification(int $serverId, string $serverName, float $hoursDown): void
```

### Zaman Hesaplama
```php
$timeDiff = $lastNotificationTime ? 
    (strtotime($now) - strtotime($lastNotificationTime)) / 60 : 
    PHP_INT_MAX;
```

## 📈 Faydalar

1. **Spam Önleme**: Gereksiz bildirimler engellenir
2. **Önemli Bildirimler**: Kritik durumlar vurgulanır
3. **Kullanıcı Deneyimi**: Daha temiz bildirim akışı
4. **Acil Durum Farkındalığı**: Uzun süreli düşüşler özel olarak işaretlenir
5. **Otomatik Sıfırlama**: Sunucu açıldığında sistem otomatik sıfırlanır

## 🧪 Test Senaryoları

1. **İlk Düşüş**: Sunucu kapandığında anında `first_down` bildirimi
2. **Tekrar Kontrol**: 30 dakika sonra `repeated_down` bildirimi
3. **Uzun Süreli**: 2+ saat sonra `long_term_down` bildirimi
4. **Sunucu Açılma**: Sunucu açıldığında state sıfırlanır
5. **Yeni Düşüş**: Sıfırlanmış state ile yeni döngü başlar

## 🔄 Migration

Sistemi aktif etmek için:
```bash
php api/app/migrations/19_06_25_update_notifications.php
```

Bu migration:
- notifications tablosuna yeni alanlar ekler
- server_notification_states tablosunu oluşturur
- Mevcut bildirimleri etkilemez

## 📝 Notlar

- Sistem geriye uyumludur
- Mevcut bildirimler korunur
- Eski bildirimler `status_change` türünde görünür
- Yeni sistem otomatik olarak devreye girer 