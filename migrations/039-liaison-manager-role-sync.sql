-- C6 — ارتقای یک‌بارهٔ مدیران واحدهای مستقل (liaison) به نقش «manager» پس از آپدیت.
-- تا قبل از این مهاجرت، مدیران واحدهای مستقل در org_position_unit_types نبودند و نقش خودکار نمی‌گرفتند؛
-- بنابراین تا SAVE بعدیِ چارت ارتقا نمی‌یافتند. این فایل همان ارتقا را به‌صورت idempotent اجرا می‌کند.
-- نقش‌های استثنا (supervisor / support_manager / inspector / primary_admin) و نقش‌های manager/«سایر» دست نمی‌خورند؛
-- org_sync_position_roles() همچنان مرجع نهایی همگام‌سازی است و پس از هر ذخیرهٔ چارت دوباره اعمال می‌شود.
ALTER TABLE users ADD COLUMN IF NOT EXISTS org_auto_role TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE users ADD COLUMN IF NOT EXISTS org_prev_role VARCHAR(20) NULL;

UPDATE users u
  JOIN org_units ou ON ou.manager_user_id = u.id AND ou.is_active = 1 AND ou.unit_type = 'liaison'
   SET u.org_prev_role = CASE WHEN u.org_auto_role = 1 AND u.org_prev_role = '__agent__' THEN u.org_prev_role ELSE u.role END,
       u.org_auto_role = 1,
       u.role = 'manager'
 WHERE u.role IN ('user', 'agent');

UPDATE users u
  JOIN org_unit_managers m ON m.user_id = u.id
  JOIN org_units ou ON ou.id = m.unit_id AND ou.is_active = 1 AND ou.unit_type = 'liaison'
   SET u.org_prev_role = CASE WHEN u.org_auto_role = 1 AND u.org_prev_role = '__agent__' THEN u.org_prev_role ELSE u.role END,
       u.org_auto_role = 1,
       u.role = 'manager'
 WHERE u.role IN ('user', 'agent');
