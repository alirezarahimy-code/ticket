# گزارش پذیرش Staging — نسخه 1.62.76

**NOT RUN**

هیچ نصب یا تغییری روی Staging یا Production مقصد انجام نشده است. این سند چک‌لیست پذیرش است، نه گزارش قبولی.

## موارد لازم برای پذیرش

- [ ] تأیید Base دقیق 1.62.75 و تهیه Backup کامل فایل/DB.
- [ ] اجرای Update با Updater داخلی روی Clone مستقل.
- [ ] تأیید عدم بازنویسی `config.php`، `storage/` و `uploads/`.
- [ ] اجرای Integration واقعی MariaDB و سناریوهای Commit/Rollback/Circuit Open.
- [ ] آزمون ایجاد، ویرایش، حذف و Import تعطیلات و کنترل Audit اجباری.
- [ ] آزمون CD/DVD، Change/Problem/CAB، LDAP و Profile و کنترل دسته/Module/Target.
- [ ] آزمون فیلتر مدیریتی با Module NULL/خالی و مقایسه با دامنه مورد انتظار.
- [ ] آزمون جست‌وجو و صفحه‌بندی تاریخچه Ticket با نقش کارمند و ثبت‌کننده.
- [ ] بررسی ACL واقعی IIS AppPool و Task Scheduler روی Windows.
- [ ] آزمون Backup/Restore و Rollback روی Clone.
- [ ] ثبت شواهد، نسخه، SHA-256 و نتیجه آزمون توسط مالک سامانه.

تا تکمیل موارد بالا: `STAGING ACCEPTED = NO` و `PRODUCTION APPROVED = NO`.
