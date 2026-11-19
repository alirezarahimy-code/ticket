#!/usr/bin/env bash
# بالا آوردن محیط واقعی PHP + MariaDB در سندباکس Arena (توسعه/تست — غیرتخریبی).
# پیشنیاز: بستههای .deb رسمی Debian 12 قبلاً نصب شده باشند (docs/ARENA-RUNTIME-FA.md).
# این اسکریپت هیچ DROP/DELETE/بازنشانی انجام نمیدهد؛ فقط «اگر نبود» میسازد.
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
DB_NAME="persian_ticketing"
DB_USER="ticketing"
DB_PASS="ticketing-local-dev"   # همان قرارداد محلی tools/local-run.sh

echo "== ۱/۴  بررسی باینریها =="
for bin in php mariadb mariadb-install-db; do
    command -v "$bin" >/dev/null || {
        echo "خطا: «$bin» نصب نیست. apt در این سندباکس بلاک است؛"
        echo "      طبق docs/ARENA-RUNTIME-FA.md بستههای .deb را دوباره بسازید."
        exit 1
    }
done
php -v | head -1
mariadb --version
php -m | grep -qi '^pdo_mysql$' || { echo "خطا: امتداد pdo_mysql فعال نیست."; exit 1; }

echo "== ۲/۴  راهاندازی سرویس MariaDB =="
if command -v systemctl >/dev/null && sudo systemctl is-active --quiet mariadb; then
    echo "سرویس از قبل فعال است."
else
    sudo systemctl start mariadb 2>/dev/null || sudo service mariadb start
fi
for i in $(seq 1 30); do
    sudo mariadb -e 'SELECT 1' >/dev/null 2>&1 && break
    sleep 1
done
sudo mariadb -e 'SELECT VERSION() AS version, @@port AS port;' || {
    echo "خطا: MariaDB پاسخ نمیدهد."; exit 1
}

echo "== ۳/۴  ساخت پایگاهداده و کاربر محلی (اگر نباشند) =="
sudo mariadb <<SQL
CREATE DATABASE IF NOT EXISTS \`$DB_NAME\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASS';
CREATE USER IF NOT EXISTS '$DB_USER'@'127.0.0.1' IDENTIFIED BY '$DB_PASS';
GRANT ALL PRIVILEGES ON \`$DB_NAME\`.* TO '$DB_USER'@'localhost';
GRANT ALL PRIVILEGES ON \`$DB_NAME\`.* TO '$DB_USER'@'127.0.0.1';
FLUSH PRIVILEGES;
SQL

TABLES=$(mariadb --skip-ssl -h 127.0.0.1 -u "$DB_USER" -p"$DB_PASS" -N \
    -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='$DB_NAME';" 2>/dev/null)
echo "جدولهای فعلی: $TABLES"
if [ "$TABLES" -eq 0 ]; then
    echo "بارگذاری شِمای نصب تازه (PDO) ..."
    (cd "$ROOT" && CI_DB_HOST=127.0.0.1 CI_DB_PORT=3306 CI_DB_NAME="$DB_NAME" \
        CI_DB_USER="$DB_USER" CI_DB_PASS="$DB_PASS" \
        php tools/ci_load_schema.php install-empty-db.sql)
else
    echo "شِما موجود است؛ هیچ چیز بازنویسی نشد."
fi

echo
echo "== ۴/۴  آماده =="
cat <<MSG
  وبسرور:   cd "$ROOT" && php -S 0.0.0.0:8099 -t .
  بوت جلسه: python3 tools/session-boot.py
  صحت DB:   CI_DB_HOST=127.0.0.1 CI_DB_NAME=$DB_NAME CI_DB_USER=$DB_USER \\
            CI_DB_PASS=$DB_PASS CI_DB_REQUIRED=1 \\
            php tests/test_mariadb_fresh_install.php fresh-install
MSG
