<?php
declare(strict_types=1);

/** داشبورد گرافیکی و اجرایی Logging؛ داده‌ها فقط از سامانه محلی خوانده می‌شوند. */
function logging_dashboard_fetch(int $days = 7): array
{
    $days = max(1, min(30, $days));
    $out = [
        'database' => 'unknown', 'summary' => [], 'hourly' => [], 'daily' => [],
        'levels' => [], 'modules' => [], 'issues' => null, 'notifications' => null, 'checks' => [], 'recent' => [],
        'spool' => [], 'worker' => [], 'generated_at' => date(DATE_ATOM),
    ];
    // منابع محلی را حتی در زمان قطع DB بخوان تا صف اضطراری از دید خارج نشود.
    try { $out['spool'] = logging_spool_stats(); } catch (Throwable $e) { $out['spool'] = ['state'=>'unknown']; }
    $workerPath = APP_ROOT . '/storage/logs/logging-worker-status.json';
    if (is_file($workerPath)) {
        $worker = json_decode((string) @file_get_contents($workerPath), true);
        if (is_array($worker)) $out['worker'] = $worker;
    }
    try {
        db()->query('SELECT 1')->fetchColumn();
        $out['database'] = 'ok';
    } catch (Throwable $e) {
        logging_emergency_failure('logging_dashboard_db_unavailable', get_class($e));
        try { $out['checks'] = system_health_checks(); } catch (Throwable) { $out['checks'] = []; }
        return $out;
    }

    try {
        $q = db()->query("SELECT
            SUM(level IN ('critical','error') AND created_at >= NOW() - INTERVAL 24 HOUR) errors_24h,
            SUM(level='warning' AND created_at >= NOW() - INTERVAL 24 HOUR) warnings_24h,
            SUM(level='critical' AND created_at >= NOW() - INTERVAL 24 HOUR) critical_24h,
            COUNT(*) total_24h
            FROM system_logs WHERE created_at >= NOW() - INTERVAL 24 HOUR");
        $out['summary'] = $q->fetch(PDO::FETCH_ASSOC) ?: [];
    } catch (Throwable $e) { logging_emergency_failure('logging_dashboard_summary_failed', get_class($e)); }

    try {
        $q = db()->query("SELECT DATE_FORMAT(created_at,'%Y-%m-%d %H:00:00') bucket,
            SUM(level IN ('critical','error')) errors, SUM(level='warning') warnings,
            SUM(level IN ('debug','info','notice')) notices
            FROM system_logs WHERE created_at >= NOW() - INTERVAL 24 HOUR
            GROUP BY bucket ORDER BY bucket");
        $rows = $q->fetchAll(PDO::FETCH_ASSOC);
        $map = [];
        foreach ($rows as $r) $map[(string)$r['bucket']] = ['errors'=>(int)$r['errors'], 'warnings'=>(int)$r['warnings'], 'notices'=>(int)$r['notices']];
        for ($i=23; $i>=0; $i--) {
            $key = date('Y-m-d H:00:00', time() - $i*3600);
            $out['hourly'][] = ['label'=>date('H', strtotime($key)), 'errors'=>$map[$key]['errors']??0, 'warnings'=>$map[$key]['warnings']??0, 'notices'=>$map[$key]['notices']??0];
        }
    } catch (Throwable $e) { logging_emergency_failure('logging_dashboard_hourly_failed', get_class($e)); }

    try {
        $stmt = db()->prepare("SELECT DATE(created_at) bucket, COUNT(*) total,
            SUM(level IN ('critical','error')) errors, SUM(level='warning') warnings
            FROM system_logs WHERE created_at >= NOW() - INTERVAL ? DAY
            GROUP BY DATE(created_at) ORDER BY bucket");
        $stmt->execute([$days]);
        $out['daily'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) { logging_emergency_failure('logging_dashboard_daily_failed', get_class($e)); }

    try {
        $q = db()->query("SELECT level, COUNT(*) total FROM system_logs WHERE created_at >= NOW() - INTERVAL 24 HOUR GROUP BY level ORDER BY total DESC");
        $out['levels'] = $q->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) { logging_emergency_failure('logging_dashboard_levels_failed', get_class($e)); }

    try {
        $q = db()->query("SELECT COALESCE(NULLIF(context,''),'بدون ماژول') module, COUNT(*) total,
            SUM(level IN ('critical','error')) errors
            FROM system_logs WHERE created_at >= NOW() - INTERVAL 7 DAY
            GROUP BY context ORDER BY total DESC LIMIT 7");
        $out['modules'] = $q->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) { logging_emergency_failure('logging_dashboard_modules_failed', get_class($e)); }

    try {
        $q = db()->query("SELECT status, severity, COUNT(*) total FROM log_issues GROUP BY status, severity");
        $out['issues'] = $q->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) { logging_emergency_failure('logging_dashboard_issues_failed', get_class($e)); }

    try {
        $q = db()->query("SELECT id, level, context, code, message, created_at, request_id
            FROM system_logs ORDER BY id DESC LIMIT 12");
        $out['recent'] = $q->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) { logging_emergency_failure('logging_dashboard_recent_failed', get_class($e)); }

    try {
        $q = db()->query("SELECT state, COUNT(*) total FROM log_issue_notifications GROUP BY state");
        $out['notifications'] = $q->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) { logging_emergency_failure('logging_dashboard_notifications_failed', get_class($e)); }
    try { $out['checks'] = system_health_checks(); } catch (Throwable $e) { $out['checks'] = []; }
    return $out;
}

function logging_dashboard_svg_trend(array $points, string $ariaLabel = 'روند ساعتی خطاها و هشدارها در ۲۴ ساعت گذشته'): string
{
    $w=760; $h=220; $left=34; $right=12; $top=18; $bottom=30; $plotW=$w-$left-$right; $plotH=$h-$top-$bottom;
    $max=1;
    foreach ($points as $p) $max=max($max,(int)($p['errors']??0)+(int)($p['warnings']??0));
    $bars=''; $n=max(1,count($points)); $slot=$plotW/$n; $barW=max(3,$slot*.58);
    foreach ($points as $i=>$p) {
        $x=$left+$i*$slot+$slot/2; $err=(int)($p['errors']??0); $warn=(int)($p['warnings']??0);
        $eh=($err/$max)*$plotH; $wh=($warn/$max)*$plotH;
        $bars.='<rect x="'.round($x-$barW/2,1).'" y="'.round($top+$plotH-$eh,1).'" width="'.round($barW,1).'" height="'.round($eh,1).'" rx="3" class="ld-bar-error"><title>'.$err.' خطا</title></rect>';
        $bars.='<rect x="'.round($x-$barW/2,1).'" y="'.round($top+$plotH-$eh-$wh,1).'" width="'.round($barW,1).'" height="'.round($wh,1).'" rx="3" class="ld-bar-warning"><title>'.$warn.' هشدار</title></rect>';
    }
    $grid=''; for($i=0;$i<=4;$i++){ $y=$top+$plotH*$i/4; $val=(int)round($max*(4-$i)/4); $grid.='<line x1="'.$left.'" y1="'.$y.'" x2="'.($w-$right).'" y2="'.$y.'" class="ld-chart-grid"/><text x="'.($left-8).'" y="'.($y+4).'" text-anchor="end" class="ld-axis">'.$val.'</text>'; }
    $labels=''; $labelIndexes=array_unique([0,(int)round(($n-1)*.25),(int)round(($n-1)*.5),(int)round(($n-1)*.75),$n-1]); foreach($labelIndexes as $idx){ if(isset($points[$idx])){ $x=$left+$idx*$slot+$slot/2; $labels.='<text x="'.$x.'" y="'.($h-8).'" text-anchor="middle" class="ld-axis">'.e((string)$points[$idx]['label']).'</text>'; }}
    return '<svg class="ld-trend-svg" viewBox="0 0 '.$w.' '.$h.'" role="img" aria-label="'.e($ariaLabel).'">'.$grid.$bars.$labels.'</svg>';
}

function logging_dashboard_render(array $user): never
{
    if (!user_can($user, 'system.health')) { security_log_access_denied($user, 'system.health', 'logging-dashboard'); http_response_code(403); exit('دسترسی به داشبورد لاگینگ مجاز نیست.'); }
    $days = max(1, min(30, (int)($_GET['days'] ?? 7)));
    $data = logging_dashboard_fetch($days);
    $summary = $data['summary'];
    $errors = isset($summary['errors_24h']) ? (int)$summary['errors_24h'] : null;
    $warnings = isset($summary['warnings_24h']) ? (int)$summary['warnings_24h'] : null;
    $critical = isset($summary['critical_24h']) ? (int)$summary['critical_24h'] : null;
    $openIssues=null; $investigating=null; $resolved=null;
    if (is_array($data['issues'])) { $openIssues=0; $investigating=0; $resolved=0; foreach($data['issues'] as $issue){ $count=(int)$issue['total']; if($issue['status']==='open')$openIssues+=$count; elseif($issue['status']==='investigating')$investigating+=$count; elseif($issue['status']==='resolved')$resolved+=$count; } }
    $spool=$data['spool']; $worker=$data['worker'];
    $notificationCounts=null;
    if (is_array($data['notifications'])) { $notificationCounts=['pending'=>0,'sending'=>0,'sent'=>0,'failed'=>0]; foreach($data['notifications'] as $n){$state=(string)$n['state'];if(array_key_exists($state,$notificationCounts))$notificationCounts[$state]=(int)$n['total'];} }
    $checkOk=count(array_filter($data['checks'],static fn($c)=>($c[1]??'')==='ok'));
    $checkWarn=count(array_filter($data['checks'],static fn($c)=>($c[1]??'')==='warn'));
    $checkFail=count(array_filter($data['checks'],static fn($c)=>($c[1]??'')==='fail'));
    $checkSummary=$data['checks'] ? number_format($checkOk).' سالم · '.number_format($checkWarn).' هشدار · '.number_format($checkFail).' ناموفق' : 'اطلاعات بررسی اجزا در دسترس نیست';
    $workerAt=!empty($worker['last_success_at'])?strtotime((string)$worker['last_success_at']):false;
    $workerFresh=$workerAt!==false && $workerAt>=time()-900;
    $queueFiles=(int)($spool['files']??0); $dead=(int)($spool['dead_letter_files']??0);
    $metricsKnown=array_key_exists('total_24h',$summary);
    $health=($data['database']!=='ok'||!$metricsKnown)?'unknown':(($critical??0)>0||$dead>0?'critical':(($errors??0)>0||($warnings??0)>0||!$workerFresh?'warning':'ok'));
    $healthLabel=['ok'=>'پایدار','warning'=>'نیازمند بررسی','critical'=>'بحرانی','unknown'=>'نامشخص'][$health];
    render_header('داشبورد مانیتورینگ لاگینگ',$user);
    echo '<section class="ld-head"><div><span class="ld-eyebrow">LOGGING OBSERVABILITY · OFFLINE</span><h1>داشبورد مانیتورینگ لاگینگ</h1><p>نمای اجرایی رخدادها، روند خطا، وضعیت صف، Worker و Issueها؛ داده‌ها مستقیماً از دیتابیس و فایل‌های محلی خوانده می‌شوند.</p></div><div class="ld-head-actions"><span class="ld-live"><i></i> به‌روزرسانی خودکار هر ۶۰ ثانیه</span><a class="button secondary" href="index.php?page=system-health">گزارش سلامت فنی</a><a class="button" href="index.php?page=activity-log&amp;category=errors">مشاهده لاگ‌ها</a></div></section>';
    echo '<section class="ld-status-row"><div class="ld-status-card"><span class="ld-status-dot '.$health.'"></span><div><small>وضعیت کلی Logging</small><strong>'.e($healthLabel).'</strong><em>آخرین بررسی: '.e(date('H:i:s')).'</em></div></div><div class="ld-status-card"><span class="ld-status-icon">DB</span><div><small>پایگاه داده</small><strong>'.($data['database']==='ok'?'متصل':'نامشخص').'</strong><em>'.($data['database']==='ok'?'SELECT 1 موفق':'آمار قابل محاسبه نیست').'</em></div></div><div class="ld-status-card"><span class="ld-status-icon">WK</span><div><small>Worker</small><strong>'.($workerFresh?'فعال':'نیازمند بررسی').'</strong><em>'.($workerAt!==false?'آخرین موفقیت '.e(date('Y-m-d H:i',$workerAt)):'سابقه موفقیت ثبت نشده').'</em></div></div><div class="ld-status-card"><span class="ld-status-icon">Q</span><div><small>صف اضطراری</small><strong>'.number_format($queueFiles).' فایل</strong><em>'.number_format((int)($spool['bytes']??0)/1048576,2).' MB · Dead Letter: '.number_format($dead).'</em></div></div></section>';
    if($data['database']!=='ok') echo '<div class="ld-alert">اتصال پایگاه داده در دسترس نیست؛ نمودارها و شمارنده‌های دیتابیسی نامشخص‌اند و صفر تلقی نمی‌شوند. وضعیت صف محلی همچنان نمایش داده می‌شود.</div>';
    echo '<section class="ld-kpis"><article class="ld-kpi"><span>خطا و بحرانی · ۲۴ ساعت</span><strong>'.($errors===null?'—':number_format($errors)).'</strong><small>رخدادهای ثبت‌شده در system_logs</small></article><article class="ld-kpi"><span>هشدار · ۲۴ ساعت</span><strong>'.($warnings===null?'—':number_format($warnings)).'</strong><small>نیازمند پایش و بررسی</small></article><article class="ld-kpi"><span>Issue باز</span><strong>'.($openIssues===null?'—':number_format($openIssues)).'</strong><small>موارد نیازمند رسیدگی</small></article><article class="ld-kpi"><span>Issue در حال بررسی</span><strong>'.($investigating===null?'—':number_format($investigating)).'</strong><small>موارد تخصیص‌یافته/در دست اقدام</small></article><article class="ld-kpi"><span>Issue حل‌شده</span><strong>'.($resolved===null?'—':number_format($resolved)).'</strong><small>سوابق بسته‌شده</small></article><article class="ld-kpi"><span>Dead Letter</span><strong>'.number_format($dead).'</strong><small>'.number_format((int)($spool['dead_letter_bytes']??0)/1048576,2).' MB فایل نیازمند رسیدگی</small></article></section>';
    echo '<section class="ld-grid"><article class="ld-panel ld-panel-wide"><div class="ld-panel-head"><div><h2>روند خطا و هشدار</h2><p>توزیع ساعتی در ۲۴ ساعت گذشته · زمان سرور</p></div><span class="ld-period">۲۴ ساعت</span></div>'.logging_dashboard_svg_trend($data['hourly']).'<div class="ld-legend"><span><i class="err"></i> خطا / بحرانی</span><span><i class="warn"></i> هشدار</span></div></article>';
    echo '<article class="ld-panel ld-panel-wide"><div class="ld-panel-head"><div><h2>روند روزانه رخدادها</h2><p>مقایسه خطا و هشدار در بازه انتخاب‌شده</p></div><span class="ld-period">'.number_format($days).' روز</span></div>'.logging_dashboard_svg_trend(array_map(static fn($r)=>['label'=>date('m/d',strtotime((string)$r['bucket'])),'errors'=>(int)$r['errors'],'warnings'=>(int)$r['warnings']],$data['daily']),'روند روزانه خطاها و هشدارها').'<div class="ld-legend"><span><i class="err"></i> خطا / بحرانی</span><span><i class="warn"></i> هشدار</span></div></article>';
    echo '<article class="ld-panel"><div class="ld-panel-head"><div><h2>شدت رخدادها</h2><p>۲۴ ساعت گذشته</p></div></div><div class="ld-level-list">';
    $levelLabels=['critical'=>'بحرانی','error'=>'خطا','warning'=>'هشدار','notice'=>'اطلاع','info'=>'اطلاعات','debug'=>'اشکال‌زدایی'];
    foreach($data['levels'] as $level){$lv=(string)$level['level'];$cnt=(int)$level['total'];$pct=(int)round(100*$cnt/max(1,array_sum(array_map(static fn($x)=>(int)$x['total'],$data['levels']))));echo '<div class="ld-level"><div><span class="ld-level-dot '.e($lv).'"></span>'.e($levelLabels[$lv]??$lv).'<b>'.number_format($cnt).'</b></div><div class="ld-meter"><i style="width:'.$pct.'%"></i></div></div>';}
    if(!$data['levels'])echo '<p class="ld-empty">داده‌ای برای این بازه موجود نیست.</p>';
    echo '</div></article><article class="ld-panel"><div class="ld-panel-head"><div><h2>ماژول‌های پرتکرار</h2><p>۷ روز گذشته</p></div></div><div class="ld-module-list">';
    foreach($data['modules'] as $module){$total=(int)$module['total'];$err=(int)$module['errors'];$pct=(int)round(100*$total/max(1,(int)($data['modules'][0]['total']??1)));echo '<div class="ld-module"><div><strong>'.e((string)$module['module']).'</strong><span>'.number_format($total).' رخداد · '.number_format($err).' خطا</span></div><div class="ld-meter"><i style="width:'.$pct.'%"></i></div></div>';}
    if(!$data['modules'])echo '<p class="ld-empty">داده‌ای برای این بازه موجود نیست.</p>';
    echo '</div></article><article class="ld-panel"><div class="ld-panel-head"><div><h2>وضعیت صف و Worker</h2><p>شاخص‌های محلی و زمان آخرین اجرا</p></div></div><div class="ld-runtime"><div><span>فایل‌های صف</span><b>'.number_format($queueFiles).'</b></div><div><span>فایل‌های درحال پردازش</span><b>'.number_format((int)($spool['processing_files']??0)).'</b></div><div><span>فایل موقت بازنویسی</span><b>'.number_format((int)($spool['rewrite_temp_files']??0)).'</b></div><div><span>حجم صف</span><b>'.number_format((int)($spool['bytes']??0)/1048576,2).' MB</b></div><div><span>تعداد پردازش‌شده Worker</span><b>'.number_format((int)($worker['processed']??0)).'</b></div><div><span>شکست Worker</span><b>'.number_format((int)($worker['failed']??0)).'</b></div><div><span>اعلان‌های در انتظار</span><b>'.($notificationCounts===null?'—':number_format($notificationCounts['pending'])).'</b></div><div><span>اعلان‌های درحال ارسال</span><b>'.($notificationCounts===null?'—':number_format($notificationCounts['sending'])).'</b></div><div><span>اعلان‌های ناموفق</span><b>'.($notificationCounts===null?'—':number_format($notificationCounts['failed'])).'</b></div></div></article><article class="ld-panel"><div class="ld-panel-head"><div><h2>پایش اجزای سامانه</h2><p>'.e($checkSummary).'</p></div></div><div class="ld-check-list">';
    foreach($data['checks'] as $check){$st=(string)($check[1]??'unknown');echo '<div class="ld-check-item"><span class="ld-check-dot '.e($st).'"></span><div><strong>'.e((string)($check[0]??'بررسی')).'</strong><small>'.e((string)($check[2]??'')).'</small></div><b>'.e(['ok'=>'سالم','warn'=>'هشدار','fail'=>'ناموفق'][$st]??'نامشخص').'</b></div>';}
    if(!$data['checks'])echo '<p class="ld-empty">اطلاعات بررسی اجزا در دسترس نیست.</p>';
    echo '</div></article>';
    echo '<article class="ld-panel ld-panel-wide"><div class="ld-panel-head"><div><h2>آخرین رخدادها</h2><p>۱۲ رخداد اخیر؛ برای مشاهده جزئیات به لاگ سامانه بروید</p></div><a href="index.php?page=activity-log&amp;category=errors">همه لاگ‌ها ←</a></div><div class="ld-table-wrap"><table class="ld-table"><thead><tr><th>زمان</th><th>شدت</th><th>ماژول</th><th>کد</th><th>شرح</th><th>Request ID</th></tr></thead><tbody>';
    foreach($data['recent'] as $r){$msg=logging_diagnostic_safe_value((string)($r['message']??''),180);$level=(string)($r['level']??'');echo '<tr><td>'.e((string)$r['created_at']).'</td><td><span class="ld-pill '.e($level).'">'.e($levelLabels[$level]??$level).'</span></td><td>'.e((string)($r['context']??'—')).'</td><td>'.e((string)($r['code']??'—')).'</td><td class="ld-message">'.e($msg).'</td><td>'.e((string)($r['request_id']??'—')).'</td></tr>';}
    if(!$data['recent'])echo '<tr><td colspan="6" class="ld-empty">رخدادی برای نمایش ثبت نشده است.</td></tr>';
    echo '</tbody></table></div></article></section>';
    echo '<section class="ld-foot"><span>بازهٔ تحلیل: <strong>'.number_format($days).' روز</strong></span><form method="get"><input type="hidden" name="page" value="logging-dashboard"><label for="ld-days">بازه گزارش</label><select id="ld-days" name="days"><option value="1"'.($days===1?' selected':'').'>۱ روز</option><option value="7"'.($days===7?' selected':'').'>۷ روز</option><option value="14"'.($days===14?' selected':'').'>۱۴ روز</option><option value="30"'.($days===30?' selected':'').'>۳۰ روز</option></select><button class="button secondary" type="submit">اعمال</button></form><span>آخرین دریافت: '.e(date('Y-m-d H:i:s')).'</span></section>';
    echo '<script src="assets/logging-dashboard.js?v=1" defer></script>';
    render_footer();
    exit;
}
