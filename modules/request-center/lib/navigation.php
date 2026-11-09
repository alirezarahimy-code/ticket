<?php
declare(strict_types=1);

/**
 * Request Center-only navigation.
 *
 * Kept inside the module so the shared site renderer does not own lifecycle
 * workspace routes or their role-specific menu policy.
 *
 * @param callable(string,string,string):string $menuLink
 * @param callable(string):string $menuIcon
 */
function request_center_render_navigation(array $user, callable $menuLink, callable $menuIcon): void
{
    $menuItems = [
        '<li>' . $menuLink('request-center.php', 'خانه مرکز درخواست‌ها', 'home') . '</li>',
    ];
    if (user_can($user, 'ticket.create') || user_can($user, 'ticket.view_own')) {
        $menuItems[] = '<li>' . $menuLink('request-center.php?workspace=requester', 'فضای درخواست‌دهنده', 'ticket') . '</li>';
    }
    if (user_can($user, 'ticket.edit') || user_can($user, 'queue.view')
        || user_can($user, 'ticket.view_unit') || user_can($user, 'ticket.view_all')) {
        $menuItems[] = '<li>' . $menuLink('request-center.php?workspace=agent', 'صف شخصی کارشناس', 'support') . '</li>';
    }
    if (user_can($user, 'queue.view')) {
        $menuItems[] = '<li>' . $menuLink('request-center.php?workspace=agent&agent_view=team', 'صف گروهی مجاز', 'support') . '</li>';
        $menuItems[] = '<li>' . $menuLink('request-center.php?page=queue', 'صف عملیاتی', 'ticket') . '</li>';
    }
    if (user_can($user, 'supervisor.panel')) {
        $menuItems[] = '<li>' . $menuLink('request-center.php?workspace=supervisor', 'فضای سوپروایزر', 'reports') . '</li>';
        $menuItems[] = '<li>' . $menuLink('request-center.php?page=supervisor', 'صف تصمیم‌ها', 'settings') . '</li>';
    }
    if (user_is_primary_admin($user)) {
        $menuItems[] = '<li>' . $menuLink('request-center.php?workspace=admin', 'مدیریت مرکز درخواست‌ها', 'settings') . '</li>';
        $menuItems[] = '<li>' . $menuLink('request-center.php?workspace=admin&calendar=1', 'تقویم موعدها', 'reports') . '</li>';
    } elseif (user_can($user, 'supervisor.panel')) {
        $menuItems[] = '<li>' . $menuLink('request-center.php?workspace=supervisor&calendar=1', 'تقویم موعدها', 'reports') . '</li>';
    } elseif (user_can($user, 'queue.view')) {
        $menuItems[] = '<li>' . $menuLink('request-center.php?workspace=agent&calendar=1&agent_view=team', 'تقویم موعدها', 'reports') . '</li>';
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
    $menuItems[] = '<li class="logout"><form method="post" class="logout-form">' . csrf_field()
        . '<input type="hidden" name="action" value="logout"><button class="logout-link" type="submit">'
        . $menuIcon('logout') . '<span>خروج</span></button></form></li>';

    echo '<div class="header-tools">' . app_theme_toggle_markup() . '<span class="profile-shortcut">'
        . e((string) ($user['full_name'] ?? $user['username'] ?? '')) . '</span></div>';
    echo '<nav class="navbar" aria-label="ناوبری مرکز درخواست‌ها"><ul class="menu">'
        . implode('', $menuItems) . '</ul></nav>';
}
