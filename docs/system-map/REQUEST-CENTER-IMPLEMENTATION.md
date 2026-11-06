# یادداشت اجرای مرکز درخواست‌ها

این یادداشت، رسید ممیزی برش نخست مرکز درخواست‌هاست و عمداً در کنار ایندکس‌های تولیدشده نگهداری می‌شود. پوشه docs/system-map به‌طور کامل از ورودی سازنده نقشه مستثناست تا خروجی‌های خودِ سازنده باعث کهنگی بازگشتی نشوند.

- شاخهٔ اجرایی: feature/request-center-module-1.62.88
- ورودی مستقل: request-center.php
- Repository: modules/request-center/RequestCenterRepository.php
- ممیزی As-Is: docs/architecture/REQUEST-CENTER-AS-IS-AUDIT-FA.md
- قرارداد CI: .github/workflows/request-center-contract.yml
- آخرین بازتولید نقشه: commit f6f41aa7916f7d8e98a3b09afedf0c8d3ae4d8b6
- تست‌های نقشه: test_system_map_static.py و test_system_map_blocks.py

این برش فقط خواندنی است؛ انتقال عملیات نوشتن، WorkflowService، چهار فضای کاری کامل، تقویم کارشناسان و بستهٔ انتشار در مراحل بعد قرار دارند. این یادداشت و CI موفق به‌معنی مجوز ادغام به main یا نصب روی سامانه عملیاتی نیستند.
