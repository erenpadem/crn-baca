#!/bin/bash
# Sunucuya atılacak paketi oluşturur (vendor + build dahil).
# Kullanım: ./paketle-sunucu.sh
# Çıktı: crn-deploy.zip (bir üst klasörde)

set -e
cd "$(dirname "$0")"
OUT="../crn-deploy-$(date +%Y%m%d-%H%M).zip"

echo "Paketleniyor: $OUT"
zip -r "$OUT" . \
  -x ".git/*" \
  -x ".env" \
  -x ".env.backup" \
  -x ".env.production" \
  -x "node_modules/*" \
  -x ".DS_Store" \
  -x "*.log" \
  -x "storage/logs/*" \
  -x "storage/framework/cache/data/*" \
  -x "storage/framework/sessions/*" \
  -x "storage/framework/views/*" \
  -x ".phpunit.result.cache" \
  -x "phpunit.xml" \
  -x "tests/*"

echo "Tamam. Yüklenecek dosya: $OUT"
echo "Sunucuya atma adımları için DEPLOY_SUNUCU.md dosyasına bakın."
