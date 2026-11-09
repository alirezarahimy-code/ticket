<?php
declare(strict_types=1);

/**
 * برش فاز استقلال فاز ۲ — ارسال بایت‌به‌بایت از index.php.
 * این فایل لایهٔ «chrome مشترک سایت» است: هدر ناوبری، فوتر، وضعیت فعال ماژول لیست‌مرتب،
 * و یادآور نمایش شاجومد/بازرسی غذا (که از قبل در رندر تمام صفحه‌ها نقش chrome داشت).
 * مالکیت و رفتار هیچ‌کدام تغییر نکرده است؛ فقط محل تعریف جابه‌جا شده تا هم index.php
 * و هم ورودی مستقل modules/request-center بتوانند همان صفحه‌ها را بدون front controller اجرا کنند.
 */

function render_header(string $title, ?array $user = null): void
{
    global $page;
    $isRequestCenterEntry = defined('REQUEST_CENTER_INDEPENDENT_ENTRY') && REQUEST_CENTER_INDEPENDENT_ENTRY === true;
    $appName = setting('app_name', (string) cfg('app.name', 'سامانه درخواست پشتیبانی'));
    $logo = setting('app_logo', (string) cfg('app.logo', ''));
    $flashes = take_flash();
    $theme = $user ? valid_choice((string) setting('theme_user_' . (int) $user['id'], 'current'), ['current', 'ruby', 'indigo', 'copper'], 'current') : 'current';
    $notificationSummary = ($user && !$isRequestCenterEntry)
        ? notification_header_summary((int) $user['id'], $user)
        : ['unread' => 0, 'latest' => 0];
    $notificationCount = $notificationSummary['unread'];
    $layoutPage = 'dashboard';
    $layoutAdmin = false;
    $layoutCss = '';
    try {
        if (function_exists('admin_layout_design_page_key')
            && function_exists('admin_layout_design_is_admin')
            && function_exists('admin_layout_design_css_for_page')) {
            $layoutPage = admin_layout_design_page_key($page ?? ($_GET['page'] ?? 'dashboard'));
            $layoutAdmin = $user !== null && admin_layout_design_is_admin($user);
            $layoutCss = admin_layout_design_css_for_page($layoutPage);
        }
    } catch (Throwable $exception) {
        app_error_log('page layout enhancement skipped: ' . $exception->getMessage());
        $layoutPage = 'dashboard';
        $layoutAdmin = false;
        $layoutCss = '';
    }
    $bodyPageClass = ($page ?? '') === 'organization' ? 'page-organization' : '';
    $themeMode = app_theme_mode();
    echo '<!doctype html><html lang="fa" dir="rtl" data-theme="' . e($theme) . '" data-theme-mode="' . e($themeMode) . '"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="theme-color" content="' . ($themeMode === 'dark' ? '#101915' : '#174f43') . '"><title>' . e($title . ' | ' . $appName) . '</title><link rel="stylesheet" href="assets/style.css?v=3.4.60">' . ($layoutAdmin ? '<link rel="stylesheet" href="assets/admin-layout-design.css?v=9">' : '') . ($layoutCss !== '' ? '<style id="admin-page-layout-rules">' . $layoutCss . '</style>' : '') . '<link rel="stylesheet" href="assets/theme-mode.css?v=3">' . (($page ?? '') === 'logging-dashboard' ? '<link rel="stylesheet" href="assets/logging-dashboard.css?v=1">' : '') . '</head><body class="' . e($bodyPageClass) . '" data-theme-mode="' . e($themeMode) . '" data-layout-page="' . e($layoutPage) . '" data-layout-design-admin="' . ($layoutAdmin ? '1' : '0') . '" data-weekend-days="' . e(implode(',', sla_business_hours()['weekend'])) . '"><div class="app-shell"><header class="topbar"><div class="topbar-inner"><a class="brand" href="' . ($isRequestCenterEntry ? 'request-center.php' : 'index.php') . '"><span class="brand-mark">' . ($logo ? '<img src="' . e($logo) . '" alt="' . e($appName) . '">' : '<span class="brand-glyph">پ</span>') . '</span><span><strong>' . e($appName) . '</strong><small>مرکز خدمات و پشتیبانی</small></span></a>' . (!$user ? app_theme_toggle_markup() : '');
    if ($user) {
        $menuIcons = [
            'home' => '<path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>',
            'ticket' => '<path d="M3 9a3 3 0 0 0 0 6v3a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-3a3 3 0 0 1 0-6V6a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2z"/>',
            'disc' => '<circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="3"/>',
            'traffic' => '<path d="M3 21h18"/><path d="M5 21V7l8-4v18"/><path d="M19 21V11l-6-4"/>',
            'food' => '<path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2"/><path d="M7 2v20"/><path d="M21 15V2a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3zm0 0v7"/>',
            'support' => '<path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94z"/>',
            'reports' => '<line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/>',
            'settings' => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 1 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 1 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 1 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 1 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>',
            'bell' => '<path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/>',
            'logout' => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>',
        ];
        $menuIcon = static function (string $name) use ($menuIcons): string {
            return '<svg class="icon-svg" viewBox="0 0 24 24" aria-hidden="true" focusable="false">' . ($menuIcons[$name] ?? '') . '</svg>';
        };
        $menuLink = static function (string $href, string $label, string $icon) use ($menuIcon): string {
            return '<a href="' . e($href) . '">' . $menuIcon($icon) . '<span>' . e($label) . '</span></a>';
        };
        $submenuLink = static function (string $href, string $label): string {
            return '<li><a href="' . e($href) . '">' . e($label) . '</a></li>';
        };
        $submenu = static function (string $id, string $label, string $icon, array $links) use ($menuIcon): string {
            if (!$links) {
                return '';
            }
            return '<li class="has-submenu"><details class="nav-dropdown"><summary aria-controls="' . e($id) . '">' . $menuIcon($icon) . '<span>' . e($label) . '</span><span class="arrow" aria-hidden="true"></span></summary><ul class="submenu" id="' . e($id) . '">' . implode('', $links) . '</ul></details></li>';
        };
        if ($isRequestCenterEntry) {
            $menuItems = [
                '<li>' . $menuLink('request-center.php', 'خانه مرکز درخواست‌ها', 'home') . '</li>',
            ];
            if (user_can($user, 'ticket.create') || user_can($user, 'ticket.view_own')) {
                $menuItems[] = '<li>' . $menuLink('request-center.php?workspace=requester', 'فضای درخواست‌دهنده', 'ticket') . '</li>';
            }
            if (user_can($user, 'ticket.edit') || user_can($user, 'queue.view')
                || user_can($user, 'ticket.view_unit') || user_can($user, 'ticket.view_all')) {
                $menuItems[] = '<li>' . $menuLink('request-center.php?workspace=agent', 'فضای کارشناس', 'support') . '</li>';
            }
            if (user_can($user, 'queue.view')) {
                $menuItems[] = '<li>' . $menuLink('request-center.php?page=queue', 'صف عملیاتی', 'ticket') . '</li>';
            }
            if (user_can($user, 'supervisor.panel')) {
                $menuItems[] = '<li>' . $menuLink('request-center.php?workspace=supervisor', 'فضای سوپروایزر', 'reports') . '</li>';
                $menuItems[] = '<li>' . $menuLink('request-center.php?page=supervisor', 'صف تصمیم‌ها', 'settings') . '</li>';
            }
            if (user_is_primary_admin($user)) {
                $menuItems[] = '<li>' . $menuLink('request-center.php?workspace=admin', 'مدیریت مرکز درخواست‌ها', 'settings') . '</li>';
            }
            if (user_can($user, 'ticket.create')) {
                $menuItems[] = '<li>' . $menuLink('request-center.php?page=new-ticket', 'ثبت درخواست', 'ticket') . '</li>';
            }
            if (user_can($user, 'reports.view')) {
                $menuItems[] = '<li>' . $menuLink('request-center.php?page=reports', 'گزارش‌ها', 'reports') . '</li>';
            }
            if (user_can($user, 'analytics.view')) {
                $menuItems[] = '<li>' . $menuLink('request-center.php?page=analytics', 'تحلیل عملکرد', 'reports') . '</li>';
            }
            if (is_admin_role((string) ($user['role'] ?? '')) || user_is_primary_admin($user)) {
                $menuItems[] = '<li>' . $menuLink('request-center.php?page=services', 'کاتالوگ خدمات', 'support') . '</li>';
            }
            $menuItems[] = '<li class="logout"><form method="post" class="logout-form">' . csrf_field() . '<input type="hidden" name="action" value="logout"><button class="logout-link" type="submit">' . $menuIcon('logout') . '<span>خروج</span></button></form></li>';
            echo '<div class="header-tools">' . app_theme_toggle_markup() . '<span class="profile-shortcut">' . e((string) ($user['full_name'] ?? $user['username'] ?? '')) . '</span></div>';
            echo '<nav class="navbar" aria-label="ناوبری مرکز درخواست‌ها"><ul class="menu">' . implode('', $menuItems) . '</ul></nav>';
        } else {
        $supportLinks = [
            $submenuLink('index.php?page=services', 'خدمات'),
            $submenuLink('index.php?page=knowledge', 'دانش‌نامه'),
        ];
        if (user_can($user, 'queue.view')) {
            $supportLinks[] = $submenuLink('index.php?page=queue', 'صف کاری');
        }
        if (user_can($user, 'assets.view')) {
            $supportLinks[] = $submenuLink('index.php?page=assets', 'شناسنامه‌های فنی');
        }
        // ۱.۳۷.۷ — «تغییر و مشکل» فقط برای کارمندان: پیش‌تر این آیتم در منوی کاربران عادی هم
        // دیده می‌شد، ولی خود صفحه با require_staff() رد می‌کرد.
        if (user_can($user, 'governance.view') && is_staff_role((string) $user['role'])) {
            $supportLinks[] = $submenuLink('governance.php', 'تغییر و مشکل');
        }
        if (user_can($user, 'supervisor.panel')) {
            $supportLinks[] = $submenuLink('index.php?page=supervisor', 'پنل سوپروایزر');
        }
        if (user_can($user, 'ola.view')) {
            $supportLinks[] = $submenuLink('ola.php', 'OLA');
        }
        $reportLinks = [];
        if (user_can($user, 'reports.view')) {
            $reportLinks[] = $submenuLink('index.php?page=reports', 'گزارش درخواست‌ها');
        }
        if (user_can($user, 'analytics.view')) {
            $reportLinks[] = $submenuLink('index.php?page=analytics', 'چارت‌ها');
        }
        if (user_can_any($user, ['logs.activity', 'audit.view', 'logs.view'])) {
            $reportLinks[] = $submenuLink('index.php?page=activity-log', 'لاگ سامانه');
        }
        if (user_can($user, 'system.health')) {
            $reportLinks[] = $submenuLink('index.php?page=logging-dashboard', 'داشبورد مانیتورینگ لاگینگ');
            $reportLinks[] = $submenuLink('index.php?page=system-health', 'سلامت سامانه');
        }
        $adminLinks = [];
        if (user_can($user, 'org.view')) {
            $adminLinks[] = $submenuLink('index.php?page=organization', 'سازمان');
        }
        if (user_can_any($user, ['settings.general', 'settings.domain', 'settings.users'])) {
            $adminLinks[] = $submenuLink('index.php?page=settings', 'تنظیمات');
        }
        if (user_can($user, 'backup.view')) {
            $adminLinks[] = $submenuLink('index.php?page=backup', 'به‌روزرسانی و پشتیبان‌گیری');
        }
        if (user_can($user, 'chatbot.manage')) {
            $adminLinks[] = $submenuLink('index.php?page=chatbot-admin', 'مدیریت بات سخنگو');
        }
        $menuItems = [
            '<li>' . $menuLink('index.php', 'داشبورد', 'home') . '</li>',
        ];
        // مرکز درخواست‌ها ورودی رسمی Workspaceهای RC است؛ مسیر قدیمی
        // ثبت درخواست فقط به‌عنوان Bridge سازگاری حفظ می‌شود.
        if (user_can_any($user, ['ticket.create', 'ticket.view_own', 'ticket.edit', 'queue.view', 'ticket.view_unit', 'ticket.view_all', 'supervisor.panel']) || user_is_primary_admin($user)) {
            $menuItems[] = '<li>' . $menuLink('request-center.php', 'مرکز درخواست‌ها', 'ticket') . '</li>';
        }
        if (user_can($user, 'cddvd.view')) {
            $menuItems[] = '<li>' . $menuLink('index.php?page=cd-dvd', 'کنترل CD/DVD', 'disc') . '</li>';
        }
        if (user_can($user, 'traffic.view')) {
            $menuItems[] = '<li>' . $menuLink('index.php?page=traffic-control', 'کنترل تردد', 'traffic') . '</li>';
        }
        if (user_can($user, 'foodorder.self')) {
            $menuItems[] = '<li>' . $menuLink('index.php?page=food-order', 'سفارش غذا', 'food') . '</li>';
        }
        if (function_exists('food_ticket_is_allowed') && food_ticket_is_allowed($user)) {
            $menuItems[] = '<li>' . $menuLink('index.php?page=food-ticket', 'سامانه مدیریت غذا', 'food') . '</li>';
        }
        $menuItems[] = $submenu('menu-support', 'پشتیبانی', 'support', $supportLinks);
        $menuItems[] = $submenu('menu-reports', 'گزارش‌ها', 'reports', $reportLinks);
        $menuItems[] = $submenu('menu-management', 'مدیریت', 'settings', $adminLinks);
        $menuItems[] = '<li>' . $menuLink('index.php?page=help', 'راهنما', 'support') . '</li>';
        if (user_can($user, 'notif.view')) {
    $menuItems[] = '<li class="notification-item"><a class="notification-link" href="index.php?page=notifications">' . $menuIcon('bell') . '<span>اعلان‌ها</span>' . ($notificationCount > 0 ? '<b>' . (int) $notificationCount . '</b>' : '') . '</a></li>';
        }
        $menuItems[] = '<li class="logout"><form method="post" class="logout-form">' . csrf_field() . '<input type="hidden" name="action" value="logout"><button class="logout-link" type="submit">' . $menuIcon('logout') . '<span>خروج</span></button></form></li>';
        echo '<div class="header-tools"><form class="global-search header-search" method="get"><input type="hidden" name="page" value="search"><input name="q" placeholder="جست‌وجو در صفحه‌ها، درخواست، دارایی یا دانش‌نامه"></form>' . app_theme_toggle_markup() . ($layoutAdmin ? '<button class="layout-design-launch" type="button" data-layout-design-launch data-layout-page-title="' . e(admin_layout_design_page_label($layoutPage)) . '" aria-pressed="false">🎨 طراحی همین صفحه</button>' : '') . '<a class="profile-shortcut" href="index.php?page=profile#appearance">🎨 انتخاب تم</a><a class="profile-shortcut" href="index.php?page=profile">پروفایل: ' . e((string) $user['username']) . '</a></div>';
        echo '<nav class="navbar" aria-label="ناوبری اصلی"><ul class="menu">' . implode('', $menuItems) . '</ul></nav>';
        }
    }
      echo '</div></header><div class="content-wrap">';
      echo '<div class="ambient-orbits" aria-hidden="true"><span class="orbit orbit-reusable orbit-reusable--top orbit-reusable--lg"></span><span class="orbit orbit-reusable orbit-reusable--ring orbit-reusable--md"></span><span class="orbit orbit-reusable orbit-reusable--bottom orbit-reusable--xl"></span><span class="orbit orbit-reusable orbit-reusable--side orbit-reusable--sm"></span><span class="orbit orbit-reusable orbit-reusable--corner orbit-reusable--md"></span></div>';
     if ($user && !$isRequestCenterEntry) {
         echo '<div id="notification-toasts" class="notification-toasts" data-cursor="' . (int) $notificationSummary['latest'] . '" aria-live="polite"></div>';
     }
     foreach ($flashes as $flash) {
         echo '<div class="alert ' . e($flash['type']) . '">' . e($flash['message']) . '</div>';
     }
     // توکن CSRF را پیش از بستن session بساز تا فرم‌های همین صفحه معتبر بمانند.
     csrf_token();
     // آزادسازی قفل فایل session پس از رندر هدر تا درخواست‌های موازی (پولینگ اعلان‌ها و
     // بارگذاری تصاویر/فونت) پشت سر هم بلاک نشوند. فقط خواندن session بعد از این ممکن است.
     if (function_exists('session_write_close') && session_status() === PHP_SESSION_ACTIVE) {
         @session_write_close();
     }
 }

