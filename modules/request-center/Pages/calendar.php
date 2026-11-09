<?php
declare(strict_types=1);

// محافظ: این فایل فقط از طریق نقاط ورود معتبر (index.php هسته یا request-center.php) بارگذاری می‌شود؛ دسترسی مستقیم قطعاً ممنوع.
if (!defined('APP_ROOT')) {
    http_response_code(404);
    exit;
}

/**
 * تقویم شمسی کارشناسان — فاز ۴ «تقویم کارشناسان و برنامه‌ریزی رسیدگی».
 *
 * این صفحه دو لایهٔ اطلاعاتی را در یک‌جا نشان می‌دهد:
 *   ۱) موعدهای SLA/OLA (خواندنی؛ مبنایش RequestCenterRepository::calendarItems با همان دامنهٔ دسترسی ticket_scope)
 *   ۲) نوبت‌های رسیدگی (کنشی؛ AppointmentRepository — ساخت/لغو/اتمام با تراکنش و قفل)
 *
 * تاریخ‌ها در داخل شمسی و در پایگاه دادهٔ Gregorian ذخیره می‌شوند؛ همهٔ نمایش‌ها با helperهای jalali_* تبدیل می‌شوند.
 */

$canUseCalendar = $workspace === 'agent'
    || ($workspace === 'supervisor' && user_can($user, 'supervisor.panel'))
    || ($workspace === 'admin' && user_is_primary_admin($user));
if (!$canUseCalendar) {
    http_response_code(403);
    exit('دسترسی به تقویم کارشناسان مجاز نیست.');
}

require_once dirname(__DIR__) . '/AppointmentRepository.php';
$rcAppointments = new AppointmentRepository(db());
$rcMissing = !$rcAppointments->tableExists();
$rcElevated = user_can($user, 'supervisor.panel') || user_is_primary_admin($user);
$rcActorId = (int) $user['id'];

header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: same-origin');
header('Cache-Control: private, no-store');

// ——— امروز (شمسی + میلادی) ———
$nowG = new DateTimeImmutable('now');
$todayGStr = $nowG->format('Y-m-d');
[$tjy, $tjm, $tjd] = gregorian_to_jalali((int) $nowG->format('Y'), (int) $nowG->format('n'), (int) $nowG->format('j'));

// ——— پارامتر ماه شمسی ———
$jalaliMonthNames = ['فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور', 'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند'];
$jmonthParam = trim((string) ($_GET['jmonth'] ?? ''));
if ($jmonthParam === '' && isset($_GET['month']) && preg_match('/^(\d{4})-(0[1-9]|1[0-2])$/', (string) $_GET['month'], $gm)) {
    // سازگاری عقب‌رو با لینک‌های قدیمی «?month=YYYY-MM»
    [$jmonthParam] = [sprintf('%04d-%02d', ...array_slice(gregorian_to_jalali((int) $gm[1], (int) $gm[2], 1), 0, 2))];
}
if (!preg_match('/^(\d{4})-(0[1-9]|1[0-2])$/', $jmonthParam, $jmMatch)) {
    $jmonthParam = sprintf('%04d-%02d', $tjy, $tjm);
}
$jy = (int) substr($jmonthParam, 0, 4);
$jm = (int) substr($jmonthParam, 5, 2);
if ($jy < 1300 || $jy > 1500) {
    [$jy, $jm] = [$tjy, $tjm];
    $jmonthParam = sprintf('%04d-%02d', $jy, $jm);
}

$daysInJMonth = static function (int $jy2, int $jm2): int {
    if ($jm2 <= 6) {
        return 31;
    }
    if ($jm2 <= 11) {
        return 30;
    }
    [$sy, $sm, $sd] = jalali_to_gregorian($jy2, 1, 1);
    [$ny, $nm, $nd] = jalali_to_gregorian($jy2 + 1, 1, 1);
    $span = (strtotime(sprintf('%04d-%02d-%02d', $ny, $nm, $nd)) - strtotime(sprintf('%04d-%02d-%02d', $sy, $sm, $sd))) / 86400;
    return (int) $span === 366 ? 30 : 29;
};
$daysInThisMonth = $daysInJMonth($jy, $jm);
[$gsy, $gsm, $gsd] = jalali_to_gregorian($jy, $jm, 1);
[$gey, $gem, $ged] = jalali_to_gregorian($jy, $jm, $daysInThisMonth);
$gFrom = sprintf('%04d-%02d-%02d', $gsy, $gsm, $gsd);
$gTo = sprintf('%04d-%02d-%02d', $gey, $gem, $ged);
$fromTs = $gFrom . ' 00:00:00';
$toTs = $gTo . ' 23:59:59';

$prevJ = $jm === 1 ? sprintf('%04d-12', $jy - 1) : sprintf('%04d-%02d', $jy, $jm - 1);
$nextJ = $jm === 12 ? sprintf('%04d-01', $jy + 1) : sprintf('%04d-%02d', $jy, $jm + 1);
$currentJ = sprintf('%04d-%02d', $tjy, $tjm);
$monthLabel = $jalaliMonthNames[$jm - 1] . ' ' . $jy;
$monthLabelEn = $gFrom . ' → ' . $gTo;

$view = ($_GET['view'] ?? 'month') === 'agenda' ? 'agenda' : 'month';
$kindFilter = trim((string) ($_GET['kind'] ?? ''));
if ($kindFilter !== '' && $kindFilter !== 'sla' && !isset(AppointmentRepository::KINDS[$kindFilter])) {
    $kindFilter = '';
}
$showDeadlines = ($kindFilter === '' || $kindFilter === 'sla');
$showAppointments = ($kindFilter === '' || $kindFilter !== 'sla');
$calendarLink = static function (string $jmonth, string $view, string $kind) use ($workspace, $agentView): string {
    $qs = 'workspace=' . rawurlencode($workspace) . '&calendar=1&agent_view=' . rawurlencode($agentView)
        . '&jmonth=' . rawurlencode($jmonth) . '&view=' . rawurlencode($view);
    if ($kind !== '') {
        $qs .= '&kind=' . rawurlencode($kind);
    }
    return 'request-center.php?' . $qs;
};
$calendarTicketUrl = static function (int $ticketId) use ($workspace, $agentView, $jmonthParam, $view, $kindFilter): string {
    $params = [
        'page' => 'ticket',
        'id' => max(0, $ticketId),
        'workspace' => $workspace,
        'calendar' => 1,
        'jmonth' => $jmonthParam,
        'view' => $view,
    ];
    if ($workspace === 'agent') {
        $params['agent_view'] = $agentView;
    }
    if ($kindFilter !== '') {
        $params['kind'] = $kindFilter;
    }
    return 'request-center.php?' . http_build_query($params);
};
$monthNavLink = static fn(string $jmonth): string => $calendarLink($jmonth, $view, $kindFilter);
$viewLink = static fn(string $v): string => $calendarLink($jmonthParam, $v, $kindFilter);
$kindLink = static fn(string $k): string => $calendarLink($jmonthParam, $view, $k);

