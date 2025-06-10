#!/bin/bash

# Verilen bilgiler
DB_HOST="127.0.0.1"
DB_USER="grant"
DB_PASS="grant345"
DB_PORT="3307"
DB_NAME="netsentinel"

# XAMPP içindeki MySQL yolunu belirle
MYSQL_PATH="/Applications/XAMPP/xamppfiles/bin/mysql"

# MySQL bağlantısını test et
echo "🔍 MySQL bağlantısı test ediliyor..."
"$MYSQL_PATH" -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" -P "$DB_PORT" -e "USE $DB_NAME;" 2> error.log

# Hata kontrolü
if [ $? -eq 0 ]; then
    echo "✅ MySQL bağlantısı başarılı!"
else
    echo "❌ Bağlantı hatası! Ayrıntılar için 'error.log' dosyasını kontrol et."
    cat error.log
fi
