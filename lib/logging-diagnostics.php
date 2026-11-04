<?php
declare(strict_types=1);

/** تشخیص قطعی و آفلاین رخدادها؛ خروجی‌ها توضیح‌پذیرند و جایگزین بررسی کارشناس نیستند. */
function logging_diagnostic_rules(): array
{
    return [
        'SYS-DB-001' => ['cause'=>'اختلال اتصال، ناسازگاری Schema یا قفل/ظرفیت پایگاه داده','checks'=>['اتصال PDO و دسترس‌پذیری میزبان دیتابیس','اجرای Migrationهای 019 تا 022 و تطبیق ستون‌های موردنیاز','بررسی فضای دیسک، max_connections، deadlock و slow query log'],'fix'=>'ابتدا نوع خطای SQLSTATE و لاگ دیتابیس را مشخص کنید؛ Migration را فقط پس از پشتیبان‌گیری و روی Staging اجرا کنید.','confidence'=>'medium','signals'=>['sqlstate','db_operation','table']],
        'SYS-PHP-001' => ['cause'=>'خطای زمان اجرا در PHP؛ احتمال ورودی نامعتبر، وابستگی یا خطای منطق برنامه','checks'=>['فایل و خط گزارش‌شده و چند فریم Stack پالایش‌شده','نسخه PHP و افزونه‌های موردنیاز','رخدادهای هم‌بسته با Request ID و Trace ID'],'fix'=>'خطا را در Staging با ورودی حداقلی بازتولید کنید؛ قبل از تغییر کد، خط و تابع گزارش‌شده را با نسخه مستقر تطبیق دهید.','confidence'=>'medium','signals'=>['error_type','file_basename','line']],
        'SYS-PERF-001' => ['cause'=>'عبور زمان پاسخ از آستانه؛ Query سنگین، رقابت منابع یا وابستگی کند','checks'=>['duration_ms و query_count در رخداد','شاخص‌های Query و slow query log دیتابیس','CPU، RAM، دیسک و تعداد درخواست‌های هم‌زمان'],'fix'=>'با Trace و زمان Queryها گلوگاه را جدا کنید؛ افزودن شاخص یا Cache فقط پس از بررسی Query plan انجام شود.','confidence'=>'medium','signals'=>['duration_ms','query_count','route']],
        'INT-LDAP-001' => ['cause'=>'عدم دسترسی یا خطای احراز هویت/همگام‌سازی LDAP','checks'=>['DNS و دسترسی شبکه به سرور دامین','اعتبار گواهی TLS و ساعت سیستم','تنظیمات Base DN، Bind و نتیجه عملیات بدون افشای رمز'],'fix'=>'اتصال و گواهی را از خود Windows Server بررسی کنید؛ رمز یا Bind secret را در بسته تشخیصی قرار ندهید.','confidence'=>'medium','signals'=>['server_alias','operation','result']],
        'FOOD-PRT-003' => ['cause'=>'ناموفق بودن تولید یا تحویل فیش به چاپگر','checks'=>['وضعیت چاپگر و صف چاپ Windows','آدرس/پورت و دسترسی شبکه','تکرار Print Job ID و شمار تلاش‌ها'],'fix'=>'صف چاپ را بررسی و پس از اطمینان از عدم چاپ تکراری، همان Job را آگاهانه دوباره ارسال کنید.','confidence'=>'medium','signals'=>['printer_alias','print_job_id','attempt']],
        'SEC-AUTH-001' => ['cause'=>'تلاش احراز هویت ناموفق؛ ممکن است ناشی از رمز اشتباه یا تلاش غیرمجاز باشد','checks'=>['تعداد تلاش‌ها در بازه زمانی و وضعیت قفل حساب','تطبیق زمان و منبع تلاش با سیاست امنیتی','بررسی رخدادهای متوالی برای همان username_hash'],'fix'=>'در صورت الگوی غیرعادی، حساب و منبع تلاش را طبق رویه امنیتی بررسی کنید؛ اطلاعات ورود را در گزارش خروجی درج نکنید.','confidence'=>'low','signals'=>['username_hash','attempt_window']],
        'SEC-ACL-001' => ['cause'=>'درخواست عملیات خارج از مجوز مؤثر کاربر یا دامنه دسترسی','checks'=>['permission ثبت‌شده و مجوز مؤثر نقش','دامنه سازمانی/مالکیت منبع','تغییرات اخیر نقش و مجوز در Audit'],'fix'=>'مجوز را با اصل حداقل دسترسی تطبیق دهید؛ صرفاً برای رفع خطا نقش گسترده‌تر اعطا نکنید.','confidence'=>'medium','signals'=>['permission','route','resource_type']],
    ];
}