// ——— بارگذاری رویدادهای موعد (خواندنی) ———
$calendarFilters = [];
if ($workspace === 'agent' && $agentView === 'mine') {
    $calendarFilters['assigned_to'] = $rcActorId;
}
$calendarRows = $showDeadlines ? $repo->calendarItems($user, $gFrom, $gTo, $calendarFilters) : [];

$deadlineMeta = [
    ['field' => 'due_at', 'label' => 'SLA'],
    ['field' => 'ola_response_due_at', 'label' => 'OLA پاسخ'],
    ['field' => 'ola_due_at', 'label' => 'OLA حل'],
];
$eventsByDay = [];
$deadlineCount = 0;
foreach ($calendarRows as $row) {
    foreach ($deadlineMeta as $deadline) {
        $value = trim((string) ($row[$deadline['field']] ?? ''));
        if ($value === '') {
            continue;
        }
        $eventDate = substr($value, 0, 10);
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $eventDate)) {
            continue;
        }
        $paused = !empty($row['sla_paused_at']);
        $eventsByDay[$eventDate][] = [
            'type' => 'deadline',
            'time' => substr($value, 11, 5),
            'ts' => strtotime($value) ?: 0,
            'label' => $deadline['label'],
            'id' => (int) $row['id'],
            'ticket_no' => (string) ($row['ticket_no'] ?? ''),
            'subject' => (string) ($row['subject'] ?? ''),
            'assignee' => (string) ($row['assignee_name'] ?? ''),
            'paused' => $paused,
            'overdue' => !$paused && strtotime($value) < time(),
        ];
        $deadlineCount++;
    }
}

// ——— بارگذاری نوبت‌های رسیدگی (کنشی) ———
$apptScopeId = $rcElevated ? null : $rcActorId;
$appointmentRows = (!$rcMissing && $showAppointments)
    ? $rcAppointments->listRange($apptScopeId, $fromTs, $toTs, $kindFilter !== '' && $kindFilter !== 'sla' ? ['kind' => $kindFilter] : [])
    : [];
foreach ($appointmentRows as $appt) {
    $start = (string) $appt['starts_at'];
    $eventDate = substr($start, 0, 10);
    $endDate = substr((string) $appt['ends_at'], 0, 10);
    if ($eventDate === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $eventDate)) {
        continue;
    }
    $day = $eventDate;
    $kindLabel = AppointmentRepository::KINDS[(string) $appt['kind']] ?? (string) $appt['kind'];
    $eventsByDay[$day][] = [
        'type' => 'appointment',
        'time' => substr($start, 11, 5),
        'ts' => strtotime($start) ?: 0,
        'end_time' => substr((string) $appt['ends_at'], 11, 5),
        'label' => $kindLabel,
        'kind' => (string) $appt['kind'],
        'id' => (int) $appt['id'],
        'ticket_id' => (int) $appt['ticket_id'],
        'ticket_no' => (string) ($appt['ticket_no'] ?? ''),
        'subject' => (string) ($appt['subject'] ?? ''),
        'assignee' => (string) ($appt['assignee_name'] ?? ''),
        'assignee_id' => (int) $appt['assignee_id'],
        'status' => (string) $appt['status'],
        'spans_multi_day' => $endDate !== $eventDate,
    ];
}

foreach ($eventsByDay as &$dayEvents) {
    usort($dayEvents, static fn(array $a, array $b): int => $a['ts'] <=> $b['ts']);
}
unset($dayEvents);

$counts = $rcMissing
    ? ['today' => 0, 'active' => 0, 'overdue' => 0, 'done' => 0]
    : $rcAppointments->countsForRange($apptScopeId, $fromTs, $toTs, $nowG->format('Y-m-d H:i:s'));
$totalEventsShown = 0;
foreach ($eventsByDay as $dayEvents) {
    $totalEventsShown += count($dayEvents);
}
$eventsToday = count($eventsByDay[$todayGStr] ?? []);

// ——— تعطیلات رسمی در این بازه ———
$holidayMap = [];
$rcHolidaysUnavailable = false;
try {
    $holStmt = db()->prepare('SELECT holiday_date, title FROM holidays WHERE is_active = 1 AND holiday_date BETWEEN ? AND ? ORDER BY holiday_date');
    $holStmt->execute([$gFrom, $gTo]);
    foreach ($holStmt->fetchAll(PDO::FETCH_ASSOC) ?: [] as $holRow) {
        $holidayMap[(string) $holRow['holiday_date']] = (string) $holRow['title'];
    }
} catch (Throwable $exception) {
    if (function_exists('activity_log')) {
        try {
            activity_log([
                'action_code' => 'rc_holidays_lookup_failed',
                'module' => 'request-center',
                'result' => 'warning',
                'meta' => ['error' => $exception->getMessage(), 'surface' => 'calendar'],
            ]);
        } catch (Throwable) {}
    }
    $rcHolidaysUnavailable = true;
    $holidayMap = [];
}

// ——— ساعت کاری/تعطیل هفتگی (ISO-8601: ۵شنبه و جمعه = ۴ و ۵) ———
$business = function_exists('sla_business_hours') ? sla_business_hours() : ['start' => 8, 'end' => 16, 'weekend' => [4, 5]];
$weekendIso = array_map('intval', (array) ($business['weekend'] ?? [4, 5]));

// ——— ظرفیت تیم (فقط مدیر) یا خلاصهٔ شخصی ———
$capacityRows = [];
if (!$rcMissing && $rcElevated) {
    $capacityRows = $rcAppointments->capacitySummary($fromTs, $toTs, $nowG->format('Y-m-d H:i:s'));
}
$capMax = 1;
foreach ($capacityRows as $capRow) {
    $capMax = max($capMax, (int) $capRow['total']);
}
$aliasStaff = $rcElevated && !$rcMissing ? $rcAppointments->assignableStaff(300) : [];

// ——— ظرفیت امروز (سقف روزانهٔ کارشناس/دایرهٔ تیم) — فقط نمای مدیر ———
$todayLoad = ['per_assignee' => [], 'per_dept' => []];
$viewerDeptId = 0;
$viewerTeamCap = 0;
$viewerTeamUsed = 0;
if (!$rcMissing && $rcElevated) {
    $todayLoad = $rcAppointments->dailyLoadSnapshot($nowG->format('Y-m-d H:i:s'));
    try {
        $vd = db()->prepare('SELECT department_id FROM users WHERE id = ? LIMIT 1');
        $vd->execute([$rcActorId]);
        $viewerDeptId = (int) ($vd->fetchColumn() ?: 0);
    } catch (Throwable) {
        $viewerDeptId = 0;
    }
    if ($viewerDeptId > 0) {
        $viewerTeamCap = $rcAppointments->teamDailyCapacity($viewerDeptId);
        $viewerTeamUsed = (int) ($todayLoad['per_dept'][$viewerDeptId] ?? 0);
    }
}

