#!/bin/bash

# NetSentinel - API üzerinden bildirim üretme scripti
# Bu script veritabanına doğrudan bağlanmaz, sadece API kullanır

API_BASE_URL="http://localhost/netsentinel/api"
NOTIFICATION_COUNT=80

echo "🚀 NetSentinel Bildirim Üretme Scripti Başlatılıyor..."
echo "📊 $NOTIFICATION_COUNT adet bildirim üretilecek"
echo "🌐 API URL: $API_BASE_URL"
echo ""

# Sunucu listesini API'den çek
echo "📡 Sunucu listesi alınıyor..."
SERVERS_RESPONSE=$(curl -s "$API_BASE_URL/servers")

if [ $? -ne 0 ]; then
    echo "❌ Sunucu listesi alınamadı!"
    exit 1
fi

# Sadece aktif sunucuların ID'lerini al
SERVER_IDS=$(echo "$SERVERS_RESPONSE" | grep -o '{[^}]*}' | grep '"is_active":1' | grep -o '"id":[0-9]*' | sed 's/"id"://g')

if [ -z "$SERVER_IDS" ]; then
    echo "❌ Aktif sunucu ID'leri alınamadı!"
    echo "API yanıtı: $SERVERS_RESPONSE"
    exit 1
fi

echo "✅ $(echo "$SERVER_IDS" | wc -l) adet aktif sunucu bulundu"

# Bildirim türleri
NOTIFICATION_TYPES=("server_down" "server_up" "port_closed" "port_opened" "high_latency" "low_disk_space" "backup_failed" "ssl_expiring")

# Önem seviyeleri
PRIORITIES=("low" "medium" "high" "critical")

# Bildirim mesajları
MESSAGES=(
    "Sunucu yanıt vermiyor"
    "Sunucu tekrar aktif"
    "Port kapatıldı"
    "Port açıldı"
    "Yüksek gecikme tespit edildi"
    "Disk alanı kritik seviyede"
    "Yedekleme başarısız"
    "SSL sertifikası yakında sona erecek"
    "Ağ bağlantısı kesildi"
    "CPU kullanımı yüksek"
    "RAM kullanımı kritik"
    "Servis yeniden başlatıldı"
    "Güvenlik uyarısı"
    "Performans düşüşü"
    "Sistem güncellemesi gerekli"
)

echo ""
echo "📝 Bildirimler üretiliyor..."

SUCCESS_COUNT=0
FAILED_COUNT=0

for i in $(seq 1 $NOTIFICATION_COUNT); do
    # Rastgele sunucu ID seç
    SERVER_ID=$(echo "$SERVER_IDS" | shuf -n 1)
    
    # Rastgele bildirim türü seç
    NOTIFICATION_TYPE=$(printf '%s\n' "${NOTIFICATION_TYPES[@]}" | shuf -n 1)
    
    # Rastgele önem seviyesi seç
    PRIORITY=$(printf '%s\n' "${PRIORITIES[@]}" | shuf -n 1)
    
    # Rastgele mesaj seç
    MESSAGE=$(printf '%s\n' "${MESSAGES[@]}" | shuf -n 1)
    
    # Rastgele tarih oluştur (son 30 gün içinde, macOS uyumlu)
    DAYS_AGO=$((RANDOM % 30))
    HOURS_AGO=$((RANDOM % 24))
    MINUTES_AGO=$((RANDOM % 60))
    CREATED_AT=$(date -v-"${DAYS_AGO}"d -v-"${HOURS_AGO}"H -v-"${MINUTES_AGO}"M "+%Y-%m-%d %H:%M:%S")
    
    # JSON verisi oluştur
    JSON_DATA=$(cat <<EOF
{
    "server_id": $SERVER_ID,
    "type": "$NOTIFICATION_TYPE",
    "message": "$MESSAGE",
    "priority": "$PRIORITY",
    "is_read": 0,
    "created_at": "$CREATED_AT"
}
EOF
)
    
    # API'ye POST isteği gönder
    RESPONSE=$(curl -s -w "%{http_code}" -X POST \
        -H "Content-Type: application/json" \
        -d "$JSON_DATA" \
        "$API_BASE_URL/notifications")
    
    HTTP_CODE="${RESPONSE: -3}"
    RESPONSE_BODY="${RESPONSE%???}"
    
    if [ "$HTTP_CODE" = "201" ] || [ "$HTTP_CODE" = "200" ]; then
        echo "✅ Bildirim $i oluşturuldu (Server: $SERVER_ID, Type: $NOTIFICATION_TYPE)"
        ((SUCCESS_COUNT++))
    else
        echo "❌ Bildirim $i başarısız (HTTP: $HTTP_CODE) - $RESPONSE_BODY"
        ((FAILED_COUNT++))
    fi
    
    # API'yi yormamak için kısa bekleme
    sleep 0.1
done

echo ""
echo "🎉 Bildirim üretme tamamlandı!"
echo "📊 Özet:"
echo "   ✅ Başarılı: $SUCCESS_COUNT"
echo "   ❌ Başarısız: $FAILED_COUNT"
echo "   📝 Toplam: $NOTIFICATION_COUNT"
echo ""
echo "🔗 Bildirimleri görüntülemek için: http://localhost/netsentinel/notifications" 