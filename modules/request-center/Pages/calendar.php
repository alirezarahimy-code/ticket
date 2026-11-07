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
<p class="muted">نمای ماهانه فقط موعدهای ثبت‌شدهٔ درخواست‌ها را نشان می‌دهد؛ مدیریت نوبت‌های رسیدگی پایین‌تر انجام می‌شود.</p>
<?php
/** ===== نوبت‌های رسیدگی کارشناسان (فاز سه) ===== */
require_once dirname(__DIR__) . '/AppointmentRepository.php';
$rcElevated = user_can($user, 'supervisor.panel') || user_is_primary_admin($user);
$rcAppointments = new AppointmentRepository(db());
$rcMissing = !$rcAppointments->tableExists();
$rcFlash = trim((string) ($_GET['appointments'] ?? ''));
$rcReason = trim((string) ($_GET['reason'] ?? ''));
$rcFlashMessage = match ($rcFlash) {
    'created'   => 'نوبت ثبت شد.',
    'cancelled' => 'نوبت لغو شد.',
    'error'     => 'خطا: ' . ($rcReason !== '' ? $rcReason : 'عملیات انجام نشد.'),
    default     => '',
};
$rcList = $rcMissing ? [] : $rcAppointments->listUpcoming($rcElevated ? null : (int) $user['id'], date('Y-m-d H:i:s'), 10);
?>
<section class="card" style="padding:14px 16px">
  <h2 style="margin-top:0">نوبت‌های رسیدگی کارشناسان</h2>
  <?php if ($rcFlashMessage !== ''): ?><div class="alert <?= $rcFlash === 'error' ? 'danger' : 'success' ?>" style="margin:6px 0"><?= $escape($rcFlashMessage) ?></div><?php endif; ?>
  <?php if ($rcMissing): ?>
    <div class="alert warning" style="margin:0">جدول <code>request_center_appointments</code> هنوز ایجاد نشده است. روی همین سامانهٔ تست مهاجرت <code>migrations/035-request-center-appointments.sql</code> را اعمال کنید؛ تا آن زمان ثبت نوبت غیرفعال است. این ورودی هیچ DDL خودکاری اجرا نمی‌کند.</div>
  <?php else: ?>
  <div style="display:grid;grid-template-columns:1.15fr 1fr;gap:16px" class="rc-appt-cols">
    <div>
      <h3 style="font-size:14px;margin:6px 0 8px">نوبت‌های برنامه‌ریزی‌شده (۱۴ روز آینده)</h3>
      <?php if ($rcList === []): ?><p class="muted" style="margin:0">هنوز نوبتی ثبت نشده است.</p><?php endif; ?>
      <?php foreach ($rcList as $apptRow):
          $apptStart = (string) $apptRow['starts_at'];
          $apptEnd = (string) $apptRow['ends_at'];
          $apptKind = AppointmentRepository::KINDS[(string) $apptRow['kind']] ?? (string) $apptRow['kind'];
      ?>
      <div style="display:flex;gap:10px;align-items:flex-start;justify-content:space-between;border:1px solid #e4eaf1;border-radius:10px;padding:8px 10px;margin:6px 0">
        <div style="min-width:0">
          <b><?= $escape(substr($apptStart, 0, 10)) ?> — <?= $escape(substr($apptStart, 11, 5)) ?> تا <?= $escape(substr($apptEnd, 11, 5)) ?></b>
          <div style="font-size:12px"><a href="request-center.php?page=ticket&amp;id=<?= (int) $apptRow['ticket_id'] ?>"><?= $escape((string) ($apptRow['ticket_no'] ?? ('#' . $apptRow['ticket_id']))) ?></a> · <?= $escape($apptKind) ?><?php if ($rcElevated && !empty($apptRow['assignee_name'])): ?> · <?= $escape((string) $apptRow['assignee_name']) ?><?php endif; ?></div>
          <?php if (!empty($apptRow['note'])): ?><small class="muted"><?= $escape((string) $apptRow['note']) ?></small><?php endif; ?>
        </div>
        <form method="post" action="request-center.php?workspace=<?= $escape($workspace) ?>&amp;calendar=1&amp;agent_view=<?= $escape($agentView) ?>" onsubmit="return confirm('این نوبت لغو شود؟')"><?= csrf_field() ?><input type="hidden" name="rc_appointment_cancel" value="1"><input type="hidden" name="appointment_id" value="<?= (int) $apptRow['id'] ?>"><button class="mini-button danger" type="submit">لغو</button></form>
      </div>
      <?php endforeach; ?>
    </div>
    <form method="post" action="request-center.php?workspace=<?= $escape($workspace) ?>&amp;calendar=1&amp;agent_view=<?= $escape($agentView) ?>" style="border:1px solid #e4eaf1;border-radius:10px;padding:10px 12px">
      <h3 style="font-size:14px;margin:6px 0 10px">ثبت نوبت رسیدگی</h3>
      <?= csrf_field() ?><input type="hidden" name="rc_appointment_create" value="1">
      <label style="display:block;font-size:12px;margin:6px 0">شمارهٔ شناسهٔ درخواست<input type="number" name="ticket_id" min="1" required style="width:100%;padding:6px" placeholder="مثلاً 123"></label>
      <label style="display:block;font-size:12px;margin:6px 0">شروع<input type="datetime-local" name="starts_at" required style="width:100%;padding:6px"></label>
      <label style="display:block;font-size:12px;margin:6px 0">پایان<input type="datetime-local" name="ends_at" required style="width:100%;padding:6px"></label>
      <label style="display:block;font-size:12px;margin:6px 0">نوع<select name="kind" style="width:100%;padding:6px"><?php foreach (AppointmentRepository::KINDS as $kindKey => $kindLabel): ?><option value="<?= $escape($kindKey) ?>"><?= $escape($kindLabel) ?></option><?php endforeach; ?></select></label>
      <?php if ($rcElevated): ?>
      <label style="display:block;font-size:12px;margin:6px 0">شناسهٔ کارشناس مسئول (اختیاری؛ خالی = خود شما)<input type="number" name="assignee_id" min="1" style="width:100%;padding:6px" placeholder="مثلاً 42"></label>
      <?php endif; ?>
      <label style="display:block;font-size:12px;margin:6px 0">یادداشت<textarea name="note" maxlength="500" rows="2" style="width:100%;padding:6px" placeholder="اختیاری"></textarea></label>
      <button class="button" type="submit">ثبت نوبت</button>
      <small class="muted" style="display:block;margin-top:6px">ثبت نوبت وضعیت درخواست یا SLA/OLA را تغییر نمی‌دهد. تداخل با نوبت فعال هم‌زمان برای همان کارشناس پذیرفته نمی‌شود.</small>
    </form>
  </div>
  <?php endif; ?>
</section>
<style>@media(max-width:860px){.rc-appt-cols{grid-template-columns:1fr!important}}</style>
</main></body></html>