// ——— نوبت‌های امروز برای تایم‌لاین کوچک ———
$todayGlance = $eventsByDay[$todayGStr] ?? [];

$plannedThisMonth = 0;
foreach ($appointmentRows as $appt) {
    if ((string) $appt['status'] === AppointmentRepository::STATUS_PLANNED) {
        $plannedThisMonth++;
    }
}

// ——— کمک‌گرهای نمایش ———
$weekdayOrder = ['شنبه', 'یکشنبه', 'دوشنبه', 'سه‌شنبه', 'چهارشنبه', 'پنجشنبه', 'جمعه'];
$weekdayLongFa = static fn(int $isoDay): string => [1 => 'دوشنبه', 2 => 'سه‌شنبه', 3 => 'چهارشنبه', 4 => 'پنجشنبه', 5 => 'جمعه', 6 => 'شنبه', 7 => 'یکشنبه'][$isoDay] ?? '';

$avatarInitials = static function (?string $name): string {
    $name = trim((string) $name);
    if ($name === '') {
        return '؟';
    }
    $parts = preg_split('/\s+/u', $name) ?: [];
    $first = mb_substr((string) ($parts[0] ?? ''), 0, 1, 'UTF-8');
    $last = count($parts) > 1 ? mb_substr((string) end($parts), 0, 1, 'UTF-8') : '';
    return $first . $last;
};
/** چهره‌ٔ فارسی اعداد برای خوانایی بیشتر در نسخهٔ فارسی (فقط متن‌های نمایش) */
$faDigits = static fn(string $value): string => strtr($value, ['0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴', '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹']);

$avatarColor = static function (int $seed): string {
    $palette = ['#1e4f8f', '#0e7a6d', '#6d3bc2', '#b45309', '#0f766e', '#9333ea', '#c2410c', '#1d4ed8'];
    return $palette[abs($seed) % count($palette)];
};

$rcFlash = trim((string) ($_GET['appointments'] ?? ''));
$rcReason = trim((string) ($_GET['reason'] ?? ''));
$rcFlashMessage = match ($rcFlash) {
    'created'   => 'نوبت رسیدگی با موفقیت ثبت شد.',
    'cancelled' => 'نوبت لغو شد.',
    'completed' => 'نوبت «انجام‌شده» علامت خورد.',
    'error'     => 'خطا: ' . ($rcReason !== '' ? $rcReason : 'عملیات انجام نشد.'),
    default     => '',
};
$rcFlashClass = $rcFlash === 'error' ? 'err' : 'ok';

