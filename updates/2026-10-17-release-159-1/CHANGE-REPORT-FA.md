# گزارش انتشار نسخهٔ ۱.۵۹.۱ — صدای مرد، کسرهٔ اضافه، تلفظ «س»

**انتشار:** ۱۴۰۵/۰۷/۲۵ (۲۰۲۶-۱۰-۱۷) · **نسخه:** ۱.۵۹.۱ · **مهاجرت دیتابیس:** ندارد

**بسته:** `ticket-update-1.59.1.zip` — نصب عادی از صفحهٔ به‌روزرسانی.

**دانلود مستقیم:** [ticket-update-1.59.1.zip](https://github.com/alirezarahimy-code/ticket/raw/refs/heads/arena/1f9ebe28-ticket/downloads/ticket-update-1.59.1.zip)

## چه چیزی درست شد

- **صدای مرد:** نوع صدا = مرد → مدل امیر؛ زن → مدل مانا.
- **کسرهٔ اضافه:** «سفارش غذا» دیگر قطع نمی‌شود؛ **سفارشِ غذا** خوانده می‌شود.
- **حرف س:** «سلام» دیگر «سسسس» نیست؛ **سَلام** با همهٔ حروف.
- **ساکن نشدن آخر کلمه:** «آماده هست / می شه / چی هست» به‌جای «آمادهه / می‌شه / چیه».

متن روی صفحه عوض نمی‌شود؛ اعراب فقط برای صدا است.

## اگر هنوز مدل مرد ندارید

هر دو فایل را در `C:\piper` بگذارید:

- [fa_IR-amir-medium.onnx](https://huggingface.co/rhasspy/piper-voices/resolve/v1.0.0/fa/fa_IR/amir/medium/fa_IR-amir-medium.onnx)
- [fa_IR-amir-medium.onnx.json](https://huggingface.co/rhasspy/piper-voices/resolve/v1.0.0/fa/fa_IR/amir/medium/fa_IR-amir-medium.onnx.json)

در کارت گویندگی مسیر مدل مرد را وارد کنید، نوع صدا را **مرد (امیر)** بگذارید، ذخیره و تست کنید.

مدل زن مانا مثل ۱.۵۹:

- [fa_IR-mana-medium.onnx](https://huggingface.co/MahtaFetrat/Mana-Persian-Piper/resolve/main/fa_IR-mana-medium.onnx)
- [fa_IR-mana-medium.onnx.json](https://huggingface.co/MahtaFetrat/Mana-Persian-Piper/resolve/main/fa_IR-mana-medium.onnx.json)

بعد از نصب این بسته، یک سؤال **تازه** بپرسید و 🔊 را بزنید (کش صدای قبلی عوض شده است).