function logging_diagnostic_analyze(array $event): array
{
    $code = strtoupper(trim((string)($event['code'] ?? 'SYS-LEGACY-001')));
    $catalog = logging_catalog_resolve($code, (string)($event['category'] ?? 'system'), (string)($event['level'] ?? 'error'));
    $rule = logging_diagnostic_rules()[$code] ?? null;
    $evidence = [];
    foreach (['request_id'=>'شناسه درخواست','trace_id'=>'شناسه Trace','operation_id'=>'شناسه عملیات','created_at'=>'زمان رخداد','context'=>'ماژول/زمینه','level'=>'شدت'] as $key=>$label) {
        if (isset($event[$key]) && is_scalar($event[$key]) && (string)$event[$key] !== '') $evidence[] = ['key'=>$key,'label'=>$label,'value'=>logging_diagnostic_safe_value((string)$event[$key], 180)];
    }
    $meta = $event['meta'] ?? $event['meta_json'] ?? [];
    if (is_string($meta)) { $decoded = json_decode($meta, true); $meta = is_array($decoded) ? $decoded : []; }
    $stackFrames = [];
    if (is_array($meta)) {
        $stackRaw = (string)($meta['stack_trace'] ?? $meta['trace'] ?? '');
        if ($stackRaw !== '') $stackFrames = logging_diagnostic_safe_stack($stackRaw);
    }
    if (is_array($meta)) foreach ($meta as $key=>$value) {
        if (!is_scalar($value) || preg_match('/password|passwd|secret|token|authorization|cookie|email|phone|mobile|username|user_id|session/i', (string)$key)) continue;
        if (in_array((string)$key, ['sql','query','request_body','response_body','stack_trace','trace','exception_message'], true)) continue;
        $displayValue = in_array(strtolower((string)$key), ['file','file_path','filename'], true) ? basename(str_replace('\\', '/', (string)$value)) : logging_diagnostic_safe_value((string)$value, 180);
        $evidence[] = ['key'=>(string)$key,'label'=>'زمینه: '.(string)$key,'value'=>$displayValue];
        if (count($evidence) >= 18) break;
    }
    $confidence = $rule['confidence'] ?? 'low';
    if ($rule && count($evidence) >= 5) $confidence = $confidence === 'medium' ? 'high' : $confidence;
    return [
        'engine'=>'deterministic-offline-v1','code'=>$code,'title'=>(string)($catalog['title'] ?? 'رخداد ناشناخته'),
        'probable_cause'=>$rule['cause'] ?? (string)($catalog['probable_cause'] ?? 'اطلاعات کافی برای تعیین علت مشخص وجود ندارد.'),
        'confidence'=>$confidence,'confidence_label'=>['low'=>'پایین','medium'=>'متوسط','high'=>'بالا'][$confidence],
        'evidence'=>$evidence,'stack_frames'=>$stackFrames,'checks'=>$rule['checks'] ?? ['بررسی رخدادهای هم‌بسته با Request ID و Trace ID','بررسی نسخه، تغییرات اخیر و لاگ‌های همان ماژول'],
        'recommended_fix'=>$rule['fix'] ?? (string)($catalog['recommended_action'] ?? 'جزئیات رخداد و سوابق مرتبط را بررسی کنید.'),
        'known_rule'=>(bool)$rule,
    ];
}

function logging_diagnostic_safe_value(string $value, int $limit=500): string
{
    $value = preg_replace('/[\r\n\t]+/', ' ', $value) ?? '';
    $value = preg_replace('/(?i)(password|passwd|secret|token|authorization|cookie|api[_-]?key)\s*[:=]\s*[^\s,;]+/', '$1=[REDACTED]', $value) ?? $value;
    $value = preg_replace('/(?i)\b(SELECT|INSERT\s+INTO|UPDATE\s+\w+\s+SET|DELETE\s+FROM|ALTER\s+TABLE|CREATE\s+TABLE|DROP\s+TABLE)\b[^;]{0,1200}(?:;|$)/', '[SQL REDACTED]', $value) ?? $value;
    $value = preg_replace('/(?i)\b(mysql|mariadb):[^\s]+/', '[DB-CONNECTION REDACTED]', $value) ?? $value;
    $value = preg_replace('~(?:[A-Z]:\\(?:[^\\\s]+\\)*[^\\\s]+|/(?:var|srv|home|www|opt|mnt|tmp)/[^\s]+)~i', '[PATH]', $value) ?? $value;
    $value = preg_replace('/\b[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}\b/i', '[EMAIL]', $value) ?? $value;
    $value = preg_replace('/\b(?:\+?\d[\d ()-]{7,}\d)\b/', '[PHONE]', $value) ?? $value;
    return function_exists('mb_substr') ? mb_substr($value,0,$limit,'UTF-8') : substr($value,0,$limit);
}

