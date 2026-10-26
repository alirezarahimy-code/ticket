SET NAMES utf8mb4;
-- پاسخ‌های اولیه؛ فقط اگر همان پرسش قبلاً نباشد اضافه می‌شود (idempotent)
INSERT INTO chatbot_faq (question, answer, keywords, is_active, created_by, created_at, updated_at)
SELECT 'چطور وارد سامانه شوم؟', 'با نام کاربری و رمز عبور شبکهٔ سازمان وارد شوید. اگر رمز را فراموش کرده‌اید یا ورود ممکن نیست، با پشتیبانی فنی تماس بگیرید.', 'ورود, وارد شدن, وارد شدن به سامانه, وارد بشم, وارد شم, لاگین, لاگین نمیشه, login, نام کاربری, رمز, پسورد, رمز عبور', 1, NULL, NOW(), NOW() FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM chatbot_faq WHERE question = 'چطور وارد سامانه شوم؟');

INSERT INTO chatbot_faq (question, answer, keywords, is_active, created_by, created_at, updated_at)
SELECT 'چطور تیکت جدید ثبت کنم؟', 'از منوی بالا وارد «ثبت تیکت» شوید، موضوع و شرح مشکل را بنویسید و ارسال کنید. پس از ثبت، شماره پیگیری نمایش داده می‌شود.', 'ثبت تیکت, تیکت جدید, ثبت درخواست, درخواست جدید, مشکل, گزارش مشکل, تیکت', 1, NULL, NOW(), NOW() FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM chatbot_faq WHERE question = 'چطور تیکت جدید ثبت کنم؟');

INSERT INTO chatbot_faq (question, answer, keywords, is_active, created_by, created_at, updated_at)
SELECT 'وضعیت تیکتم را از کجا ببینم؟', 'در داشبورد یا فهرست تیکت‌ها، روی شماره تیکت کلیک کنید تا وضعیت، پاسخ‌ها و مراحل رسیدگی را ببینید.', 'پیگیری, وضعیت, وضعیت تیکت, تیکت من, شماره پیگیری, پیگیری تیکت', 1, NULL, NOW(), NOW() FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM chatbot_faq WHERE question = 'وضعیت تیکتم را از کجا ببینم؟');

INSERT INTO chatbot_faq (question, answer, keywords, is_active, created_by, created_at, updated_at)
SELECT 'چطور غذا سفارش دهم؟', 'از بخش «سفارش غذا» وعدهٔ مورد نظر را انتخاب و ثبت کنید. سفارش‌های ثبت‌شده را می‌توانید تا پیش از بسته شدن مهلت مشاهده، ویرایش یا لغو کنید.', 'سفارش غذا, سفارش, غذا, ناهار, صبحانه, منو, سفارش ناهار, لغو سفارش', 1, NULL, NOW(), NOW() FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM chatbot_faq WHERE question = 'چطور غذا سفارش دهم؟');

INSERT INTO chatbot_faq (question, answer, keywords, is_active, created_by, created_at, updated_at)
SELECT 'اطلاعات و عکس پروفایلم را کجا تغییر دهم؟', 'از منوی «پروفایل من» می‌توانید اطلاعات شخصی و عکس خود را ویرایش کنید.', 'پروفایل, ویرایش پروفایل, عکس, اطلاعات من, تغییر اطلاعات, پروفایل من', 1, NULL, NOW(), NOW() FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM chatbot_faq WHERE question = 'اطلاعات و عکس پروفایلم را کجا تغییر دهم؟');

INSERT INTO chatbot_faq (question, answer, keywords, is_active, created_by, created_at, updated_at)
SELECT 'بخش CD/DVD چه کاری انجام می‌دهد؟', 'در بخش CD/DVD رسانه‌ها ثبت و سوابق آن‌ها بررسی می‌شود. برای جزئیات، بخش CD/DVD در راهنمای سامانه را ببینید.', 'سی دی, دی وی دی, cd, dvd, رسانه, CD/DVD, سی‌دی', 1, NULL, NOW(), NOW() FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM chatbot_faq WHERE question = 'بخش CD/DVD چه کاری انجام می‌دهد؟');

INSERT INTO chatbot_faq (question, answer, keywords, is_active, created_by, created_at, updated_at)
SELECT 'ورود مراجعان را کجا ثبت کنم؟', 'در بخش «تردد» ورود و خروج مراجعان ثبت می‌شود و می‌توانید سوابق تردد را بررسی کنید.', 'تردد, مراجع, مراجعه کننده, مراجعه‌کننده, ملاقات, ورود مراجع, ثبت تردد', 1, NULL, NOW(), NOW() FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM chatbot_faq WHERE question = 'ورود مراجعان را کجا ثبت کنم؟');

INSERT INTO chatbot_faq (question, answer, keywords, is_active, created_by, created_at, updated_at)
SELECT 'بخشی را نمی‌بینم یا پیام دسترسی می‌گیرم، چه کنم؟', 'دسترسی هر بخش توسط مدیر سامانه تعیین می‌شود. برای فعال شدن دسترسی، با پشتیبانی یا مدیر خود تماس بگیرید.', 'دسترسی, مجوز, اجازه, نمی بینم, نمیبینم, پیام دسترسی, دسترسی ندارم', 1, NULL, NOW(), NOW() FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM chatbot_faq WHERE question = 'بخشی را نمی‌بینم یا پیام دسترسی می‌گیرم، چه کنم؟');

INSERT INTO chatbot_faq (question, answer, keywords, is_active, created_by, created_at, updated_at)
SELECT 'راهنمای کامل سامانه کجاست؟', 'کامل‌ترین راهنما در بخش «راهنما» در منوی سامانه است. هر بخش مراحل خودش را توضیح داده است.', 'راهنما, کمک, آموزش, راهنمای سامانه, چطور کار کنم, مراحل', 1, NULL, NOW(), NOW() FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM chatbot_faq WHERE question = 'راهنمای کامل سامانه کجاست؟');

INSERT INTO chatbot_faq (question, answer, keywords, is_active, created_by, created_at, updated_at)
SELECT 'چطور تیکتم را لغو کنم؟', 'پیش از ارجاع، ثبت‌کنندهٔ مجاز می‌تواند تیکت را با ذکر دلیل لغو کند. پس از ارجاع، درخواست لغو برای تصمیم مسئول همان حوزه ثبت می‌شود و رسیدگی تا تصمیم او ادامه دارد. لغو، حذف فیزیکی نیست و تاریخچه حفظ می‌شود.', 'لغو تیکت, درخواست لغو, کنسل تیکت, حذف تیکت, منصرف شدم, لغو درخواست', 1, NULL, NOW(), NOW() FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM chatbot_faq WHERE question = 'چطور تیکتم را لغو کنم؟');
