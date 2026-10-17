-- یکسان‌سازی وضعیت قدیمی pending با waiting_user.
-- کد هر دو را «در انتظار پاسخ کاربر» می‌داند و SLA/OLA را برای هر دو متوقف می‌کند،
-- اما برای حذف بن‌بست‌های قدیمی (فیلتر صف، گزارش و تغییر وضعیت)، همهٔ درخواست‌های
-- بازِ pending به waiting_user تبدیل می‌شوند. تاریخچهٔ ticket_events دست‌نخورده می‌ماند.
-- این اسکریپت idempotent است و اجرای چندبارهٔ آن بی‌اثر است.
UPDATE `tickets`
SET `status` = 'waiting_user', `updated_at` = NOW()
WHERE `status` = 'pending';
