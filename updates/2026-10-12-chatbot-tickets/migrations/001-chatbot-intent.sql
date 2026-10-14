SET NAMES utf8mb4;
-- بات پاسخ‌گو (فاز ۲): ستون نیت (intent) برای سؤال‌های ثبت تیکت و پیگیری؛ افزایشی و idempotent
ALTER TABLE chatbot_logs ADD COLUMN IF NOT EXISTS intent VARCHAR(30) NULL AFTER faq_id;