/**
 * صفحه‌هایی که لیست‌شان در سمت سرور محدود یا صفحه‌بندی شده است، مرتب‌ساز موقت را ندارند
 * تا فقط بخشی از داده مرتب نشود. پس از اضافه‌شدن «کل نتایج فیلتر» این فهرست خالی می‌شود.
 */
function list_sort_page_off(): bool
{
    return in_array((string) ($_GET['page'] ?? ''), ['logs', 'activity-log', 'traffic-control', 'domain-scan'], true);
}

function inspection_food_reminder_markup(?array $user): string
{
    if (!$user || setting('inspection_food_absence_alarm_enabled', '1') === '0') {
        return '';
    }
    $role = (string) ($user['role'] ?? '');
    $isInspectionUser = in_array($role, ['inspector', 'guard'], true)
        || (function_exists('user_is_key_role') && user_is_key_role($user, 'inspection'));
    if (!$isInspectionUser) {
        return '';
    }

    $now = new DateTimeImmutable('now', new DateTimeZone(date_default_timezone_get()));
    $todayReminder = $now->setTime(11, 0, 0);
    $dueNow = $now >= $todayReminder;
    $nextReminder = $dueNow ? $todayReminder->modify('+1 day') : $todayReminder;
    $name = trim((string) ($user['full_name'] ?? ''));
    if ($name === '') {
        $name = (string) ($user['username'] ?? '');
    }
    return '<div class="inspection-food-reminder" id="inspection-food-reminder" hidden aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="inspection-food-reminder-title" data-user-id="' . (int) ($user['id'] ?? 0) . '" data-timezone="' . e(date_default_timezone_get()) . '" data-server-now="' . $now->getTimestamp() . '" data-today-reminder-at="' . $todayReminder->getTimestamp() . '" data-next-reminder-at="' . $nextReminder->getTimestamp() . '" data-due-now="' . ($dueNow ? '1' : '0') . '"><section class="inspection-food-reminder-card"><div class="inspection-food-reminder-icon" aria-hidden="true">!</div><p class="inspection-food-reminder-heading">توجه</p><h2 id="inspection-food-reminder-title">آقای «' . e($name) . '» لطفا استعلام غایبین گروه های غذایی رو انجام بدید برای ثبت در گروه غذایی.</h2><button type="button" class="button inspection-food-reminder-dismiss">متوجه شدم</button></section></div>';
}

