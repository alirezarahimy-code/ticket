-- برنامهٔ نوبت‌های رسیدگی کارشناسان (مرکز درخواست‌ها — فاز سه)
-- افزوده به انتهای main پس از 034؛ افزایشی، idempotent و بدون حذف/بازنویسی داده.
-- هیچ ستون/داده‌ای خارج از جدول جدید تغییر نمی‌کند.

-- برای دیتابیس‌های تازه و دیتابیس‌های ارتقا، هر دو حالت امن است.
CREATE TABLE IF NOT EXISTS request_center_appointments (
    id               INT UNSIGNED      NOT NULL AUTO_INCREMENT,
    ticket_id        INT UNSIGNED      NOT NULL,
    assignee_id      INT UNSIGNED      NOT NULL,
    starts_at        DATETIME          NOT NULL,
    ends_at          DATETIME          NOT NULL,
    kind             VARCHAR(24)       NOT NULL DEFAULT 'work',
    status           VARCHAR(16)       NOT NULL DEFAULT 'planned',
    note             VARCHAR(500)      NULL,
    created_by       INT UNSIGNED      NOT NULL,
    created_at       DATETIME          NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at       DATETIME          NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_rc_appt_assignee_time (assignee_id, starts_at, ends_at),
    KEY idx_rc_appt_ticket (ticket_id),
    KEY idx_rc_appt_status_time (status, starts_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
