<?php
declare(strict_types=1);

/** Read-only month calendar of scoped expert SLA/OLA deadlines. */
$canUseCalendar = $workspace === 'agent'
    || ($workspace === 'supervisor' && user_can($user, 'supervisor.panel'))
    || ($workspace === 'admin' && user_is_primary_admin($user));
if (!$canUseCalendar) {
    http_response_code(403);
    exit('دسترسی به تقویم کارشناسان مجاز نیست.');
}

$requestedMonth = (string) ($_GET['month'] ?? date('Y-m'));
if (!preg_match('/^\\d{4}-(0[1-9]|1[0-2])$/', $requestedMonth)) {
    $requestedMonth = date('Y-m');
}
$monthStart = DateTimeImmutable::createFromFormat('!Y-m-d', $requestedMonth . '-01');
if (!$monthStart || $monthStart->format('Y-m') !== $requestedMonth) {
    $monthStart = new DateTimeImmutable('first day of this month');
    $requestedMonth = $monthStart->format('Y-m');
}
$monthEnd = $monthStart->modify('last day of this month');
$calendarFilters = [];
if ($workspace === 'agent' && $agentView === 'mine') {
    $calendarFilters['assigned_to'] = (int) $user['id'];
}
$calendarRows = $repo->calendarItems($user, $monthStart->format('Y-m-d'), $monthEnd->format('Y-m-d'), $calendarFilters);
$eventsByDay = [];
foreach ($calendarRows as $calendarRow) {
    foreach ([
        ['field' => 'due_at', 'label' => 'موعد SLA'],
        ['field' => 'ola_response_due_at', 'label' => 'موعد پاسخ OLA'],
        ['field' => 'ola_due_at', 'label' => 'موعد حل OLA'],
    ] as $deadline) {
        $value = trim((string) ($calendarRow[$deadline['field']] ?? ''));
        if ($value === '') {
            continue;
        }
        $eventDate = substr($value, 0, 10);
        if (!preg_match('/^\\d{4}-\\d{2}-\\d{2}$/', $eventDate)) {
            continue;
        }
        $eventsByDay[$eventDate][] = [
            'id' => (int) $calendarRow['id'],
            'ticket_no' => (string) ($calendarRow['ticket_no'] ?? ''),
            'subject' => (string) ($calendarRow['subject'] ?? ''),
            'assignee' => (string) ($calendarRow['assignee_name'] ?? ''),
            'deadline' => $deadline['label'],
            'time' => substr($value, 11, 5),
            'paused' => !empty($calendarRow['sla_paused_at']),
            'overdue' => !$calendarRow['sla_paused_at'] && strtotime($value) < time(),
        ];
    }
}
$firstWeekday = (int) $monthStart->format('N');
$daysInMonth = (int) $monthEnd->format('j');
$cellCount = (int) (ceil(($firstWeekday + $daysInMonth - 1) / 7) * 7);
$previousMonth = $monthStart->modify('-1 month')->format('Y-m');
$nextMonth = $monthStart->modify('+1 month')->format('Y-m');
$monthLabel = $monthStart->format('F Y');
$calendarWeekdays = ['دوشنبه', 'سه‌شنبه', 'چهارشنبه', 'پنجشنبه', 'جمعه', 'شنبه', 'یکشنبه'];
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: same-origin');
header('Cache-Control: private, no-store');
?>
<!doctype html>
<html lang="fa" dir="rtl">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>تقویم موعدهای کارشناسان</title>
<link rel="stylesheet" href="modules/request-center/Assets/request-center.css">
<style>
.rc-calendar-toolbar{display:flex;align-items:center;justify-content:space-between;gap:12px;margin:16px 0}
.rc-calendar-grid{display:grid;grid-template-columns:repeat(7,minmax(0,1fr));gap:7px}
.rc-calendar-weekday{padding:8px;text-align:center;color:#66758b;font-size:12px;font-weight:700}
.rc-calendar-day{min-height:150px;background:#fff;border:1px solid #e4eaf1;border-radius:10px;padding:8px;min-width:0}
.rc-calendar-day.outside{background:#f5f7fa;color:#a0a9b7}
.rc-calendar-day.today{border-color:#2459a6;box-shadow:inset 0 0 0 1px #2459a6}
.rc-calendar-number{font-weight:700;font-size:13px;margin-bottom:6px}
.rc-calendar-event{display:block;padding:5px 6px;margin:4px 0;border-radius:6px;background:#edf4ff;color:#234d82;font-size:11px;line-height:1.5;overflow-wrap:anywhere}
.rc-calendar-event.overdue{background:#fff0ef;color:#a52822}
.rc-calendar-event.paused{background:#f1f2f4;color:#687386}
.rc-calendar-event b{display:block;font-size:10px}
.rc-calendar-legend{display:flex;gap:12px;flex-wrap:wrap;color:#66758b;font-size:12px;margin:10px 0 18px}
.rc-calendar-dot{display:inline-block;width:9px;height:9px;border-radius:50%;margin-left:5px;background:#2459a6}
.rc-calendar-dot.late{background:#b42318}.rc-calendar-dot.paused{background:#8993a3}
.rc-calendar-empty{color:#8a94a5;font-size:11px}
@media(max-width:850px){.rc-calendar-grid{gap:3px}.rc-calendar-day{min-height:110px;padding:4px}.rc-calendar-event{font-size:10px;padding:3px}.rc-calendar-weekday{font-size:10px;padding:4px}}
@media(max-width:560px){.rc-calendar-day{min-height:90px}.rc-calendar-event small{display:none}.rc-calendar-number{font-size:11px}}
</style>
</head>
<body>
<header><div class="bar"><div><div class="brand">مرکز درخواست‌ها</div><small><?= $escape($user['full_name'] ?? $user['username'] ?? '') ?> · تقویم موعدها</small></div><a class="button secondary" href="request-center.php?workspace=<?= $escape($workspace) ?>">بازگشت به فضای کاری</a></div></header>
<main>
<section class="heading"><div><h1>تقویم موعدهای کارشناسان</h1><p>موعدهای SLA، پاسخ OLA و حل OLA برای درخواست‌های قابل مشاهده در حوزهٔ دسترسی شما.</p></div><a class="button" href="request-center.php?page=queue&amp;queue=overdue">صف موارد معوق</a></section>
<div class="rc-calendar-toolbar">
<a class="button secondary" href="request-center.php?workspace=<?= $escape($workspace) ?>&amp;calendar=1&amp;month=<?= $escape($previousMonth) ?>">ماه قبل</a>
<strong><?= $escape($monthLabel) ?></strong>
<a class="button secondary" href="request-center.php?workspace=<?= $escape($workspace) ?>&amp;calendar=1&amp;month=<?= $escape($nextMonth) ?>">ماه بعد</a>
</div>
<div class="rc-calendar-legend"><span><i class="rc-calendar-dot"></i> موعد فعال</span><span><i class="rc-calendar-dot late"></i> موعد گذشته</span><span><i class="rc-calendar-dot paused"></i> SLA/OLA متوقف</span></div>
<section class="card" style="padding:10px">
<div class="rc-calendar-grid">
<?php foreach ($calendarWeekdays as $weekday): ?><div class="rc-calendar-weekday"><?= $escape($weekday) ?></div><?php endforeach; ?>
<?php for ($cell = 0; $cell < $cellCount; $cell++):
    $dayNumber = $cell - $firstWeekday + 2;
    $isCurrentMonth = $dayNumber >= 1 && $dayNumber <= $daysInMonth;
    $cellDate = $isCurrentMonth ? $monthStart->setDate((int) $monthStart->format('Y'), (int) $monthStart->format('m'), $dayNumber) : null;
    $dateKey = $cellDate?->format('Y-m-d') ?? '';
    $isToday = $dateKey === date('Y-m-d');
    $events = $eventsByDay[$dateKey] ?? [];
?>
<div class="rc-calendar-day<?= $isCurrentMonth ? '' : ' outside' ?><?= $isToday ? ' today' : '' ?>">
<div class="rc-calendar-number"><?= $isCurrentMonth ? $dayNumber : ' ' ?></div>
<?php if ($events === [] && $isCurrentMonth): ?><span class="rc-calendar-empty">بدون موعد</span><?php endif; ?>
<?php foreach (array_slice($events, 0, 4) as $event): ?>
<a class="rc-calendar-event<?= $event['overdue'] ? ' overdue' : '' ?><?= $event['paused'] ? ' paused' : '' ?>" href="request-center.php?page=ticket&amp;id=<?= $event['id'] ?>">
<b><?= $escape($event['time']) ?> · <?= $escape($event['deadline']) ?></b>
<?= $escape($event['ticket_no'] !== '' ? $event['ticket_no'] : ('#' . $event['id'])) ?> · <?= $escape($event['subject']) ?>
<small><?= $escape($event['assignee'] ?: 'بدون مسئول') ?></small>
</a>
<?php endforeach; ?>
<?php if (count($events) > 4): ?><span class="rc-calendar-empty">و <?= count($events) - 4 ?> موعد دیگر</span><?php endif; ?>
</div>
<?php endfor; ?>
</div>
</section>
<p class="muted">این تقویم برنامهٔ کاری یا مرخصی کارشناسان نیست؛ فقط موعدهای ثبت‌شدهٔ درخواست‌ها را نمایش می‌دهد. برای جلوگیری از تغییر رفتار فعلی، داده‌ای در پایگاه‌داده ثبت یا ویرایش نمی‌شود.</p>
</main></body></html>
