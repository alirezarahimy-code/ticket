# 🛟 راهنمای بکاپ و برگردانی (بخوان، ولی نترس)

## قبل از هر آپدیت — بکاپ از فایلها

### سادهترین راه (توصیه میشود)
همین الان، قبل از اینکه پک رو نصب کنی:
`C:\xampp\htdocs\ticket\` رو **کپی کن** و بچسبون یه جای دیگه
(مثلاً `D:\backup\ticket-20261028\`). همین. نیازی به هیچ ابزاری نیست.

### راه خودکار (برای همیشه)
فایل `tools/backup-files.php` داخل پک ۱.۶۲.۴۱ هست. بعد از نصب، این لینک رو باز کن:

```
http://172.16.4.3/ticket/tools/backup-files.php?k=itsm-backup-16241
```

یه فایل ZIP دانلود میشه و همون ZIP تو `storage/backups-files/` هم ذخیره میشه.
> 🔒 بعد از اینکه بکاپت رو گرفتی، فایل `tools/backup-files.php` رو از رو سرور پاک کن
> (چون `config.php` داخل ZIP هست و نباید لینکش در دسترس بمونه).

### از نسخهٔ ۱.۶۲.۴۱ به بعد
`system-update.php` و `backup.php` طوری تغییر کردن که **قبل از هر آپدیت،
بهصورت خودکار** یه `system-files-before-update.zip` کنار بکاپ دیتابیس میسازن.
دیگه لازم نیست کاری بکنی.

## اگه خدای نکرده خراب شد، چطور برگردونی

1. Apache رو Stop کن.
2. فایلها: ZIP بکاپ رو extract کن و محتواشو روی `C:\xampp\htdocs\ticket\` کپی کن (همه چی overwrite بشه).
3. دیتابیس: پنل ← پشتیبانگیری ← آخرین بکاپ قبل از آپدیت ← برگردانی.
   یا دستی: `mysql -u root persian_ticketing < <backup>\persian_ticketing.sql`
4. Apache رو Start کن + `Ctrl+F5`.

## بکاپ دیتابیس کجاست؟
`create_backup_bundle()` قبل از هر آپدیت یه پوشه تو `storage/backups/<تاریخ>-<ساعت>-<رندوم>/`
میسازه شامل `persian_ticketing.sql`، پوشهٔ `uploads`، و (از ۱.۶۲.۴۱) `system-files.zip`.

## بکاپ شناسنامهها
migration `016` قبل از اینکه هیچ رکورد تکراری رو حذف کنه، یه جدول کامل
به اسم `asset_dedupe_backup_016` میسازه که **تمام ستونهای** رکوردهای حذفشده
+ ستون `dedupe_keeper_id` (که به کدوم رکورد منتقل شده) رو داره.
برای دیدنش:
```sql
SELECT * FROM asset_dedupe_backup_016;
```