function render_footer(): void
{
    $appName = setting('app_name', (string) cfg('app.name', 'سامانه درخواست پشتیبانی'));
    echo '<script nonce="' . e(csp_nonce()) . '">window.ITSM_LIST_SORT_OFF = ' . (list_sort_page_off() ? 'true' : 'false') . '; window.ITSM_CSRF = "' . e(csrf_token()) . '";</script><script defer src="assets/list-sort.js?v=2"></script><script defer src="assets/server-sort.js?v=1"></script>';
    $layoutUser = current_user();
    if ($layoutUser && function_exists('admin_layout_design_is_admin') && admin_layout_design_is_admin($layoutUser)) {
        echo '<script defer src="assets/admin-layout-design.js?v=10"></script>';
    }
    echo '</div><footer class="footer">' . e($appName) . ' • نسخه سازمانی فارسی • سازنده سامانه: Ali.r.Rahimi • نسخهٔ سامانه ' . e(app_version()) . '</footer></div><script nonce="' . e(csp_nonce()) . '">window.ITSM_HOLIDAYS = ';
    try {
        $__h = db()->query("SELECT holiday_date, title FROM holidays WHERE is_active = 1")->fetchAll();
        $__out = [];
        foreach ($__h as $__row) {
            $g = (string) $__row["holiday_date"];
            $parts = explode("-", substr($g, 0, 10));
            if (count($parts) === 3) {
                [$gy, $gm, $gd] = [(int) $parts[0], (int) $parts[1], (int) $parts[2]];
                [$jy, $jm, $jd] = gregorian_to_jalali($gy, $gm, $gd);
                $__out[] = ["date" => $g, "jalali" => sprintf("%04d/%02d/%02d", $jy, $jm, $jd), "title" => (string) $__row["title"]];
            }
        }
        echo json_encode($__out, JSON_UNESCAPED_UNICODE);
    } catch (Throwable) {
        echo "[]";
    }
    // Optional feature modules are not loaded by the independent Request Center entry.
    // Keep the shared shell usable without importing food, chatbot or visual-effects modules.
    $inspectionReminder = function_exists('inspection_food_reminder_markup') ? inspection_food_reminder_markup($layoutUser) : '';
    $particleScript = function_exists('particle_effect_script_tag') ? particle_effect_script_tag() : '';
    $triangleScript = function_exists('triangle_effect_script_tag') ? triangle_effect_script_tag() : '';
    $chatbotWidget = function_exists('chatbot_render_widget') ? chatbot_render_widget($layoutUser) : '';
    echo ';</script><script src="assets/jalali-calendar.js?v=3.4.7"></script><script src="assets/app.js?v=3.4.28&theme-mode=2"></script>' . $particleScript . $triangleScript . $chatbotWidget . $inspectionReminder;
    if ($inspectionReminder !== '') {
        echo '<script defer src="assets/inspection-food-reminder.js?v=1"></script>';
    }
    echo '</body></html>';
}
