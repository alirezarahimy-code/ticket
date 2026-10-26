<?php
/**
 * اکشن‌های پشتیبان‌گیری و به‌روزرسانی سامانه — جداشده از index.php در نسخهٔ ۱.۶۲.۲۰ (فاز ۲ بازسازی، برش ۹).
 * این فایل داخل بدنهٔ رسیدگی به POST در index.php با require اجرا می‌شود؛ ترتیب و رفتار عیناً حفظ شده است.
 *
 * شرح اکشن‌ها به فارسی:
 * - create_backup: ساخت نسخهٔ پشتیبان کامل (دیتابیس + فایل‌های آپلود) با مجوز backup.manage و ثبت در حسابرسی.
 * - restore_backup: بازیابی از فایل پشتیبان با تأیید نهایی اجباری؛ پیش از بازیابی، نسخهٔ ایمنی خودکار ساخته می‌شود.
 * - system_update: اعمال بستهٔ به‌روزرسانی ZIP (فقط ادمین اصلی + مجوز update.apply)؛ شامل بکاپ خودکار، اجرای مهاجرت‌ها و کپی فایل‌ها.
 *
 * نکتهٔ احتیاط: این گروه خودِ سازوکار به‌روزرسانی سامانه است؛ هر تغییر در آن باید با آزمون کامل بسته همراه باشد.
 */

if (!defined('APP_ROOT')) {
    exit;
}

        if ($action === 'create_backup') {
            $backupAdmin = require_permission('backup.manage');
            $bundle = create_backup_bundle();
            save_audit((int) $backupAdmin['id'], 'backup_created', null, ['name' => $bundle['name'], 'uploads' => $bundle['uploads']]);
            flash('success', 'نسخه پشتیبان ' . $bundle['name'] . ' ساخته شد.');
            redirect('index.php?page=backup');
        }
        if ($action === 'restore_backup') {
            $backupAdmin = require_permission('backup.manage');
            if (post_value('restore_confirm') !== '1') {
                throw new RuntimeException('برای بازیابی باید تأیید نهایی را فعال کنید.');
            }
            $safetyBundle = create_backup_bundle();
            restore_sql_dump($_FILES['sql_dump'] ?? []);
            $restoredUploads = 0;
            if (($_FILES['uploads_archive']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
                $restoredUploads = restore_upload_archive($_FILES['uploads_archive']);
            }
            save_audit((int) $backupAdmin['id'], 'backup_restored', null, ['safety_backup' => $safetyBundle['name'], 'uploads' => $restoredUploads]);
            flash('success', 'بازیابی انجام شد. نسخه ایمنی قبل از بازیابی: ' . $safetyBundle['name']);
            redirect('index.php?page=backup');
        }
        if ($action === 'system_update') {
            $updateAdmin = require_primary_admin();
            require_permission('update.apply');
            try {
                $updateResult = system_update_apply($_FILES['update_package'] ?? [], $updateAdmin, !empty($_POST['allow_downgrade']));
            } catch (RuntimeException $updateError) {
                flash('danger', 'به‌روزرسانی انجام نشد: ' . $updateError->getMessage());
                redirect('index.php?page=backup');
            }
            $modeLabel = ['upgrade' => 'به‌روزرسانی', 'reinstall' => 'نصب مجدد نسخهٔ فعلی', 'downgrade' => 'برگشت به نسخهٔ قدیمی‌تر'][$updateResult['mode']] ?? 'به‌روزرسانی';
            flash('success', $modeLabel . ' به نسخهٔ ' . $updateResult['version'] . ' انجام شد. بکاپ: ' . $updateResult['backup'] . '، مهاجرت‌های اجراشده: ' . count($updateResult['migrations']) . '، فایل‌های کپی‌شده: ' . $updateResult['files'] . '.');
            redirect('index.php?page=backup');
        }
