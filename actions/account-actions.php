<?php

declare(strict_types=1);

/**
 * actions/account-actions.php — ۳ اکشن POST احراز هویت، پروفایل و تم (فاز ۲ بازسازی، برش ۷ — نسخهٔ ۱.۶۲.۱۸).
 *
 * شرح فارسی بخش‌ها (به ترتیب داخل فایل):
 *   login                   ورود کاربر (شبکه/محلی) با سنجش کارایی مرحله‌به‌مرحله
 *   save_theme              ذخیرهٔ تم و حالت شب/روز کاربر
 *   save_profile            ذخیرهٔ مشخصات پروفایل و عکس کاربر
 *
 * این فایل فقط از دل بلوک try در «ناحیهٔ ۴» index.php با require بارگذاری می‌شود و در
 * همان حوزهٔ متغیرها اجرا می‌شود. متن اکشن‌ها بایت‌به‌بایت منتقل شده است. تنها موارد function داخل بلوک، کلوژرهای static بی‌نام (سنجش کارایی ورود) و register_shutdown_function هستند که برای include کاملاً بی‌خطرند (اسکن شد).
 */

// محافظ: این فایل فقط از طریق index.php بارگذاری می‌شود؛ دسترسی مستقیم ممنوع.
if (!defined('APP_ROOT')) {
    http_response_code(404);
    exit;
}

        // ───── گروه اکشن: احراز هویت و ورود ─────
        if ($action === 'login') {
            $loginPerfStarted = microtime(true);
            $loginPerfStage = 'login-rate-check';
            $loginPerfStageStarted = $loginPerfStarted;
            $loginPerfStages = [];
            $markLoginPerfStage = static function (string $stage) use (&$loginPerfStage, &$loginPerfStageStarted, &$loginPerfStages): void {
                $now = microtime(true);
                $loginPerfStages[$loginPerfStage] = ($loginPerfStages[$loginPerfStage] ?? 0) + (int) round(($now - $loginPerfStageStarted) * 1000);
                $loginPerfStage = $stage;
                $loginPerfStageStarted = $now;
            };
            register_shutdown_function(static function () use (&$loginPerfStage, &$loginPerfStageStarted, &$loginPerfStages, $loginPerfStarted): void {
                $elapsedMs = (int) round((microtime(true) - $loginPerfStarted) * 1000);
                $loginPerfStages[$loginPerfStage] = ($loginPerfStages[$loginPerfStage] ?? 0) + (int) round((microtime(true) - $loginPerfStageStarted) * 1000);
                if ($elapsedMs >= 2000) {
                    $stageSummary = [];
                    foreach ($loginPerfStages as $stage => $durationMs) {
                        $stageSummary[] = $stage . ':' . $durationMs . 'ms';
                    }
                    error_log('ITSM slow login request: ' . $elapsedMs . 'ms; stages=' . implode(',', $stageSummary));
                }
            });
            $username = post_value('username');
            $password = (string) ($_POST['password'] ?? '');
            if (!login_rate_allowed($username)) {
                flash('danger', 'تلاش‌های ورود بیش از حد مجاز است. ۱۵ دقیقه بعد دوباره تلاش کنید.');
                redirect('index.php?page=login');
            }
            $markLoginPerfStage('local-user-query');
            $query = db()->prepare('SELECT * FROM users WHERE username = ? AND auth_source = "local" AND is_active = 1 LIMIT 1');
            $query->execute([$username]);
            $local = $query->fetch();
            if ($local && password_verify($password, (string) $local['password_hash'])) {
                $markLoginPerfStage('local-login-finalize');
                db()->prepare('UPDATE users SET last_login_at = NOW() WHERE id = ?')->execute([$local['id']]);
                clear_login_failures($username);
                login_user($local);
                $markLoginPerfStage('client-identity');
                $initialAssetId = inventory_collect_client_asset($local);
                if ($initialAssetId) {
                    record_asset_history($initialAssetId, 'inventory_collected', 'شناسایی خودکار سیستم کلاینت در اولین ورود', (int) $local['id'], null, 'نام سیستم و IP کلاینت از درخواست ورود ثبت شد.');
                }
                redirect('index.php');
            }
            $markLoginPerfStage('ldap-authentication');
            $identity = ldap_authenticate($username, $password);
            if ($identity) {
                $markLoginPerfStage('domain-user-sync');
                $domainUser = upsert_domain_user($identity);
                if ((int) ($domainUser['is_active'] ?? 0) !== 1) {
                    throw new RuntimeException('حساب کاربری شبکه غیرفعال است. ادمین اصلی باید آن را از تنظیمات سامانه فعال کند.');
                }
                $markLoginPerfStage('domain-login-finalize');
                db()->prepare('UPDATE users SET last_login_at = NOW() WHERE id = ?')->execute([$domainUser['id']]);
                clear_login_failures($username);
                login_user($domainUser);
                $markLoginPerfStage('client-identity');
                $initialAssetId = inventory_collect_client_asset($domainUser);
                if ($initialAssetId) {
                    record_asset_history($initialAssetId, 'inventory_collected', 'شناسایی خودکار سیستم کلاینت در اولین ورود', (int) $domainUser['id'], null, 'نام سیستم و IP کلاینت از درخواست ورود ثبت شد.');
                }
                redirect('index.php');
            }
            record_login_failure($username);
            flash('danger', 'نام کاربری یا رمز عبور صحیح نیست.');
            redirect('index.php?page=login');
        }

        $user = require_login();

        // ───── گروه اکشن: پروفایل، تم و ظاهر ─────
        if ($action === 'save_theme') {
            $themeChoice = valid_choice(post_value('theme'), ['current', 'ruby', 'indigo', 'copper'], 'current');
            save_setting('theme_user_' . (int) $user['id'], $themeChoice);
            $GLOBALS['activity_audited'] = true;
            activity_log(['action_code' => 'user_theme_changed', 'action_label' => 'تغییر تم شخصی', 'module' => 'profile', 'meta' => ['theme' => ['current'=>'تم فعلی سبز و کرم','ruby'=>'یاقوتی رسمی','indigo'=>'لاجوردی مدیریتی','copper'=>'آجری روشن و کرم'][$themeChoice]]]);
            flash('success', 'تم انتخابی شما ذخیره و اعمال شد.');
            redirect('index.php?page=profile#appearance');
        }
        if ($action === 'save_profile') {
            $firstName = post_value('first_name');
            $lastName = post_value('last_name');
            $nationalCode = strtr(post_value('national_code'), ['۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4', '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9', '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4', '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9']);
            if ($firstName === '' || $lastName === '') {
                throw new RuntimeException('نام و نام خانوادگی را وارد کنید.');
            }
            if ($nationalCode !== '' && !valid_iranian_national_code($nationalCode)) {
                throw new RuntimeException('کد ملی معتبر نیست. کد ملی باید ۱۰ رقم و دارای رقم کنترل صحیح باشد.');
            }
            $photo = store_profile_photo((array) ($_FILES['profile_photo'] ?? []));
            $query = db()->prepare('UPDATE users SET first_name = ?, last_name = ?, full_name = ?, national_code = ?' . ($photo !== '' ? ', profile_photo = ?' : '') . ' WHERE id = ?');
            $values = [$firstName, $lastName, trim($firstName . ' ' . $lastName), $nationalCode !== '' ? $nationalCode : null];
            if ($photo !== '') {
                $values[] = $photo;
            }
            $values[] = (int) $user['id'];
            $query->execute($values);
            save_audit((int) $user['id'], 'profile_updated');
            flash('success', 'پروفایل کاربری شما ذخیره شد.');
            redirect('index.php?page=profile');
        }
