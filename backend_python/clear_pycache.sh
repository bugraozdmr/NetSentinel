#!/bin/bash

echo "Tüm __pycache__ klasörleri temizleniyor..."

find . -type d -name "__pycache__" -exec rm -rf {} +

echo "Temizlik tamamlandı ✅"
