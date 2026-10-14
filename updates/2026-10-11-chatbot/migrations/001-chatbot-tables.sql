SET NAMES utf8mb4;
-- بات پاسخ‌گو: جدول پرسش‌های متداول و جدول لاگ سؤال‌ها (idempotent و افزایشی)
CREATE TABLE IF NOT EXISTS chatbot_faq (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    question VARCHAR(255) NOT NULL,
    answer TEXT NOT NULL,
    keywords VARCHAR(1000) NOT NULL DEFAULT '',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_by INT UNSIGNED NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    INDEX idx_chatbot_faq_active (is_active)
) DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS chatbot_logs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    question VARCHAR(500) NOT NULL,
    faq_id INT UNSIGNED NULL,
    score DECIMAL(6,2) NOT NULL DEFAULT 0,
    helpful TINYINT(1) NULL,
    created_at DATETIME NOT NULL,
    INDEX idx_chatbot_logs_faq (faq_id),
    INDEX idx_chatbot_logs_user (user_id),
    INDEX idx_chatbot_logs_created (created_at)
) DEFAULT CHARSET=utf8mb4;
