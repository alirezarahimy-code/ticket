#!/usr/bin/env bash
# راه‌اندازی محلی سامانه (توسعه/تست — نه staging و نه production) روی Debian/Ubuntu.
# نیازمند: دسترسی sudo، اینترنت به مخازن Debian/Ubuntu، حداقل 2GB RAM.
#
# استفاده:   bash tools/local-run.sh            # آماده‌سازی کامل و راهنمای نهایی
#            bash tools/local-run.sh --reset    # حذف و ساخت دوبارهٔ دیتابیس محلی (تخریبی! با تأیید)
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
DB_NAME="persian_ticketing"
DB_USER="ticketing"
DB_PASS="ticketing-local-dev"
PORT="${1:-}"
if [[ "${1:-}" == "--reset" ]]; then PORT="${2:-}"; fi
PORT="${PORT:-8099}"

echo "== 1/4  نصب بسته‌های سیستمی (Debian/Ubuntu)"
if ! command -v php >/dev/null || ! apt-cache policy php8.2-cli 2>/dev/null | grep -q Candidate; then
  sudo apt-get update
fi
sudo apt-get install -y mariadb-server mariadb-client \
  php8.2-cli php8.2-mysql php8.2-sqlite3 php8.2-mbstring php8.2-xml php8.2-zip php8.2-curl

echo "== 2/4  راه‌اندازی MariaDB"
sudo service mariadb start || sudo systemctl start mariadb
for i in $(seq 1 30); do
  sudo mariadb -e 'SELECT 1' >/dev/null 2>&1 && break
  sleep 1
done

if [[ "${1:-}" == "--reset" ]]; then
  read -rp "دیتابیس محلی '$DB_NAME' کاملاً پاک و دوباره ساخته می‌شود. ادامه؟ [yes/N] " ans
  [[ "$ans" == "yes" ]] || { echo "لغو شد."; exit 1; }
  sudo mariadb -e "DROP DATABASE IF EXISTS \`$DB_NAME\`;"
fi

echo "== 3/4  ساخت دیتابیس و کاربر محلی (idempotent)"
sudo mariadb <<SQL
CREATE DATABASE IF NOT EXISTS \`$DB_NAME\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASS';
CREATE USER IF NOT EXISTS '$DB_USER'@'127.0.0.1' IDENTIFIED BY '$DB_PASS';
GRANT ALL PRIVILEGES ON \`$DB_NAME\`.* TO '$DB_USER'@'localhost';
GRANT ALL PRIVILEGES ON \`$DB_NAME\`.* TO '$DB_USER'@'127.0.0.1';
FLUSH PRIVILEGES;
SQL

echo
echo "== 4/4  آماده شد. مراحل نهایی را خودتان انجام دهید:"
cat <<MSG
  cd "$ROOT"
  php -S 127.0.0.1:$PORT -t .
  # مرورگر: http://127.0.0.1:$PORT/install.php
  #    Wizard اطلاعات اتصال را این‌گونه وارد کنید:
  #      host=127.0.0.1  port=3306  name=$DB_NAME  user=$DB_USER  pass=$DB_PASS
  #    سپس نام کاربری/رمز مدیر اولیه را انتخاب کنید و «ساخت سامانه و ورود» را بزنید.
  #    شِمای کامل (install-empty-db.sql) و مهاجرت‌ها توسط خود نصاب (install.php) اعمال می‌شود.
  #
  # ورود بعدی: http://127.0.0.1:$PORT/   و مرکز درخواست: http://127.0.0.1:$PORT/request-center.php
  # نکته: config.php در ریشهٔ پروژه ساخته می‌شود و در .gitignore ثبت است — آن را commit نکنید.
MSG
