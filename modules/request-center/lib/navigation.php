<?php
declare(strict_types=1);

/**
 * Request Center-only navigation.
 *
 * منوی فشرده و گروه‌بندی‌شده (هم‌شکل منوی سامانهٔ اصلی) + لینک سادهٔ «بازگشت به سامانه».
 * این تابع داخل render_header() بارگذاری می‌شود؛ چهرهٔ صفحات RC را هسته تعیین می‌کند.
 * دکمه‌های «حالت شب» و نام کاربر در ردیف برند (بالای منو) توسط render_header() رندر می‌شوند.
 *
 * @param callable(string,string,string):string $menuLink
 * @param callable(string):string $menuIcon
 */
function request_center_render_navigation(array $user, callable $menuLink, callable $menuIcon): void
{
    $submenuLink = static fn (string $href, string $label): string =>
        '<li><a href="' . e($href) . '">' . e($label) . '</a></li>';

    $submenu = static function (string $id, string $label, string $icon, array $links) use ($menuIcon): string {
        $links = array_filter($links);
        if ($links === []) {
            return '';
        }
        return '<li class="has-submenu"><details class="nav-dropdown"><summary aria-controls="' . e($id) . '">'
            . $menuIcon($icon) . '<span>' . e($label) . '</span><span class="arrow" aria-hidden="true"></span></summary>'
            . '<ul class="submenu" id="' . e($id) . '">' . implode('', $links) . '</ul></details></li>';
    };

    // مدیر واحدِ صرف (مدیر چارت بدون نشانۀ کارشناسی) «کار من»اش «فضای مدیر واحد» است؛
    // صف/تقویم/گزارش‌های عملیاتی برای او شرکت‌پذیر نیستند (تصمیم مالک).
    $unitHeadOnly = function_exists('request_center_is_org_unit_head') && request_center_is_org_unit_head($user);
    $isUnitHead = function_exists('request_center_is_unit_head') && request_center_is_unit_head($user);
    $canAgent = !$unitHeadOnly && (user_can($user, 'ticket.edit') || user_can($user, 'queue.view')
        || user_can($user, 'ticket.view_unit') || user_can($user, 'ticket.view_all'));

    $workLinks = [];
    if (user_can($user, 'ticket.create') || user_can($user, 'ticket.view_own')) {
        $workLinks[] = $submenuLink('request-center.php?workspace=requester', 'فضای درخواست‌دهنده');
    }
    if (user_can($user, 'ticket.create')) {
        $workLinks[] = $submenuLink('request-center.php?page=new-ticket', 'ثبت درخواست جدید');
    }
    $workLinks[] = $submenuLink('request-center.php?page=knowledge', 'دانش‌نامه');
    if ($isUnitHead) {
        $workLinks[] = $submenuLink('request-center.php?workspace=unit', 'فضای مدیر واحد');
    }
    if ($canAgent) {
        $workLinks[] = $submenuLink('request-center.php?workspace=agent', 'فضای کارشناس (صف شخصی)');
    }
    if (!$unitHeadOnly && user_can($user, 'queue.view')) {
        $workLinks[] = $submenuLink('request-center.php?workspace=agent&agent_view=team', 'صف گروهی مجاز');
        $workLinks[] = $submenuLink('request-center.php?page=queue', 'صف عملیاتی');
    }

    $superviseLinks = [];
    if (user_can($user, 'supervisor.panel')) {
        $superviseLinks[] = $submenuLink('request-center.php?workspace=supervisor', 'فضای سوپروایزر');
        $superviseLinks[] = $submenuLink('request-center.php?page=supervisor', 'صف تصمیم‌ها');
    }
    $calendarUrl = null;
    if (user_is_primary_admin($user)) {
        $calendarUrl = 'request-center.php?workspace=admin&calendar=1';
    } elseif (user_can($user, 'supervisor.panel')) {
        $calendarUrl = 'request-center.php?workspace=supervisor&calendar=1';
    } elseif ($canAgent) {
        $calendarUrl = 'request-center.php?workspace=agent&agent_view=mine&calendar=1';
    }
    if ($calendarUrl !== null) {
        $superviseLinks[] = $submenuLink($calendarUrl, 'تقویم برنامه‌ریزی رسیدگی');
    }

    // «تغییر و مشکل» و «OLA» فقط برای نقش‌های کارمندی با کد مربوطه
    if (user_can($user, 'governance.view') && function_exists('is_staff_role') && is_staff_role((string) ($user['role'] ?? ''))) {
        $superviseLinks[] = $submenuLink('request-center.php?page=governance', 'تغییر و مشکل');
    }
    if (user_can($user, 'ola.view')) {
        $superviseLinks[] = $submenuLink('request-center.php?page=ola', 'OLA');
    }

    $reportLinks = [];
    if (!$unitHeadOnly && user_can($user, 'reports.view')) {
        $reportLinks[] = $submenuLink('request-center.php?page=reports', 'گزارش‌ها');
    }
    if (!$unitHeadOnly && user_can($user, 'analytics.view')) {
        $reportLinks[] = $submenuLink('request-center.php?page=analytics', 'تحلیل عملکرد');
    }

    $adminLinks = [];
    if (user_is_primary_admin($user)) {
        $adminLinks[] = $submenuLink('request-center.php?workspace=admin', 'مدیریت مرکز درخواست‌ها');
    }
    if (is_admin_role((string) ($user['role'] ?? '')) || user_is_primary_admin($user)) {
        $adminLinks[] = $submenuLink('request-center.php?page=services', 'کاتالوگ خدمات');
    }

    $menuItems = [
        '<li>' . $menuLink('request-center.php', 'خانه مرکز', 'home') . '</li>',
        $submenu('rc-menu-work', 'کار من', 'ticket', $workLinks),
        $submenu('rc-menu-supervise', 'نظارت و تقویم', 'support', $superviseLinks),
        $submenu('rc-menu-reports', 'گزارش‌ها', 'reports', $reportLinks),
        $submenu('rc-menu-admin', 'مدیریت', 'settings', $adminLinks),
        // «خروج» از مرکز درخواست‌ها کار نمی‌کرد (POST به مسیرهای ماژول می‌خورد به «درخواست نامعتبر است.»).
        // خروج واقعی در داشبورد اصلی سامانه است؛ اینجا فقط راه برگشت ساده می‌گذاریم.
        '<li class="logout"><a class="back-to-system-link" href="index.php">'
            . '<svg class="icon-svg" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M9 14l-4-4 4-4"/><path d="M5 10h9a5 5 0 0 1 0 10h-3"/></svg>'
            . '<span>بازگشت به سامانه</span></a></li>',
    ];

    echo '<nav class="navbar" aria-label="ناوبری مرکز درخواست‌ها"><ul class="menu">'
        . implode('', array_filter($menuItems)) . '</ul></nav>';
}