function logging_diagnostic_safe_stack(string $stack): array
{
    $frames=[];
    foreach (preg_split('/\r\n|\r|\n/', $stack) ?: [] as $line) {
        if (!preg_match('/(?:in\s+)?([A-Za-z]:\\\\[^:\r\n(]+|\/[^:\r\n(]+)(?::|\()(\d+)/', $line, $match)) continue;
        $path=str_replace('\\','/',$match[1]);
        $function='';
        if (preg_match('/\b([A-Za-z_][A-Za-z0-9_\\\\:>-]*)\s*\(/', $line, $fn)) $function=$fn[1];
        $frames[]=['file'=>basename($path),'line'=>(int)$match[2],'function'=>logging_diagnostic_safe_value($function,100)];
        if (count($frames)>=12) break;
    }
    return $frames;
}

function logging_diagnostic_similarity(array $target, array $candidates, int $limit=10): array
{
    $code = (string)($target['code'] ?? ''); $category=(string)($target['category'] ?? ''); $context=(string)($target['context'] ?? '');
    $targetTitle = strtolower(trim(preg_replace('/\s+/',' ',(string)($target['title'] ?? $target['message'] ?? '')) ?? ''));
    $scores=[];
    foreach ($candidates as $candidate) {
        if (!is_array($candidate) || (string)($candidate['fingerprint'] ?? '') === (string)($target['fingerprint'] ?? '')) continue;
        $score=0;
        if ($code !== '' && $code === (string)($candidate['code'] ?? '')) $score += 55;
        if ($category !== '' && $category === (string)($candidate['category'] ?? '')) $score += 15;
        if ($context !== '' && $context === (string)($candidate['context'] ?? '')) $score += 15;
        $title = strtolower(trim(preg_replace('/\s+/',' ',(string)($candidate['title'] ?? '')) ?? ''));
        if ($targetTitle !== '' && $title !== '' && $targetTitle === $title) $score += 15;
        if ($score >= 55) $scores[]=['score'=>$score,'fingerprint'=>(string)($candidate['fingerprint'] ?? ''),'code'=>(string)($candidate['code'] ?? ''),'title'=>logging_diagnostic_safe_value((string)($candidate['title'] ?? ''),255),'occurrences'=>(int)($candidate['occurrences'] ?? 0),'status'=>(string)($candidate['status'] ?? 'open')];
    }
    usort($scores, static fn($a,$b)=>$b['score'] <=> $a['score']);
    return array_slice($scores,0,max(1,min(50,$limit)));
}

function logging_diagnostic_redact_event(array $event): array
{
    $safe=[];
    foreach (['event_id','request_id','trace_id','operation_id','parent_event_id','code','category','level','context','created_at'] as $key) {
        if (isset($event[$key])) $safe[$key]=logging_diagnostic_safe_value((string)$event[$key],255);
    }
    $safe['message']=logging_diagnostic_safe_value((string)($event['message'] ?? ''),1000);
    $meta=$event['meta'] ?? $event['meta_json'] ?? [];
    if (is_string($meta)) { $decoded=json_decode($meta,true); $meta=is_array($decoded)?$decoded:[]; }
    $safeMeta=[];
    if (is_array($meta)) foreach ($meta as $key=>$value) {
        if (!is_scalar($value) || preg_match('/password|passwd|secret|token|authorization|cookie|email|phone|mobile|username|user_id|session|sql|query|body|stack|trace/i',(string)$key)) continue;
        $safeMeta[(string)$key]=in_array(strtolower((string)$key), ['file','file_path','filename'], true) ? basename(str_replace('\\', '/', (string)$value)) : logging_diagnostic_safe_value((string)$value,300);
        if (count($safeMeta)>=30) break;
    }
    $safe['meta']=$safeMeta;
    $stackRaw=is_array($meta)?(string)($meta['stack_trace']??$meta['trace']??''):'';
    $safe['stack_frames']=$stackRaw!==''?logging_diagnostic_safe_stack($stackRaw):[];
    return $safe;
}