$todayJalaliLabel = $jalaliMonthNames[$tjm - 1] . ' ' . $tjd . '، ' . $weekdayLongFa((int) $nowG->format('N')) . ' ' . $tjy;
?>
<!doctype html>
<html lang="fa" dir="rtl">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>تقویم شمسی کارشناسان — مرکز درخواست‌ها</title>
<link rel="stylesheet" href="modules/request-center/Assets/request-center.css">
</head>
<body>
<div class="rc-shell">
  <header class="rc-topbar">
    <div class="rc-wrap rc-topbar-in" style="display:flex;align-items:center;justify-content:space-between;gap:14px;padding:12px 22px">
      <div class="rc-brandbox">
        <div class="rc-logomark" aria-hidden="true">📅</div>
        <div>
          <b>تقویم کارشناسان</b>
          <small>مرکز درخواست‌ها · SLA/OLA و نوبت‌های رسیدگی</small>
        </div>
      </div>
      <div style="display:flex;align-items:center;gap:10px;min-width:0">
        <div class="rc-userchip" title="<?= $escape((string) ($user['full_name'] ?? $user['username'] ?? '')) ?>">
          <span class="rc-avatar" style="background:<?= $avatarColor((int) ($user['id'] ?? 1)) ?>"><?= $escape($avatarInitials((string) ($user['full_name'] ?? $user['username'] ?? ''))) ?></span>
          <span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= $escape((string) ($user['full_name'] ?? $user['username'] ?? '')) ?></span>
        </div>
        <a class="rc-link" href="request-center.php?workspace=<?= $escape($workspace) ?>">بازگشت به فضای کاری ←</a>
      </div>
    </div>
  </header>

  <main class="rc-wrap">
    <!-- ─── سربرگ و وضعیت امروز ─── -->
    <div class="rc-hero">
      <h1>تقویم شمسی برنامه‌ریزی رسیدگی</h1>
      <p>امروز <strong><?= $escape($faDigits($todayJalaliLabel)) ?></strong> است · موعدهای SLA/OLA به‌صورت خواندنی، و نوبت‌های رسیدگی به‌صورت عملیاتی از همین صفحه مدیریت می‌شوند. فهرست ماه: <span dir="ltr"><?= $escape($monthLabelEn) ?></span></p>
    </div>

    <?php if ($rcFlashMessage !== ''): ?>
      <div class="rc-alert <?= $escape($rcFlashClass) ?>" role="status"><?= $escape($rcFlashMessage) ?></div>
    <?php endif; ?>
    <?php if ($rcMissing): ?>
      <div class="rc-alert warn" role="alert">جدول <code>request_center_appointments</code> هنوز ایجاد نشده است. مهاجرت <code>migrations/035-request-center-appointments.sql</code> را اجرا کنید؛ تا آن زمان فقط موعدها نمایش داده می‌شوند و ثبت نوبت غیرفعال است.</div>
    <?php endif; ?>

    <!-- ─── شاخص‌های بالای صفحه ─── -->
    <div class="rc-kpis" aria-label="شاخص‌های بازه">
      <div class="rc-kpi today"><div class="rc-kpi-label">رویدادها در این ماه (با فیلتر فعلی)</div><div class="rc-kpi-value"><?= $escape($faDigits(number_format($totalEventsShown))) ?></div></div>
      <div class="rc-kpi"><div class="rc-kpi-label">رویدادهای امروز</div><div class="rc-kpi-value"><?= $escape($faDigits(number_format($eventsToday))) ?></div></div>
      <div class="rc-kpi warn"><div class="rc-kpi-label">نوبت‌های عقب‌افتاده (معوق)</div><div class="rc-kpi-value"><?= $escape($faDigits(number_format($counts['overdue']))) ?></div></div>
      <div class="rc-kpi"><div class="rc-kpi-label">نوبت‌های انجام‌شده در این ماه</div><div class="rc-kpi-value"><?= $escape($faDigits(number_format($counts['done']))) ?></div></div>
    </div>

    <!-- ─── نوار ابزار: جابه‌جایی ماه، نما، فیلتر نوع ─── -->
    <div class="rc-toolbar">
      <div class="rc-monthnav">
        <a class="rc-iconbtn" href="<?= $escape($monthNavLink($prevJ)) ?>" aria-label="ماه قبل">‹</a>
        <strong><?= $escape($faDigits($monthLabel)) ?></strong>
        <a class="rc-iconbtn" href="<?= $escape($monthNavLink($nextJ)) ?>" aria-label="ماه بعد">›</a>
        <a class="rc-today-btn" href="<?= $escape($calendarLink($currentJ, $view, $kindFilter)) ?>">این ماه / امروز</a>
      </div>
      <nav class="rc-seg" aria-label="نوع نما">
        <a class="<?= $view === 'month' ? 'active' : '' ?>" href="<?= $escape($viewLink('month')) ?>">نمای ماهانه</a>
        <a class="<?= $view === 'agenda' ? 'active' : '' ?>" href="<?= $escape($viewLink('agenda')) ?>">فهرست روزها</a>
      </nav>
    </div>
    <div class="rc-kindchips" role="group" aria-label="فیلتر نوع رویداد">
      <a class="rc-filterchip<?= $kindFilter === '' ? ' active' : '' ?>" href="<?= $escape($kindLink('')) ?>">همهٔ رویدادها</a>
      <a class="rc-filterchip<?= $kindFilter === 'sla' ? ' active' : '' ?>" href="<?= $escape($kindLink('sla')) ?>"><i class="rc-dot k-work"></i>موعدهای SLA / OLA</a>
      <?php foreach (AppointmentRepository::KINDS as $kindKey => $kindLabel): ?>
        <a class="rc-filterchip<?= $kindFilter === $kindKey ? ' active' : '' ?>" href="<?= $escape($kindLink($kindKey)) ?>"><i class="rc-dot k-<?= $escape($kindKey) ?>"></i><?= $escape($kindLabel) ?></a>
      <?php endforeach; ?>
    </div>

    <div class="rc-board">
      <!-- ══════════ ستون اصلی: تقویم ══════════ -->
      <div>
        <?php if ($view === 'month'): ?>
        <section class="rc-panel" aria-label="تقویم ماهانه">
          <div class="rc-panel-title">
            <h2>نمای ماهانه — <?= $escape($faDigits($monthLabel)) ?></h2>
            <div class="rc-legend" aria-hidden="true">
              <span><i class="rc-dot k-work"></i>SLA/OLA</span>
              <span><i class="rc-dot k-followup"></i>نوبت رسیدگی</span>
              <span style="color:var(--rc-red)">● قرمز = عقب‌افتاده / تعطیل</span>
            </div>
          </div>
          <div class="rc-panel-body" style="padding:12px">
            <div class="rc-grid" role="grid" aria-label="روزهای <?= $escape($monthLabel) ?>">
              <?php foreach ($weekdayOrder as $weekday): ?><div class="rc-weekday" role="columnheader"><?= $escape($weekday) ?></div><?php endforeach; ?>
              <?php
              $firstIso = (int) (new DateTimeImmutable($gFrom))->format('N'); // 1=Mon..7=Sun
              $startOffset = ($firstIso + 1) % 7; // خانهٔ شنبه = ۰
              $cellCount = (int) (ceil(($startOffset + $daysInThisMonth) / 7) * 7);
              for ($cell = 0; $cell < $cellCount; $cell++):
                  $jalaliDay = $cell - $startOffset + 1;
                  $inMonth = $jalaliDay >= 1 && $jalaliDay <= $daysInThisMonth;
                  if ($inMonth) {
                      [$gy2, $gm2, $gd2] = jalali_to_gregorian($jy, $jm, $jalaliDay);
                      $cellDate = sprintf('%04d-%02d-%02d', $gy2, $gm2, $gd2);
                  } else {
                      $cellDate = $jalaliDay < 1
                          ? (new DateTimeImmutable($gFrom))->modify(($jalaliDay - 1) . ' days')->format('Y-m-d')
                          : (new DateTimeImmutable($gTo))->modify(($jalaliDay - $daysInThisMonth) . ' days')->format('Y-m-d');
                  }
                  [$cellJy, $cellJm, $cellJd] = gregorian_to_jalali((int) date('Y', strtotime($cellDate)), (int) date('n', strtotime($cellDate)), (int) date('j', strtotime($cellDate)));
                  $cellIso = (int) (new DateTimeImmutable($cellDate))->format('N');
                  $isToday = $cellDate === $todayGStr;
                  $isWeekend = in_array($cellIso, $weekendIso, true);
                  $holidayTitle = $holidayMap[$cellDate] ?? null;
                  $cellEvents = $eventsByDay[$cellDate] ?? [];
                  $cellClasses = 'rc-day';
                  if (!$inMonth) { $cellClasses .= ' outside'; }
                  if ($isWeekend) { $cellClasses .= ' weekend-cell'; }
                  if ($isToday) { $cellClasses .= ' today'; }
                  if ($holidayTitle !== null) { $cellClasses .= ' holiday'; }
              ?>
              <div class="<?= $cellClasses ?>" role="gridcell"<?= $holidayTitle ? ' title="' . $escape($holidayTitle) . '"' : '' ?>>
                <div class="rc-daynum"><?= $escape($faDigits((string) $cellJd)) ?><?php if (!$inMonth): ?><span style="font-weight:400;color:#aab4c5;font-size:10px"> <?= $escape($jalaliMonthNames[$cellJm - 1]) ?></span><?php endif; ?></div>
                <?php if ($holidayTitle !== null): ?><span class="rc-hol-tag"><?= $escape($holidayTitle) ?></span><?php endif; ?>
                <?php foreach (array_slice($cellEvents, 0, 3) as $event): ?>
                  <?php if ($event['type'] === 'deadline'): ?>
                    <a class="rc-event sla<?= $event['overdue'] ? ' overdue' : '' ?><?= $event['paused'] ? ' paused' : '' ?>" href="<?= $escape($calendarTicketUrl((int) $event['id'])) ?>" title="<?= $escape($event['label']) ?> · <?= $escape($event['ticket_no'] !== '' ? $event['ticket_no'] : ('#' . $event['id'])) ?> — <?= $escape($event['subject']) ?>">
                      <b><?= $escape($event['time']) ?> · <?= $escape($event['label']) ?></b>
                      <?= $escape($event['ticket_no'] !== '' ? $event['ticket_no'] : ('#' . $event['id'])) ?>
                    </a>
                  <?php else: ?>
                    <a class="rc-event <?= $escape((string) $event['kind']) ?><?= $event['status'] === 'done' ? ' done' : ($event['status'] === 'cancelled' ? ' paused' : '') ?>" href="<?= $escape($calendarTicketUrl((int) $event['ticket_id'])) ?>" title="<?= $escape($event['label']) ?> · <?= $escape($event['time']) ?> تا <?= $escape((string) $event['end_time']) ?> — <?= $escape($event['subject']) ?><?= $rcElevated && $event['assignee'] !== '' ? ' · ' . $escape($event['assignee']) : '' ?>">
                      <b><?= $escape($event['time']) ?> · <?= $escape($event['label']) ?></b>
                      <?= $escape($event['ticket_no'] !== '' ? $event['ticket_no'] : ('درخواست #' . $event['ticket_id'])) ?>
                    </a>
                  <?php endif; ?>
                <?php endforeach; ?>
                <?php if (count($cellEvents) > 3): ?>
                  <a class="rc-more" href="<?= $escape($calendarLink($jmonthParam, 'agenda', $kindFilter)) ?>">+<?= count($cellEvents) - 3 ?> رویداد دیگر…</a>
                <?php elseif ($cellEvents === [] && !$isToday && !$holidayTitle && $inMonth): ?>
                  <span class="rc-more" style="opacity:.55">—</span>
                <?php endif; ?>
              </div>
              <?php endfor; ?>
            </div>
            <p class="rc-footer" style="margin:10px 4px 0;text-align:right">ستون‌ها به ترتیب «شنبه تا جمعه» هستند · روزهای «تعطیل/پنجشنبه‌وجمعه» از جدول <code>holidays</code> آمده و با رنگ متمایزند · خوشهٔ قرمز = موعد گذشته یا روز تعطیل.</p>
          </div>
        </section>
        <?php else: /* نمای فهرست روزها */ ?>
        <section class="rc-panel" aria-label="فهرست رویدادهای روزها">
          <div class="rc-panel-title">
            <h2>فهرست روزها — <?= $escape($faDigits($monthLabel)) ?></h2>
            <a class="rc-link" href="<?= $escape($calendarLink($currentJ, 'agenda', '')) ?>">برو به ماه جاری</a>
          </div>
          <div class="rc-panel-body">
            <?php
            $agendaDays = $eventsByDay;
            ksort($agendaDays);
            $agendaShown = 0;
            foreach ($agendaDays as $day => $dayEvents):
                $agendaShown += count($dayEvents);
                [$jy2, $jm2, $jd2] = gregorian_to_jalali((int) date('Y', strtotime($day)), (int) date('n', strtotime($day)), (int) date('j', strtotime($day)));
                $dayLabel = $jd2 . ' ' . $jalaliMonthNames[$jm2 - 1] . ' ' . $jy2 . ' · ' . $weekdayLongFa((int) date('N', strtotime($day)));
                $dayHoliday = $holidayMap[$day] ?? null;
            ?>
            <div style="margin:14px 0 4px;padding-bottom:6px;border-bottom:1px dashed var(--rc-line);display:flex;align-items:baseline;justify-content:space-between;gap:12px;flex-wrap:wrap">
              <strong style="font-size:14px"><?= $escape($faDigits($dayLabel)) ?></strong>
              <span style="font-size:11px;color:var(--rc-muted)"><?= count($dayEvents) ?> رویداد<?= $dayHoliday ? ' · <span style="color:var(--rc-red);font-weight:700">' . $escape($dayHoliday) . '</span>' : '' ?></span>
            </div>
            <?php foreach ($dayEvents as $event): ?>
              <?php if ($event['type'] === 'deadline'): ?>
                <a class="rc-event sla<?= $event['overdue'] ? ' overdue' : '' ?><?= $event['paused'] ? ' paused' : '' ?>" style="display:flex;justify-content:space-between;gap:10px;font-size:12px;-webkit-line-clamp:1;padding:7px 10px" href="<?= $escape($calendarTicketUrl((int) $event['id'])) ?>">
                  <span><b><?= $escape($event['time']) ?> · <?= $escape($event['label']) ?></b><br><?= $escape($event['ticket_no'] !== '' ? $event['ticket_no'] : ('#' . $event['id'])) ?> — <?= $escape($event['subject']) ?></span>
                  <span style="color:var(--rc-muted);font-size:11px;white-space:nowrap"><?= $escape($event['assignee'] ?: 'بدون مسئول') ?></span>
                </a>
              <?php else: ?>
                <div class="rc-slot-card" style="display:flex;justify-content:space-between;gap:12px;align-items:center;flex-wrap:wrap">
                  <div style="min-width:0">
                    <b style="font-size:12.5px"><?= $escape($event['time']) ?> تا <?= $escape((string) $event['end_time']) ?> · <?= $escape($event['label']) ?></b><br>
                    <a href="<?= $escape($calendarTicketUrl((int) $event['ticket_id'])) ?>" style="color:var(--rc-brand-strong);font-weight:700"><?= $escape($event['ticket_no'] !== '' ? $event['ticket_no'] : ('درخواست #' . $event['ticket_id'])) ?></a>
                    <span style="color:var(--rc-muted);font-size:11px"> · <?= $escape($event['subject']) ?></span>
                    <?php if ($rcElevated && $event['assignee'] !== ''): ?><span style="color:var(--rc-muted);font-size:11px"> · <?= $escape($event['assignee']) ?></span><?php endif; ?>
                    <?php if ($event['status'] === 'done'): ?><span style="color:var(--rc-green);font-size:11px;font-weight:700"> · انجام‌شده</span><?php endif; ?>
                  </div>
                  <?php if ($event['status'] === 'planned'): ?>
                  <div class="rc-slot-actions" style="margin:0">
                    <form method="post" action="request-center.php?workspace=<?= $escape($workspace) ?>&amp;calendar=1&amp;agent_view=<?= $escape($agentView) ?>"><?= csrf_field() ?><input type="hidden" name="rc_appointment_complete" value="1"><input type="hidden" name="appointment_id" value="<?= (int) $event['id'] ?>"><button class="rc-mini-btn" type="submit">انجام شد</button></form>
                    <form method="post" action="request-center.php?workspace=<?= $escape($workspace) ?>&amp;calendar=1&amp;agent_view=<?= $escape($agentView) ?>"><?= csrf_field() ?><input type="hidden" name="rc_appointment_cancel" value="1"><input type="hidden" name="appointment_id" value="<?= (int) $event['id'] ?>"><button class="rc-mini-btn danger" type="submit">لغو</button></form>
                  </div>
                  <?php endif; ?>
                </div>
              <?php endif; ?>
            <?php endforeach; ?>
            <?php endforeach; ?>
            <?php if ($agendaShown === 0): ?>
              <div class="rc-empty">رویدادی برای این ماه پیدا نشد. فیلتر نوع رویداد را تغییر دهید یا ماه دیگری را انتخاب کنید.</div>
            <?php endif; ?>
          </div>
        </section>
        <?php endif; ?>
      </div>

      <!-- ══════════ ستون کناری ══════════ -->
      <aside class="rc-aside">
        <!-- ثبت نوبت رسیدگی -->
        <?php if (!$rcMissing): ?>
        <section class="rc-panel rc-form" aria-label="ثبت نوبت رسیدگی">
          <div class="rc-panel-body">
            <h3>ثبت نوبت رسیدگی <small>وضعیت درخواست و زمان‌های SLA/OLA تغییر نمی‌کنند؛ فقط زمان‌بندی کار انجام می‌شود.</small></h3>
            <form method="post" action="request-center.php?workspace=<?= $escape($workspace) ?>&amp;calendar=1&amp;agent_view=<?= $escape($agentView) ?>">
              <?= csrf_field() ?><input type="hidden" name="rc_appointment_create" value="1">
              <label class="rc-field"><span>شناسهٔ درخواست <em>عدد</em></span><input class="rc-input" type="number" name="ticket_id" min="1" step="1" required placeholder="مثلاً 123"></label>
              <div class="rc-twofold">
                <label class="rc-field"><span>شروع</span><input class="rc-input" type="datetime-local" name="starts_at" required><small style="display:block;margin-top:4px;color:var(--rc-muted);font-size:11px">تا ۶۰ دقیقهٔ گذشته نیز قابل ثبت است؛ زودتر از آن ثبت نمی‌شود.</small></label>
                <label class="rc-field"><span>پایان</span><input class="rc-input" type="datetime-local" name="ends_at" required></label>
              </div>
              <label class="rc-field"><span>نوع نوبت</span><select class="rc-select" name="kind"><?php foreach (AppointmentRepository::KINDS as $kindKey => $kindLabel): ?><option value="<?= $escape($kindKey) ?>"><?= $escape($kindLabel) ?></option><?php endforeach; ?></select></label>
              <?php if ($rcElevated): ?>
                <label class="rc-field"><span>کارشناس مسئول <em>خالی = خود شما</em></span>
                  <select class="rc-select" name="assignee_id">
                    <option value="0">خود من</option>
                    <?php foreach ($aliasStaff as $staff): ?>
                      <?php if ((int) $staff['id'] === $rcActorId) { continue; } ?>
                      <option value="<?= (int) $staff['id'] ?>"><?= $escape((string) $staff['full_name']) ?></option>
                    <?php endforeach; ?>
                  </select>
                </label>
                <label class="rc-check"><input type="checkbox" name="allow_outside_business" value="1"> ثبت حتی بیرون از تقویم کاری/ساعات کاری/تعطیلات (فقط مدیر)</label>
              <?php endif; ?>
              <label class="rc-field"><span>یادداشت <em>اختیاری · حداکثر ۵۰۰ نویسه</em></span><textarea class="rc-textarea" name="note" rows="2" maxlength="500" placeholder="جزئیات کوتاه…"></textarea></label>
              <button class="rc-primary" type="submit">ثبت نوبت رسیدگی</button>
              <p class="rc-hint">ثبت نوبت تنها روی <strong>زمان‌بندی خودتان</strong> اثر می‌گذارد؛ تداخل نوبت فعال هم‌زمان برای همان کارشناس پذیرفته نمی‌شود. روزهای تعطیل و پنجشنبه‌وجمعه به‌صورت پیش‌فرض مسدودند.</p>
            </form>
          </div>
        </section>
        <?php endif; ?>

        <!-- امروز در یک نگاه -->
        <section class="rc-panel" aria-label="امروز در یک نگاه">
          <div class="rc-panel-title"><h2>امروز در یک نگاه</h2><span class="rc-kpi-label"><?= $escape($faDigits($todayJalaliLabel)) ?></span></div>
          <div class="rc-panel-body">
            <?php if ($todayGlance === []): ?>
              <div class="rc-empty">امروز رویدادی ثبت نشده است. برای امروز نوبت بزنید یا از منوی بالا ماه دیگر را ببینید.</div>
            <?php else: ?>
              <div class="rc-timeline">
                <?php foreach ($todayGlance as $event): ?>
                  <div class="rc-slot <?= $escape($event['type'] === 'appointment' ? (string) $event['kind'] : 'sla') ?><?= $event['type'] === 'appointment' && $event['status'] === 'done' ? ' done' : '' ?>">
                    <time><?= $escape($event['time']) ?><?= isset($event['end_time']) ? ' – ' . $escape((string) $event['end_time']) : '' ?></time>
                    <div class="rc-slot-card">
                      <?php if ($event['type'] === 'deadline'): ?>
                        <div style="font-size:12px"><strong><?= $escape($event['label']) ?></strong> — <a style="color:var(--rc-brand-strong);font-weight:700" href="<?= $escape($calendarTicketUrl((int) $event['id'])) ?>"><?= $escape($event['ticket_no'] !== '' ? $event['ticket_no'] : ('#' . $event['id'])) ?></a></div>
                        <div style="font-size:11px;color:var(--rc-muted)"><?= $escape($event['subject']) ?> · <?= $escape($event['assignee'] ?: 'بدون مسئول') ?></div>
                      <?php else: ?>
                        <div style="font-size:12px"><strong><?= $escape($event['label']) ?></strong> — <a style="color:var(--rc-brand-strong);font-weight:700" href="<?= $escape($calendarTicketUrl((int) $event['ticket_id'])) ?>"><?= $escape($event['ticket_no'] !== '' ? $event['ticket_no'] : ('#' . $event['ticket_id'])) ?></a></div>
                        <div style="font-size:11px;color:var(--rc-muted)"><?= $escape($event['subject']) ?></div>
                        <?php if ($event['status'] === 'planned'): ?>
                          <div class="rc-slot-actions">
                            <form method="post" action="request-center.php?workspace=<?= $escape($workspace) ?>&amp;calendar=1&amp;agent_view=<?= $escape($agentView) ?>"><?= csrf_field() ?><input type="hidden" name="rc_appointment_complete" value="1"><input type="hidden" name="appointment_id" value="<?= (int) $event['id'] ?>"><button class="rc-mini-btn" type="submit">انجام شد</button></form>
                            <form method="post" action="request-center.php?workspace=<?= $escape($workspace) ?>&amp;calendar=1&amp;agent_view=<?= $escape($agentView) ?>"><?= csrf_field() ?><input type="hidden" name="rc_appointment_cancel" value="1"><input type="hidden" name="appointment_id" value="<?= (int) $event['id'] ?>"><button class="rc-mini-btn danger" type="submit">لغو</button></form>
                          </div>
                        <?php endif; ?>
                      <?php endif; ?>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
        </section>

        <!-- نوبت‌های آیندهٔ ۱۴ روز -->
        <?php if (!$rcMissing): ?>
        <?php
        $upcomingAppointments = $rcAppointments->listRange(
            $apptScopeId,
            $nowG->format('Y-m-d H:i:s'),
            (new DateTimeImmutable('+14 days'))->format('Y-m-d H:i:s'),
            ['status' => AppointmentRepository::STATUS_PLANNED]
        );
        $upcomingAppointments = array_slice($upcomingAppointments, 0, 6);
        ?>
        <section class="rc-panel" aria-label="نوبت‌های آینده">
          <div class="rc-panel-title"><h2>نوبت‌های آیندهٔ ۱۴ روز</h2><span class="rc-kpi-label"><?= $escape($faDigits((string) count($upcomingAppointments))) ?> مورد اول</span></div>
          <div class="rc-panel-body">
            <?php if ($upcomingAppointments === []): ?>
              <div class="rc-empty">نوبت برنامه‌ریزی‌شدهٔ آینده‌ای ثبت نشده است.</div>
            <?php else: ?>
              <?php foreach ($upcomingAppointments as $upcoming):
                  $upStart = (string) $upcoming['starts_at'];
                  $upEnd = (string) $upcoming['ends_at'];
                  $upKind = AppointmentRepository::KINDS[(string) $upcoming['kind']] ?? (string) $upcoming['kind'];
              ?>
              <div style="display:flex;gap:8px;align-items:center;justify-content:space-between;border:1px solid var(--rc-line-soft);border-radius:var(--rc-radius-sm);padding:7px 9px;margin:6px 0">
                <div style="min-width:0">
                  <div style="font-size:12px;font-weight:800"><?= $escape($faDigits(jalali_date($upStart, false))) ?> · <?= $escape($faDigits(substr($upStart, 11, 5))) ?> تا <?= $escape($faDigits(substr($upEnd, 11, 5))) ?></div>
                  <div style="font-size:11px;color:var(--rc-muted)"><?= $escape($upKind) ?> · <a style="color:var(--rc-brand-strong);font-weight:700" href="<?= $escape($calendarTicketUrl((int) $upcoming['ticket_id'])) ?>"><?= $escape($upcoming['ticket_no'] !== '' ? (string) $upcoming['ticket_no'] : ('#' . (int) $upcoming['ticket_id'])) ?></a><?php if ($rcElevated && !empty($upcoming['assignee_name'])): ?> · <?= $escape((string) $upcoming['assignee_name']) ?><?php endif; ?></div>
                </div>
                <div class="rc-slot-actions" style="margin:0;flex:0 0 auto">
                  <form method="post" action="request-center.php?workspace=<?= $escape($workspace) ?>&amp;calendar=1&amp;agent_view=<?= $escape($agentView) ?>"><?= csrf_field() ?><input type="hidden" name="rc_appointment_complete" value="1"><input type="hidden" name="appointment_id" value="<?= (int) $upcoming['id'] ?>"><button class="rc-mini-btn" type="submit" title="ثبت اتمام">انجام شد</button></form>
                  <form method="post" action="request-center.php?workspace=<?= $escape($workspace) ?>&amp;calendar=1&amp;agent_view=<?= $escape($agentView) ?>"><?= csrf_field() ?><input type="hidden" name="rc_appointment_cancel" value="1"><input type="hidden" name="appointment_id" value="<?= (int) $upcoming['id'] ?>"><button class="rc-mini-btn danger" type="submit" title="لغو نوبت">لغو</button></form>
                </div>
              </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </section>
        <?php endif; ?>

        <!-- ظرفیت تیم یا خلاصهٔ شخصی -->
        <section class="rc-panel" aria-label="ظرفیت این بازه">
          <div class="rc-panel-title"><h2><?= $rcElevated ? 'ظرفیت و تراکم تیم' : 'جمع‌بندی نوبت‌های شما' ?></h2><span class="rc-kpi-label"><?= $escape($monthLabel) ?></span></div>
          <div class="rc-panel-body">
            <?php if ($rcElevated): ?>
              <?php if ($capacityRows === []): ?>
                <div class="rc-empty">هنوز نوبتی برای این بازه ثبت نشده است؛ ظرفیت نمایش داده نمی‌شود.</div>
              <?php else: ?>
                <?php foreach (array_slice($capacityRows, 0, 8) as $capRow):
                    $capTotal = max(0, (int) $capRow['total']);
                    $pct = (int) round(100 * $capTotal / $capMax);
                    $fillClass = $pct > 85 ? 'over' : ($pct > 60 ? 'hot' : '');
                    $overdueCount = (int) ($capRow['overdue_planned'] ?? 0);
                ?>
                <div class="rc-cap-row">
                  <div class="rc-cap-name">
                    <span class="rc-mini-avatar" style="background:<?= $avatarColor((int) $capRow['assignee_id']) ?>"><?= $escape($avatarInitials((string) ($capRow['assignee_name'] ?? '؟'))) ?></span>
                    <span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= $escape((string) ($capRow['assignee_name'] ?: 'بدون مسئول')) ?></span>
                    <small><?= $capTotal ?> نوبت<?= $overdueCount > 0 ? ' · <span style="color:var(--rc-red)">' . $overdueCount . ' معوق</span>' : '' ?></small>
                  </div>
                  <div style="font-size:11px;color:var(--rc-muted);text-align:left"><?= $escape($faDigits((string) $pct)) ?>٪</div>
                  <div class="rc-cap-bar"><div class="rc-cap-fill <?= $fillClass ?>" style="width:<?= $pct ?>%"></div></div>
                </div>
                <?php endforeach; ?>
                <p class="rc-hint" style="margin-top:10px">مبنا: نوبت‌های «انجام‌نشده و لغو‌نشده» در این ماه (نوبت‌های تکراری محتمل‌اند؛ این فقط نمونهٔ تراکم <strong>مشاهده</strong> است و هیچ تصمیم صف یا تخصیص خودکار از آن صادر نمی‌شود.</p>
              <?php endif; ?>
            <?php else: ?>
              <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:8px;text-align:center">
                <div><div class="rc-kpi-value" style="font-size:20px"><?= (int) $counts['active'] ?></div><div class="rc-kpi-label">نوبت فعال</div></div>
                <div><div class="rc-kpi-value" style="font-size:20px;color:var(--rc-red)"><?= (int) $counts['overdue'] ?></div><div class="rc-kpi-label">معوق</div></div>
                <div><div class="rc-kpi-value" style="font-size:20px;color:var(--rc-green)"><?= (int) $counts['done'] ?></div><div class="rc-kpi-label">انجام‌شده</div></div>
              </div>
              <?php if ($plannedThisMonth > 0): ?><p class="rc-hint"><?= $plannedThisMonth ?> نوبت «برنامه‌ریزی‌شده» در این ماه دارید.</p><?php endif; ?>
            <?php endif; ?>
          </div>
        </section>

        <!-- ظرفیت امروز: سقف روزانهٔ کارشناس و دایره -->
        <?php if ($rcElevated && !$rcMissing && $todayLoad['per_assignee'] !== []): ?>
        <section class="rc-panel" aria-label="ظرفیت امروز">
          <div class="rc-panel-title"><h2>ظرفیت امروز (سقف روزانه)</h2><span class="rc-kpi-label"><?= $escape($todayGStr) ?></span></div>
          <div class="rc-panel-body">
            <?php if ($viewerDeptId > 0 && $viewerTeamCap > 0): ?>
              <p class="rc-hint" style="margin-bottom:8px">دایرهٔ شما امروز <?= $viewerTeamUsed ?> از <?= $viewerTeamCap ?> نوبت ثبت کرده است (سقف تجمعی تیم).</p>
            <?php elseif ($viewerDeptId > 0): ?>
              <p class="rc-hint" style="margin-bottom:8px">سقف تجمعی تیم شما غیرفعال است (۰) و اعمال نمی‌شود.</p>
            <?php endif; ?>
            <?php foreach (array_slice($todayLoad['per_assignee'], 0, 6) as $loadRow):
                $used = (int) $loadRow['used'];
                $cap = max(1, (int) $loadRow['cap']);
                $pctToday = (int) round(100 * $used / $cap);
                $loadClass = $pctToday >= 100 ? 'over' : ($pctToday >= 75 ? 'hot' : '');
            ?>
            <div class="rc-cap-row">
              <div class="rc-cap-name">
                <span class="rc-mini-avatar" style="background:<?= $avatarColor((int) $loadRow['assignee_id']) ?>"><?= $escape($avatarInitials((string) ($loadRow['full_name'] ?? '؟'))) ?></span>
                <span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= $escape((string) ($loadRow['full_name'] ?: 'بدون مسئول')) ?></span>
                <small><?= $used ?> از <?= $cap ?></small>
              </div>
              <div class="rc-cap-bar"><div class="rc-cap-fill <?= $loadClass ?>" style="width:<?= min(100, $pctToday) ?>%"></div></div>
            </div>
            <?php endforeach; ?>
            <p class="rc-hint" style="margin-top:10px">مبنا: سقف روزانهٔ کارشناس از تنظیمات (<code>rc.daily_capacity_assignee</code>، پیش‌فرض ۸) و سقف تجمعی دایره (<code>rc.team_daily_capacity_dept_&lt;id&gt;</code> با fallback به <code>rc.team_daily_capacity</code>، پیش‌فرض ۲۴؛ صفر یعنی غیرفعال). ساعت کاری روز از «تقویم کاری» ادمین (یا <code>rc.work_hours_user_&lt;id&gt;</code> برای استثناِ کارشناس) خوانده می‌شود.</p>
          </div>
        </section>
        <?php endif; ?>

        <!-- تعطیلات این ماه -->
        <section class="rc-panel" aria-label="تعطیلات این ماه">
          <div class="rc-panel-title"><h2>تعطیلات رسمی این ماه</h2></div>
          <div class="rc-panel-body">
            <?php if ($rcHolidaysUnavailable): ?>
              <div class="rc-alert warn" role="alert" style="margin:0 0 8px">هشدار: بررسی جدول تعطیلات ممکن نشد؛ روزهای تعطیل در این ماه نمایش داده نشدند. با مدیر سامانه تماس بگیرید یا تاریخ‌ها را دستی بررسی کنید.</div>
            <?php endif; ?>
            <?php if (!$rcHolidaysUnavailable && $holidayMap === []): ?>
              <div class="rc-empty">تعطیلی ثبت‌شده‌ای برای این ماه در جدول «holidays» وجود ندارد.</div>
            <?php elseif ($holidayMap !== []): ?>
              <ul style="margin:0;padding:0;list-style:none">
                <?php foreach ($holidayMap as $holDate => $holTitle): ?>
                  <li style="display:flex;gap:10px;align-items:baseline;padding:7px 2px;border-bottom:1px solid var(--rc-line-soft);font-size:12.5px">
                    <b style="color:var(--rc-red);min-width:86px"><?= $escape($faDigits(jalali_date($holDate, false))) ?></b>
                    <span><?= $escape($holTitle) ?></span>
                  </li>
                <?php endforeach; ?>
              </ul>
            <?php endif; ?>
          </div>
        </section>
      </aside>
    </div>

    <script id="rc-calendar-interactions">
(function(){
 var forms=Array.prototype.slice.call(document.querySelectorAll('form input[name^="rc_appointment_"]')).map(function(input){return input.form;}).filter(function(form,index,all){return form&&all.indexOf(form)===index;});
 forms.forEach(function(form){
  var create=form.querySelector('[name="rc_appointment_create"]');
  var start=form.querySelector('[name="starts_at"]');
  var end=form.querySelector('[name="ends_at"]');
  var ticket=form.querySelector('[name="ticket_id"]');
  var button=form.querySelector('button[type="submit"]');
  if(start&&end){
   var syncEnd=function(){
    end.min=start.value||"";
    if(start.value&&end.value&&end.value<=start.value){end.setCustomValidity("زمان پایان باید بعد از زمان شروع باشد.");}
    else{end.setCustomValidity("");}
   };
   start.addEventListener("change",syncEnd);
   end.addEventListener("change",syncEnd);
  }
  form.addEventListener("submit",function(event){
   if(form.dataset.submitting==="1"){event.preventDefault();return;}
   if(create){
    if(ticket&&(!/^\\d+$/.test(ticket.value)||Number(ticket.value)<1)){event.preventDefault();ticket.setCustomValidity("شناسه درخواست باید عددی بزرگ‌تر از صفر باشد.");ticket.reportValidity();ticket.focus();return;}
    if(ticket)ticket.setCustomValidity("");
    if(start&&end){syncEnd();if(!form.reportValidity()){event.preventDefault();return;}}
   }
   if(form.querySelector('[name="rc_appointment_cancel"]')&&!window.confirm("این نوبت لغو شود؟")){event.preventDefault();return;}
   form.dataset.submitting="1";
   if(button){button.disabled=true;button.textContent=create?"در حال ثبت نوبت…":"در حال ثبت…";}
  });
  if(ticket)ticket.addEventListener("input",function(){ticket.setCustomValidity("");});
 });
})();
</script>

    <footer class="rc-footer">مرکز درخواست‌ها · تقویم شمسی کارشناسان — موعد SLA/OLA «خواندنی»، نوبت رسیدگی «کنشی» · چاپ صفحه برای تحویل شیفت مناسب است (@media print).</footer>
  </main>
</div>
</body>
</html>
