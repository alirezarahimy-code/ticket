<?php
declare(strict_types=1);

/*
 * بات پاسخ‌گوی سامانه (فاز ۱): تطبیق قانون‌محور با پرسش‌های متداول.
 * - بدون سرویس خارجی و بدون سخت‌افزار اضافه
 * - جدول‌ها: chatbot_faq (پرسش/پاسخ/کلیدواژه) و chatbot_logs (سؤال‌ها و بازخورد)
 * - دسترسی: chatbot.use برای پاسخ، chatbot.manage برای مدیریت محتوا
 */

const CHATBOT_THRESHOLD = 1.5;
const CHATBOT_STOPWORDS = ['من', 'را', 'به', 'از', 'در', 'که', 'و', 'یک', 'یه', 'این', 'آن', 'چطور', 'چگونه', 'چه', 'چی',
    'است', 'هست', 'می', 'توانم', 'بتوانم', 'باید', 'برای', 'با', 'شما', 'کنم', 'کنیم', 'نمی', 'ام', 'هم', 'تا', 'رو', 'های', 'میشه', 'میشود'];

/** متن فارسی را برای مقایسه یکسان می‌کند (ی/ک عربی، ارقام، علامت‌ها، فاصلهٔ اضافه). */
function chatbot_normalize(string $text): string
{
    $text = str_replace(["\u{200C}", "\u{200B}", "\u{FEFF}"], '', $text);
    $text = strtr($text, [
        'ي' => 'ی', 'ى' => 'ی', 'ك' => 'ک', 'ة' => 'ه', 'ۀ' => 'ه', 'أ' => 'ا', 'إ' => 'ا', 'ؤ' => 'و',
        '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4', '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
        '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4', '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
    ]);
    $text = mb_strtolower($text, 'UTF-8');
    $text = (string) preg_replace('/[^\p{L}\p{N}\s]+/u', ' ', $text);
    return trim((string) preg_replace('/\s+/u', ' ', $text));
}

/** متن بدون فاصله؛ برای اینکه «می خواهم» و «میخواهم» یکی شوند. */
function chatbot_compact(string $normalized): string
{
    return str_replace(' ', '', $normalized);
}

/** کلیدواژه‌های یک پاسخ را (با جداکنندهٔ ویرگول) به فهرست نرمال تبدیل می‌کند. */
function chatbot_keywords(string $raw): array
{
    $list = [];
    foreach (preg_split('/[,،;\n]+/u', $raw) ?: [] as $part) {
        $norm = chatbot_normalize((string) $part);
        if (mb_strlen(chatbot_compact($norm), 'UTF-8') >= 2) {
            $list[chatbot_compact($norm)] = $norm;
        }
    }
    return array_values($list);
}

/** امتیاز تطبیق یک پرسش با یک مدخل FAQ. */
function chatbot_score(string $question, array $faq): float
{
    $qNorm = chatbot_normalize($question);
    $qCompact = chatbot_compact($qNorm);
    $qTokens = array_values(array_filter(
        explode(' ', $qNorm),
        static fn(string $t): bool => mb_strlen($t, 'UTF-8') >= 2 && !in_array($t, CHATBOT_STOPWORDS, true)
    ));

    $score = 0.0;
    foreach (chatbot_keywords((string) ($faq['keywords'] ?? '')) as $compactKey => $keyNorm) {
        $keyCompact = chatbot_compact($keyNorm);
        $keyLen = mb_strlen($keyCompact, 'UTF-8');
        if ($keyLen >= 2 && str_contains($qCompact, $keyCompact)) {
            $score += 1.0 + min(2.0, $keyLen / 5);
            continue;
        }
        // غلط املایی ساده: فاصلهٔ ویرایشی کم روی کلمه‌های بلندتر
        if ($keyLen >= 4) {
            $allowed = $keyLen >= 7 ? 2 : 1;
            foreach ($qTokens as $token) {
                if (mb_strlen($token, 'UTF-8') >= 4 && levenshtein($token, $keyCompact) <= $allowed) {
                    $score += 0.7;
                    break;
                }
            }
        }
    }

    // هم‌پوشانی کلمه‌های خود پرسش FAQ با سؤال کاربر
    $faqTokens = array_filter(
        explode(' ', chatbot_normalize((string) ($faq['question'] ?? ''))),
        static fn(string $t): bool => mb_strlen($t, 'UTF-8') >= 2 && !in_array($t, CHATBOT_STOPWORDS, true)
    );
    $shared = count(array_intersect($faqTokens, $qTokens));
    $score += min(1.0, $shared * 0.5);

    return round($score, 2);
}

/** کلمه‌های نیت (intent) — با همان نرمال‌سازی و بدون فاصله مقایسه می‌شوند. */
const CHATBOT_WORDS_HOW = ['کجا', 'چطور', 'چگونه', 'چجوری', 'چه طور'];
const CHATBOT_WORDS_STATUS = ['وضعیت', 'پیگیری', 'تیکتم', 'تیکت های من', 'تیکتهای من', 'تیکت من', 'درخواستم', 'درخواست های من', 'درخواستهای من', 'چی شد', 'چی شده', 'جواب تیکت', 'نتیجه تیکت'];
const CHATBOT_WORDS_CREATE = ['ثبت تیکت', 'تیکت جدید', 'تیکت بزنم', 'تیکت باز کنم', 'درخواست جدید', 'ثبت درخواست', 'تیکت ثبت کن', 'تیکت ثبت کنم', 'تیکت بفرستم'];
const CHATBOT_WORDS_TICKET = ['تیکت', 'درخواست', 'شماره پیگیری', 'پیگیری'];
const CHATBOT_WORDS_CANCEL = ['انصراف', 'لغو', 'بیخیال', 'ولش کن'];
const CHATBOT_FLOW_TTL = 900;
const CHATBOT_WORDS_GREET = ['سلام', 'درود', 'صبح بخیر', 'عصر بخیر', 'شب بخیر', 'خداحافظ', 'خدافظ'];
const CHATBOT_WORDS_THANKS = ['ممنون', 'مرسی', 'متشکرم', 'سپاس', 'تشکر'];
const CHATBOT_SCOPE_FALLBACK = 'من فقط دربارهٔ کارهای همین سامانه راهنمایی می‌کنم؛ مثلاً ثبت و پیگیری تیکت، سفارش غذا، فیش، کنترل تردد، CD/DVD، گزارش‌ها یا به‌روزرسانی. سؤال خود را دربارهٔ یکی از این موارد بنویسید، یا «ثبت تیکت» را بنویسید تا از کارشناسان کمک بگیرید.';

function chatbot_has_any(string $normalized, array $words): bool
{
    $compact = chatbot_compact($normalized);
    foreach ($words as $word) {
        $needle = chatbot_compact(chatbot_normalize($word));
        if ($needle !== '' && str_contains($compact, $needle)) {
            return true;
        }
    }
    return false;
}

/** ثبت پاسخ در لاگ و ساختن خروجی یکسان. */
function chatbot_reply(array $user, string $question, string $answer, bool $matched, ?int $faqId, ?string $intent, float $score = 0.0, ?array $link = null): array
{
    $stmt = db()->prepare('INSERT INTO chatbot_logs (user_id, question, faq_id, intent, score, created_at) VALUES (?, ?, ?, ?, ?, NOW())');
    $stmt->execute([(int) $user['id'], $question, $faqId, $intent, $score]);
    return [
        'matched' => $matched,
        'faq_id' => $faqId,
        'answer' => $answer,
        'link' => $link,
        'log_id' => (int) db()->lastInsertId(),
    ];
}

function chatbot_flow_active(): ?array
{
    $flow = $_SESSION['chatbot_flow'] ?? null;
    if (!is_array($flow)) {
        return null;
    }
    if (time() - (int) ($flow['at'] ?? 0) > CHATBOT_FLOW_TTL) {
        unset($_SESSION['chatbot_flow']);
        return null;
    }
    return $flow;
}

/** گام‌های ثبت تیکت از داخل بات: موضوع ← شرح ← انتقال به فرم موجود ثبت تیکت. */
function chatbot_flow_step(array $flow, array $user, string $question): array
{
    if (chatbot_has_any(chatbot_normalize($question), CHATBOT_WORDS_CANCEL)) {
        unset($_SESSION['chatbot_flow']);
        return chatbot_reply($user, $question, 'ثبت تیکت لغو شد. هر وقت خواستید دوباره «ثبت تیکت» را بنویسید.', true, null, 'ticket_cancel');
    }
    if (!user_can($user, 'ticket.create')) {
        unset($_SESSION['chatbot_flow']);
        return chatbot_reply($user, $question, 'دسترسی ثبت تیکت برای حساب شما فعال نیست.', false, null, 'ticket_create');
    }
    if (($flow['step'] ?? '') === 'subject') {
        if (mb_strlen($question, 'UTF-8') < 3) {
            return chatbot_reply($user, $question, 'موضوع کمی کوتاه است. لطفاً موضوع درخواست را در یک جمله بنویسید.', true, null, 'ticket_create');
        }
        $_SESSION['chatbot_flow'] = ['step' => 'description', 'subject' => mb_substr($question, 0, 255, 'UTF-8'), 'at' => time()];
        return chatbot_reply($user, $question, 'خوب. حالا شرح مشکل یا درخواست را بنویسید: چه کاری می‌خواهید انجام دهید و چه چیزی کار نمی‌کند؟', true, null, 'ticket_create');
    }
    if (mb_strlen($question, 'UTF-8') < 5) {
        return chatbot_reply($user, $question, 'شرح کوتاه است؛ لطفاً کمی بیشتر توضیح دهید.', true, null, 'ticket_create');
    }
    $subject = (string) ($flow['subject'] ?? '');
    unset($_SESSION['chatbot_flow']);
    $link = [
        'url' => 'index.php?page=new-ticket&' . http_build_query(['subject' => $subject, 'description' => mb_substr($question, 0, 5000, 'UTF-8')]),
        'label' => 'ادامه و ثبت نهایی تیکت',
    ];
    return chatbot_reply($user, $question, 'متن درخواست آماده شد. با دکمهٔ زیر فرم ثبت تیکت باز می‌شود؛ نوع خدمت را انتخاب کنید و ثبت نهایی را بزنید.', true, null, 'ticket_create', 0.0, $link);
}

/** شماره تیکت (مثل SBU1-86-000123) یا شناسهٔ عددی را از پرسش بیرون می‌کشد. */
function chatbot_ticket_ref(string $question): array
{
    $latin = strtr($question, ['۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4', '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9', '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4', '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9']);
    if (preg_match('/[A-Za-z]+\d*(?:-\d+)+/', $latin, $m)) {
        return ['no' => strtoupper($m[0])];
    }
    if (preg_match_all('/\d+/', $latin, $nums) && $nums[0]) {
        $largest = max(array_map('intval', $nums[0]));
        return $largest > 0 ? ['id' => $largest] : [];
    }
    return [];
}

/** وضعیت یک تیکت مشخص؛ فقط اگر کاربر طبق ticket_scope (همان قاعدهٔ صفحهٔ تیکت‌ها) دسترسی دارد. */
function chatbot_ticket_status(array $user, array $ref): ?array
{
    [$where, $params] = ticket_scope($user, 't');
    $condition = isset($ref['no']) ? 'UPPER(t.ticket_no) = ?' : 't.id = ?';
    $params[] = isset($ref['no']) ? $ref['no'] : (int) $ref['id'];
    $sql = 'SELECT t.id, t.ticket_no, t.subject, t.status, t.updated_at FROM tickets t '
        . ($where !== '' ? $where . ' AND ' : 'WHERE ') . $condition . ' LIMIT 1';
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    $row = $stmt->fetch();
    return $row ?: null;
}

function chatbot_ticket_line(array $row): string
{
    $number = trim((string) ($row['ticket_no'] ?: ticket_number((int) $row['id'])));
    return $number . ' | ' . $row['subject'] . ' | وضعیت: ' . status_label((string) $row['status']) . ' | به‌روزرسانی: ' . persian_date((string) $row['updated_at']);
}

/** بهترین پاسخ را پیدا می‌کند؛ اول نیت‌ها (تیکت و پیگیری)، بعد پرسش‌های متداول. */
function chatbot_answer(string $question, array $user): array
{
    $question = mb_substr(trim($question), 0, 500, 'UTF-8');
    $norm = chatbot_normalize($question);

    $flow = chatbot_flow_active();
    if ($flow !== null) {
        return chatbot_flow_step($flow, $user, $question);
    }

    // سلام و تشکر: پاسخ کوتاه و در محدوده (بدون حدس دربارهٔ موضوع‌های خارج از سامانه).
    if (mb_strlen($norm, 'UTF-8') <= 25 && count(explode(' ', $norm)) <= 4) {
        if (chatbot_has_any($norm, CHATBOT_WORDS_GREET)) {
            return chatbot_reply($user, $question, 'سلام! من راهنمای این سامانه هستم. دربارهٔ ثبت تیکت، پیگیری تیکت، سفارش غذا، فیش، تردد، CD/DVD، گزارش‌ها یا به‌روزرسانی بپرسید.', true, null, 'greeting');
        }
        if (chatbot_has_any($norm, CHATBOT_WORDS_THANKS)) {
            return chatbot_reply($user, $question, 'خواهش می‌کنم. اگر سؤال دیگری دربارهٔ سامانه دارید، بپرسید.', true, null, 'greeting');
        }
    }

    $ref = chatbot_ticket_ref($question);
    $asksStatus = chatbot_has_any($norm, CHATBOT_WORDS_STATUS);
    $mentionsTicket = chatbot_has_any($norm, CHATBOT_WORDS_TICKET);
    if ($ref && ($mentionsTicket || $asksStatus)) {
        $row = chatbot_ticket_status($user, $ref);
        $answer = $row
            ? 'وضعیت تیکت ' . chatbot_ticket_line($row)
            : 'تیکتی با این شماره پیدا نشد یا دسترسی شما به آن ندارد. شماره را دقیق از روی پیامک یا فرم تیکت کپی کنید.';
        $link = $row ? ['url' => 'index.php?page=ticket&id=' . (int) $row['id'], 'label' => 'مشاهدهٔ جزئیات تیکت'] : null;
        return chatbot_reply($user, $question, $answer, true, null, 'ticket_status', 0.0, $link);
    }

    // «چطور ثبت تیکت کنم؟» یک سؤال راهنما است؛ فقط درخواست مستقیم فرایند ثبت را شروع می‌کند.
    if (chatbot_has_any($norm, CHATBOT_WORDS_CREATE) && !chatbot_has_any($norm, CHATBOT_WORDS_HOW)) {
        if (!user_can($user, 'ticket.create')) {
            return chatbot_reply($user, $question, 'دسترسی ثبت تیکت برای حساب شما فعال نیست. برای فعال شدن با مدیر سامانه تماس بگیرید.', true, null, 'ticket_create');
        }
        $_SESSION['chatbot_flow'] = ['step' => 'subject', 'at' => time()];
        return chatbot_reply($user, $question, 'با چند سؤال کوتاه تیکت شما را آماده می‌کنم. ابتدا موضوع درخواست را در یک جمله بنویسید. (برای لغو «انصراف» بنویسید)', true, null, 'ticket_create');
    }

    if ($asksStatus && !chatbot_has_any($norm, CHATBOT_WORDS_HOW)) {
        $stmt = db()->prepare('SELECT id, ticket_no, subject, status, updated_at FROM tickets WHERE requester_id = ? ORDER BY updated_at DESC LIMIT 5');
        $stmt->execute([(int) $user['id']]);
        $rows = $stmt->fetchAll();
        $answer = $rows
            ? "آخرین تیکت‌های شما:\n" . implode("\n", array_map('chatbot_ticket_line', $rows))
            : 'شما تیکت ثبت‌شده‌ای ندارید. برای ثبت تیکت جدید، «ثبت تیکت» را بنویسید.';
        return chatbot_reply($user, $question, $answer, true, null, 'ticket_status');
    }

    // سؤال «چطور/کجا …» با عنوان یک بخش راهنما تطابق قوی دارد: راهنمای مرحله‌ای بهتر از پاسخ کوتاه FAQ است.
    if (chatbot_has_any($norm, CHATBOT_WORDS_HOW)) {
        $strong = chatbot_guide_search($question);
        if ($strong !== null && $strong['title_hits'] >= 1) {
            return chatbot_guide_reply($user, $question, $strong);
        }
    }

    $best = null;
    $bestScore = 0.0;
    foreach (db()->query('SELECT id, question, answer, keywords FROM chatbot_faq WHERE is_active = 1')->fetchAll() as $faq) {
        $score = chatbot_score($question, $faq);
        if ($score > $bestScore) {
            $bestScore = $score;
            $best = $faq;
        }
    }
    $matched = $best !== null && $bestScore >= CHATBOT_THRESHOLD;
    if ($matched) {
        return chatbot_reply($user, $question, (string) $best['answer'], true, (int) $best['id'], null, $bestScore);
    }
    $guide = chatbot_guide_search($question);
    if ($guide !== null) {
        return chatbot_guide_reply($user, $question, $guide);
    }
    $suggestions = chatbot_guide_suggestions($question);
    $text = 'متأسفانه پاسخ مشخصی برای این سؤال پیدا نکردم. ';
    if ($suggestions) {
        $text .= "شاید این بخش‌های راهنما کمک کنند:\n- " . implode("\n- ", $suggestions) . "\nمی‌توانید سؤال را کوتاه‌تر یا با کلمه‌های دیگری بپرسید.";
    } else {
        $text .= CHATBOT_SCOPE_FALLBACK;
    }
    return chatbot_reply($user, $question, $text, false, null, null, $bestScore);
}

/**
 * واژه‌های محاوره‌ای و هم‌معنی → واژهٔ رسمی راهنما. کلید: کلمهٔ نرمال‌شده؛ مقدار: فهرست کلمه‌ها.
 * هر کلمهٔ پرسش با این فهرست گسترش می‌یابد تا «میخوام» و «می‌خواهم» یکسان شوند.
 */
const CHATBOT_TERM_MAP = [
    'میخوام' => ['میخواهم'], 'خوام' => ['خواهم'], 'میخوای' => ['میخواهید'], 'بخوام' => ['بخواهم'],
    'نمیشه' => ['نمیشود'], 'نمیتونم' => ['نمیتوانم'],
    'چجوری' => ['چطور', 'چگونه'], 'چطوری' => ['چطور', 'چگونه'], 'چیجوری' => ['چطور', 'چگونه'],
    'کجاست' => ['کجا'], 'کجاس' => ['کجا'], 'کجان' => ['کجا'],
    'رمزمو' => ['رمز'], 'رمزم' => ['رمز'], 'پسورد' => ['رمز'], 'پسوردم' => ['رمز'], 'گذرواژه' => ['رمز'],
    'لاگین' => ['ورود'], 'وارد' => ['ورود'], 'لاگینم' => ['ورود'],
    'تیکته' => ['تیکت'], 'تیکتمو' => ['تیکت'], 'تیکتم' => ['تیکت'], 'تیکتای' => ['تیکت'], 'تیکتها' => ['تیکت'],
    'فیشو' => ['فیش'], 'فیشم' => ['فیش'], 'فیشمو' => ['فیش'],
    'سفارشمو' => ['سفارش'], 'سفارشم' => ['سفارش'], 'سفارشامو' => ['سفارش'], 'غذامو' => ['غذا'], 'غذام' => ['غذا'],
    'پرینت' => ['چاپ'], 'پرینتر' => ['پرینتر', 'چاپ'], 'پرینترم' => ['پرینتر', 'چاپ'], 'پرینترو' => ['پرینتر', 'چاپ'],
    'کنسل' => ['لغو'], 'کنسلش' => ['لغو'], 'حذف' => ['لغو'],
    'پروفایلمو' => ['پروفایل'], 'پروفایلم' => ['پروفایل'], 'پروفایلت' => ['پروفایل'], 'عوض' => ['تغییر'], 'عوضش' => ['تغییر'],
    'بکاپ' => ['پشتیبان'], 'بکآپ' => ['پشتیبان'],
    'آپدیت' => ['به‌روزرسانی', 'بروزرسانی', 'آپدیت'], 'بروزرسانی' => ['به‌روزرسانی'], 'آپدیتو' => ['به‌روزرسانی'],
    'مهمون' => ['مهمان'], 'مهمونی' => ['مهمان'], 
    'سیدی' => ['cd'], 'دیویدی' => ['dvd'],
    'اسلا' => ['sla'], 
    // ۱.۵۱: واژه‌های محاوره‌ای مربوط به امکانات جدید و مشکلات فنی
    'فوری' => ['اضطراری'], 'نیابتی' => ['نیابت'],
    'خرابی' => ['خطا'], 'ارور' => ['خطا'], 'باگ' => ['خطا'], 'ایراد' => ['خطا'], 'مشکلی' => ['مشکل'],
    'کنده' => ['کندی'], 'کند' => ['کندی'], 'سلامتی' => ['سلامت'], 
    'سرچ' => ['جستجو'], 'سرچم' => ['جستجو'], 'جابجا' => ['جابجایی'], 'دراگ' => ['جابجایی'],
    'ویژه' => ['خاص'],
    'افکت' => ['افکت نمایشی'], 'انیمیشن' => ['افکت نمایشی'],
    'پسزمینه' => ['افکت نمایشی'], 'پسزمینهای' => ['افکت نمایشی'], 'بکگراند' => ['افکت نمایشی'],
    'مثلثی' => ['مثلث', 'افکت نمایشی'],
    
];

/** گسترش کلمه‌های پرسش با واژه‌های محاوره‌ای و هم‌معنی. */
function chatbot_expand_words(array $words): array
{
    static $map = null;
    if ($map === null) {
        $map = [];
        foreach (CHATBOT_TERM_MAP as $from => $to) {
            $map[chatbot_normalize((string) $from)] = array_map(static fn($x) => chatbot_normalize((string) $x), $to);
        }
    }
    $out = [];
    foreach ($words as $w) {
        $out[$w] = true;
        foreach ($map[$w] ?? [] as $alt) {
            foreach (explode(' ', $alt) as $part) {
                if ($part !== '') {
                    $out[$part] = true;
                }
            }
        }
    }
    return array_keys($out);
}

/** فاصلهٔ ویرایشی چندبایتی (برای فارسی). */
function chatbot_edit_distance(string $a, string $b): int
{
    $s = preg_split('//u', $a, -1, PREG_SPLIT_NO_EMPTY) ?: [];
    $r = preg_split('//u', $b, -1, PREG_SPLIT_NO_EMPTY) ?: [];
    $prev = range(0, count($r));
    foreach ($s as $i => $cs) {
        $cur = [$i + 1];
        foreach ($r as $j => $cr) {
            $cur[] = min($prev[$j + 1] + 1, $cur[$j] + 1, $prev[$j] + ($cs === $cr ? 0 : 1));
        }
        $prev = $cur;
    }
    return $prev[count($r)];
}

/** کلمه‌های کوتاه؛ برای غلط‌های تایپی ساده (یک حرف اضافه/کم/جابه‌جا) در کلمه‌های بلندتر از ۳ حرف. */
function chatbot_typo_match(string $word, string $text): bool
{
    if (mb_strlen($word, 'UTF-8') < 4) {
        return false;
    }
    foreach (explode(' ', $text) as $tok) {
        if (mb_strlen($tok, 'UTF-8') >= 4 && abs(mb_strlen($tok, 'UTF-8') - mb_strlen($word, 'UTF-8')) <= 1
            && chatbot_edit_distance($word, $tok) <= 1) {
            return true;
        }
    }
    return false;
}

/** کلمه‌های عمومی روزمره که برای جست‌وجوی راهنما بی‌اثرند (مثلاً «هوا امروز چطوره» نباید به «غیبت امروز» برسد). */
const CHATBOT_GUIDE_STOPWORDS = ['کجا', 'کی', 'کدوم', 'کدام', 'چی', 'چیه', 'چیست', 'چند', 'چرا', 'آیا', 'چطوره', 'چطورن', 'چطوریه', 'امروز', 'فردا', 'دیروز', 'الان', 'هوا', 'ساعت', 'لطفا', 'لطفاً', 'کمک', 'بگو', 'بگید', 'بگین', 'میشه', 'بشه', 'میخوام', 'می‌خوام', 'خوبه', 'چنده', 'کجاست'];

/**
 * ایندکس بخش‌های راهنما (help-guide.php): هر عنوان h3/h4 یک واحد است و به عنوان h2 والد لینک می‌شود.
 * از خود فایل راهنما خوانده می‌شود، پس با به‌روزرسانی راهنما بات هم به‌روز می‌شود.
 */
function chatbot_guide_index(): array
{
    static $index = null;
    if ($index !== null) {
        return $index;
    }
    $index = [];
    $html = (string) @file_get_contents(__DIR__ . '/help-guide.php');
    $html = (string) preg_replace('/<style.*?<\/style>/is', '', $html);
    $parts = preg_split('/(<h[234][^>]*>.*?<\/h[234]>)/is', $html, -1, PREG_SPLIT_DELIM_CAPTURE) ?: [];
    $anchor = '';
    $h2Title = '';
    $cur = null;
    foreach ($parts as $part) {
        if (preg_match('/^<h([234])([^>]*)>(.*?)<\/h[234]>$/is', $part, $m)) {
            if ($cur !== null) {
                $index[] = $cur;
            }
            $level = (int) $m[1];
            $title = trim(html_entity_decode(strip_tags($m[3]), ENT_QUOTES, 'UTF-8'));
            if ($level === 2) {
                if (preg_match('/id="([^"]+)"/', $m[2], $im)) {
                    $anchor = $im[1];
                }
                $h2Title = $title;
            }
            $kw = preg_match('/data-kw="([^"]*)"/', $m[2], $km) ? html_entity_decode($km[1], ENT_QUOTES, 'UTF-8') : '';
            $cur = ['title' => $title, 'h2' => $h2Title, 'anchor' => $anchor, 'html' => '', 'kw' => $kw];
        } elseif ($cur !== null) {
            $cur['html'] .= $part;
        }
    }
    if ($cur !== null) {
        $index[] = $cur;
    }
    $out = [];
    foreach ($index as $sec) {
        if ($sec['title'] === '' || $sec['title'] === 'فهرست مطالب') {
            continue;
        }
        $steps = [];
        if (preg_match_all('/<(?:li(?:\s[^>]*)?|div class="step"[^>]*)>(.*?)<\/(?:li|div)>/is', $sec['html'], $mm)) {
            foreach ($mm[1] as $st) {
                $s = trim((string) preg_replace('/\s+/u', ' ', html_entity_decode(preg_replace('/<[^>]+>/', ' ', $st), ENT_QUOTES, 'UTF-8')));
                $s = trim((string) preg_replace('/^[0-9۰-۹]+\s*/u', '', $s));
                if ($s !== '') {
                    $steps[] = $s;
                }
            }
        }
        $text = trim((string) preg_replace('/\s+/u', ' ', html_entity_decode(preg_replace('/<[^>]+>/', ' ', $sec['html']), ENT_QUOTES, 'UTF-8')));
        if (mb_strlen($text, 'UTF-8') < 30 && !$steps) {
            continue;
        }
        $sec['steps'] = $steps;
        $sec['text'] = $text;
        $sec['title_norm'] = chatbot_normalize($sec['title'] . ' ' . ($sec['kw'] ?? ''));
        $sec['body_norm'] = chatbot_normalize($sec['h2'] . ' ' . $text);
        unset($sec['html']);
        $out[] = $sec;
    }
    // بخش‌های اصلی (h2) معمولاً فقط معرفی دارند و مراحل در زیرعنوان‌هایشان است.
    // برای پاسخ کامل، مراحل همهٔ زیرعنوان‌های همان بخش به معرفی آن داده می‌شود (بدون سقف تعداد).
    foreach ($out as $i => $sec) {
        if ($sec['steps'] || $sec['title'] !== $sec['h2']) {
            continue;
        }
        $collected = [];
        foreach ($out as $other) {
            if ($other['h2'] === $sec['h2'] && $other['title'] !== $sec['h2'] && $other['steps']) {
                array_push($collected, ...$other['steps']);
            }
        }
        if ($collected) {
            $out[$i]['steps'] = $collected;
        }
    }
    $index = $out;
    return $index;
}

/** بهترین بخش راهنما برای پرسش، یا null اگر تطابق کافی نیست. */
function chatbot_guide_search(string $question): ?array
{
    $words = [];
    foreach (chatbot_expand_words(explode(' ', chatbot_normalize($question))) as $w) {
        if (mb_strlen($w, 'UTF-8') >= 2 && !in_array($w, CHATBOT_STOPWORDS, true) && !in_array($w, CHATBOT_GUIDE_STOPWORDS, true)) {
            $words[$w] = true;
        }
    }
    if (!$words) {
        return null;
    }
    $best = null;
    $bestScore = 0;
    $bestTitle = 0;
    foreach (chatbot_guide_index() as $sec) {
        $titleHits = 0;
        $bodyHits = 0;
        foreach (array_keys($words) as $w) {
            if (str_contains($sec['title_norm'], $w) || chatbot_typo_match($w, $sec['title_norm'])) {
                $titleHits++;
            } elseif (str_contains($sec['body_norm'], $w)) {
                $bodyHits++;
            }
        }
        // حداقل یک کلمه در عنوان، یا سه کلمهٔ مختلف در متن؛ تا کلمه‌های عمومی مثل «امروز» تطابق نسازند.
        if ($titleHits < 1 && $bodyHits < 3) {
            continue;
        }
        $score = $titleHits * 3 + $bodyHits;
        if ($score > $bestScore) {
            $bestScore = $score;
            $bestTitle = $titleHits;
            $best = $sec;
        }
    }
    return $best !== null ? $best + ['score' => $bestScore, 'title_hits' => $bestTitle] : null;
}

/**
 * وقتی پاسخ دقیق پیدا نشد: تا سه بخش مرتبط راهنما را پیشنهاد می‌دهد (با حداقل یک کلمهٔ مشترک).
 * @return string[] عنوان بخش‌ها
 */
function chatbot_guide_suggestions(string $question, int $limit = 3): array
{
    $words = [];
    foreach (chatbot_expand_words(explode(' ', chatbot_normalize($question))) as $w) {
        if (mb_strlen($w, 'UTF-8') >= 2 && !in_array($w, CHATBOT_STOPWORDS, true) && !in_array($w, CHATBOT_GUIDE_STOPWORDS, true)) {
            $words[$w] = true;
        }
    }
    if (!$words) {
        return [];
    }
    $scored = [];
    foreach (chatbot_guide_index() as $sec) {
        $score = 0;
        foreach (array_keys($words) as $w) {
            if (str_contains($sec['title_norm'], $w)) {
                $score += 3;
            } elseif (str_contains($sec['body_norm'], $w)) {
                $score += 1;
            }
        }
        if ($score > 0) {
            $scored[] = [$score, $sec['title']];
        }
    }
    usort($scored, static fn(array $a, array $b): int => $b[0] <=> $a[0]);
    return array_values(array_unique(array_map(static fn(array $x): string => $x[1], array_slice($scored, 0, $limit))));
}

/** پاسخ راهنمای مرحله‌ای با لینک بخش. */
function chatbot_guide_reply(array $user, string $question, array $guide): array
{
    $link = ['url' => 'index.php?page=help#' . $guide['anchor'], 'label' => 'باز کردن بخش در راهنما'];
    return chatbot_reply($user, $question, chatbot_guide_answer($guide), true, null, 'guide_help', (float) $guide['score'], $link);
}

/** متن پاسخ راهنمای مرحله‌ای برای یک بخش. */
function chatbot_guide_answer(array $sec): string
{
    $lines = ['برای این کار، این بخش از راهنما کمک می‌کند: «' . $sec['title'] . '»'];
    if ($sec['steps']) {
        foreach ($sec['steps'] as $i => $step) {
            $lines[] = ($i + 1) . '. ' . $step;
        }
    } else {
        $lines[] = mb_substr($sec['text'], 0, 1500, 'UTF-8');
    }
    $lines[] = 'اگر این بخش را در منوی خود نمی‌بینید، دسترسی آن برای حساب شما فعال نیست و باید با مدیر سامانه صحبت کنید.';
    return implode("\n", $lines);
}

/** نقطهٔ ورود API بات. ورودی: پرسش (ask) یا بازخورد (feedback). خروجی JSON. */
function chatbot_api_handle(?array $user): void
{
    header('Content-Type: application/json; charset=utf-8');
    $send = static function (int $code, array $payload): void {
        http_response_code($code);
        echo json_encode($payload, JSON_UNESCAPED_UNICODE);
        exit;
    };
    if (!$user) {
        $send(401, ['ok' => false, 'error' => 'ابتدا وارد سامانه شوید.']);
    }
    if (!user_can($user, 'chatbot.use')) {
        $send(403, ['ok' => false, 'error' => 'دسترسی به بات فعال نیست.']);
    }
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        $send(405, ['ok' => false, 'error' => 'فقط POST مجاز است.']);
    }
    $provided = (string) ($_POST['csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? ''));
    if ($provided === '' || !hash_equals(csrf_token(), $provided)) {
        $send(403, ['ok' => false, 'error' => 'نشست منقضی شده است؛ صفحه را دوباره باز کنید.']);
    }
    check_api_rate_limit('chatbot_' . (int) $user['id'], 30, 60);

    $op = (string) ($_GET['op'] ?? 'ask');
    if ($op === 'feedback') {
        $logId = (int) ($_POST['log_id'] ?? 0);
        $helpful = ($_POST['helpful'] ?? '') === '1' ? 1 : 0;
        db()->prepare('UPDATE chatbot_logs SET helpful = ? WHERE id = ? AND user_id = ?')->execute([$helpful, $logId, (int) $user['id']]);
        $send(200, ['ok' => true]);
    }

    $question = trim((string) ($_POST['question'] ?? ''));
    if ($question === '' || mb_strlen($question, 'UTF-8') > 500) {
        $send(400, ['ok' => false, 'error' => 'سؤال را بین ۱ تا ۵۰۰ نویسه وارد کنید.']);
    }
    $result = chatbot_answer($question, $user);
    $send(200, [
        'ok' => true,
        'answer' => $result['answer'],
        'matched' => $result['matched'],
        'link' => $result['link'],
        'log_id' => $result['log_id'],
        'can_ticket' => user_can($user, 'ticket.create'),
    ]);
}

/** ذخیرهٔ یک مدخل FAQ (افزودن یا ویرایش). */
function chatbot_save_faq(array $input, array $admin): void
{
    $id = (int) ($input['id'] ?? 0);
    $question = trim((string) ($input['question'] ?? ''));
    $answer = trim((string) ($input['answer'] ?? ''));
    $keywords = trim((string) ($input['keywords'] ?? ''));
    $active = !empty($input['is_active']) ? 1 : 0;
    if (mb_strlen($question, 'UTF-8') < 3 || mb_strlen($question, 'UTF-8') > 255) {
        throw new RuntimeException('متن پرسش باید بین ۳ تا ۲۵۵ نویسه باشد.');
    }
    if (mb_strlen($answer, 'UTF-8') < 3 || mb_strlen($answer, 'UTF-8') > 5000) {
        throw new RuntimeException('متن پاسخ باید بین ۳ تا ۵۰۰۰ نویسه باشد.');
    }
    if (count(chatbot_keywords($keywords)) === 0) {
        throw new RuntimeException('حداقل یک کلیدواژهٔ معنادار (دو نویسه یا بیشتر) وارد کنید.');
    }
    $keywords = mb_substr($keywords, 0, 1000, 'UTF-8');
    if ($id > 0) {
        db()->prepare('UPDATE chatbot_faq SET question = ?, answer = ?, keywords = ?, is_active = ?, updated_at = NOW() WHERE id = ?')
            ->execute([$question, $answer, $keywords, $active, $id]);
    } else {
        db()->prepare('INSERT INTO chatbot_faq (question, answer, keywords, is_active, created_by, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())')
            ->execute([$question, $answer, $keywords, $active, (int) $admin['id']]);
    }
    save_audit((int) $admin['id'], 'chatbot_faq_saved', null, ['id' => $id, 'question' => $question]);
}

/** صفحهٔ مدیریت بات: مدخل‌ها، سؤال‌های بی‌پاسخ و بازخورد منفی. */
function chatbot_render_admin(array $admin): void
{
    $editId = (int) ($_GET['edit'] ?? 0);
    $prefill = trim((string) ($_GET['q'] ?? ''));
    $editing = null;
    if ($editId > 0) {
        $stmt = db()->prepare('SELECT * FROM chatbot_faq WHERE id = ?');
        $stmt->execute([$editId]);
        $editing = $stmt->fetch() ?: null;
    }

    $faqs = db()->query('SELECT f.*, (SELECT COUNT(*) FROM chatbot_logs l WHERE l.faq_id = f.id) AS asked, (SELECT COUNT(*) FROM chatbot_logs l WHERE l.faq_id = f.id AND l.helpful = 0) AS negative FROM chatbot_faq f ORDER BY f.is_active DESC, f.id')->fetchAll();
    $unanswered = db()->query('SELECT question, COUNT(*) AS total, MAX(created_at) AS last_at FROM chatbot_logs WHERE faq_id IS NULL AND intent IS NULL GROUP BY question ORDER BY total DESC, last_at DESC LIMIT 50')->fetchAll();

    render_header('مدیریت بات پاسخ‌گو', $admin);
    echo '<section class="page-heading"><div><span class="eyebrow">پشتیبانی هوشمند</span><h1>مدیریت بات پاسخ‌گو</h1><p>پرسش‌های متداول و کلیدواژه‌های آن‌ها را مدیریت کنید. سؤال‌های بی‌پاسخ کاربران را از جدول پایین می‌توانید به‌سرعت پاسخ دهید.</p></div></section>';

    // گزارش بازه‌ای: آمار کلی، نیت‌ها، پرتکرارترین مدخل‌ها و بازخورد منفی اخیر (بدون نام کاربر)
    $validDate = static fn(string $v): bool => (bool) preg_match('/^\d{4}-\d{2}-\d{2}$/', $v) && strtotime($v) !== false;
    $from = $validDate((string) ($_GET['from'] ?? '')) ? (string) $_GET['from'] : date('Y-m-d', strtotime('-30 days'));
    $to = $validDate((string) ($_GET['to'] ?? '')) ? (string) $_GET['to'] : date('Y-m-d');
    $range = [$from . ' 00:00:00', $to . ' 23:59:59'];
    $statStmt = db()->prepare('SELECT COUNT(*) AS total, SUM(faq_id IS NOT NULL) AS answered, SUM(intent IS NOT NULL) AS intents, SUM(faq_id IS NULL AND intent IS NULL) AS unanswered, SUM(helpful = 1) AS yes_count, SUM(helpful = 0) AS no_count FROM chatbot_logs WHERE created_at BETWEEN ? AND ?');
    $statStmt->execute($range);
    $stats = $statStmt->fetch() ?: [];
    $intentStmt = db()->prepare('SELECT intent, COUNT(*) AS c FROM chatbot_logs WHERE intent IS NOT NULL AND created_at BETWEEN ? AND ? GROUP BY intent ORDER BY c DESC');
    $intentStmt->execute($range);
    $intentRows = $intentStmt->fetchAll();
    $topStmt = db()->prepare('SELECT f.question, COUNT(l.id) AS c, SUM(l.helpful = 0) AS neg FROM chatbot_logs l JOIN chatbot_faq f ON f.id = l.faq_id WHERE l.created_at BETWEEN ? AND ? GROUP BY f.id, f.question ORDER BY c DESC LIMIT 10');
    $topStmt->execute($range);
    $topRows = $topStmt->fetchAll();
    $negStmt = db()->prepare('SELECT l.question, l.created_at, f.question AS faq_question FROM chatbot_logs l LEFT JOIN chatbot_faq f ON f.id = l.faq_id WHERE l.helpful = 0 AND l.created_at BETWEEN ? AND ? ORDER BY l.created_at DESC LIMIT 20');
    $negStmt->execute($range);
    $negRows = $negStmt->fetchAll();
    $intentLabels = ['ticket_create' => 'ثبت تیکت', 'ticket_status' => 'پیگیری تیکت', 'ticket_cancel' => 'انصراف از ثبت تیکت', 'guide_help' => 'راهنمای مرحله‌ای'];

    $total = (int) ($stats['total'] ?? 0);
    $pct = static fn(int $part, int $whole): string => $whole > 0 ? round($part * 100 / $whole) . '٪' : '—';
    echo '<div class="card"><h2>گزارش بازه‌ای</h2>'
        . '<form method="get" action="index.php" class="inline-form"><input type="hidden" name="page" value="chatbot-admin">'
        . 'از <input type="date" name="from" value="' . e($from) . '"> تا <input type="date" name="to" value="' . e($to) . '"> '
        . '<button class="button secondary" type="submit">نمایش</button></form>'
        . '<div class="stat-grid">'
        . '<div><strong>' . $total . '</strong><small>کل پرسش‌ها</small></div>'
        . '<div><strong>' . $pct((int) ($stats['answered'] ?? 0), $total) . '</strong><small>پاسخ از FAQ</small></div>'
        . '<div><strong>' . $pct((int) ($stats['intents'] ?? 0), $total) . '</strong><small>ثبت/پیگیری تیکت</small></div>'
        . '<div><strong>' . $pct((int) ($stats['unanswered'] ?? 0), $total) . '</strong><small>بی‌پاسخ</small></div>'
        . '<div><strong>' . (int) ($stats['yes_count'] ?? 0) . ' / ' . (int) ($stats['no_count'] ?? 0) . '</strong><small>مفید / کمکی نکرد</small></div>'
        . '</div>';
    if ($intentRows) {
        echo '<p class="muted">نیت‌ها: ' . e(implode('، ', array_map(static fn(array $r): string => ($intentLabels[$r['intent']] ?? $r['intent']) . ' (' . (int) $r['c'] . ')', $intentRows))) . '</p>';
    }
    echo '<h3>پرتکرارترین پاسخ‌ها</h3><table><thead><tr><th>پرسش متداول</th><th>دفعات</th><th>کمکی نکرد</th></tr></thead><tbody>';
    foreach ($topRows as $row) {
        echo '<tr><td>' . e($row['question']) . '</td><td>' . (int) $row['c'] . '</td><td>' . (int) $row['neg'] . '</td></tr>';
    }
    echo '</tbody></table>';
    echo '<h3>آخرین بازخوردهای منفی</h3><table><thead><tr><th>سؤال کاربر</th><th>پاسخ داده‌شده</th><th>زمان</th></tr></thead><tbody>';
    foreach ($negRows as $row) {
        echo '<tr><td>' . e($row['question']) . '</td><td>' . e((string) ($row['faq_question'] ?? '—')) . '</td><td>' . e(persian_date((string) $row['created_at'])) . '</td></tr>';
    }
    echo '</tbody></table></div>';

    $formValues = $editing ?: ['id' => 0, 'question' => $prefill, 'answer' => '', 'keywords' => '', 'is_active' => 1];
    echo '<div class="card form-card"><h2>' . ($editing ? 'ویرایش مدخل #' . (int) $editing['id'] : 'افزودن مدخل جدید') . '</h2>'
        . '<form method="post" action="index.php?page=chatbot-admin">' . csrf_field()
        . '<input type="hidden" name="action" value="save_chatbot_faq"><input type="hidden" name="id" value="' . (int) $formValues['id'] . '">'
        . '<label class="field">پرسش متداول<input name="question" required maxlength="255" value="' . e((string) $formValues['question']) . '"></label>'
        . '<label class="field">پاسخ<textarea name="answer" required rows="5" maxlength="5000">' . e((string) $formValues['answer']) . '</textarea></label>'
        . '<label class="field">کلیدواژه‌ها (با ویرگول جدا کنید؛ مترادف‌ها را هم بنویسید)<input name="keywords" required maxlength="1000" value="' . e((string) $formValues['keywords']) . '" placeholder="مثلاً: رمز, پسورد, گذرواژه"></label>'
        . '<label class="check"><input type="checkbox" name="is_active" value="1"' . ((int) $formValues['is_active'] === 1 ? ' checked' : '') . '> فعال</label>'
        . '<button class="button" type="submit">ذخیره</button>'
        . ($editing ? ' <a class="button secondary" href="index.php?page=chatbot-admin">انصراف</a>' : '')
        . '</form></div>';

    echo '<div class="card"><h2>مدخل‌ها</h2><table><thead><tr><th>پرسش</th><th>کلیدواژه‌ها</th><th>وضعیت</th><th>دفعات پرسش</th><th>بازخورد منفی</th><th></th></tr></thead><tbody>';
    foreach ($faqs as $faq) {
        echo '<tr><td>' . e($faq['question']) . '</td><td>' . e($faq['keywords']) . '</td><td>' . ((int) $faq['is_active'] === 1 ? 'فعال' : 'غیرفعال') . '</td><td>' . (int) $faq['asked'] . '</td><td>' . (int) $faq['negative'] . '</td><td><a href="index.php?page=chatbot-admin&edit=' . (int) $faq['id'] . '">ویرایش</a></td></tr>';
    }
    echo '</tbody></table></div>';

    echo '<div class="card"><h2>سؤال‌های بی‌پاسخ</h2><p class="muted">این سؤال‌ها با هیچ مدخلی تطبیق نشده‌اند. با «افزودن پاسخ» فرم بالا با همان سؤال پر می‌شود.</p><table><thead><tr><th>سؤال کاربر</th><th>تعداد</th><th>آخرین بار</th><th></th></tr></thead><tbody>';
    foreach ($unanswered as $row) {
        echo '<tr><td>' . e($row['question']) . '</td><td>' . (int) $row['total'] . '</td><td>' . e(persian_date((string) $row['last_at'])) . '</td><td><a href="index.php?page=chatbot-admin&q=' . rawurlencode((string) $row['question']) . '">افزودن پاسخ</a></td></tr>';
    }
    echo '</tbody></table></div>';
    render_footer();
    exit;
}

/** ویجت گفتگو برای کاربران دارای chatbot.use. */
function chatbot_render_widget(?array $user): string
{
    if (!$user || !user_can($user, 'chatbot.use')) {
        return '';
    }
    $token = json_encode(csrf_token(), JSON_HEX_TAG | JSON_HEX_AMP);
    return '<style>
.cb-bot-launch{position:fixed;left:20px;bottom:18px;width:150px;height:150px;z-index:9999;cursor:pointer;user-select:none;outline:none}.cb-bot-body{position:absolute;inset:0;animation:cbBob 3.2s ease-in-out infinite}@keyframes cbBob{0%,100%{transform:translateY(0)}50%{transform:translateY(-5px)}}.cb-layer{position:absolute;inset:0;width:100%;height:100%;display:block;pointer-events:none}.cb-arm{transform-origin:32% 50%;transform:rotate(0deg)}.cb-bot-launch.cb-wave .cb-arm{animation:cbWave .5s ease-in-out 0s 5}@keyframes cbWave{0%{transform:rotate(118deg)}50%{transform:rotate(140deg)}100%{transform:rotate(118deg)}}.cb-bot-launch.cb-buzz .cb-bot-body{animation:cbShake .35s linear 2}@keyframes cbShake{0%{transform:translate(0,0)}25%{transform:translate(-3px,1px)}50%{transform:translate(3px,-1px)}75%{transform:translate(-2px,1px)}100%{transform:none}}.cb-bubble{position:absolute;left:150px;bottom:110px;background:#fff;color:#0f3d3e;border-radius:14px;padding:10px 13px;font-size:14px;font-weight:700;line-height:1.9;white-space:nowrap;box-shadow:0 8px 22px rgba(0,0,0,.25);opacity:0;transform:translateY(6px) scale(.96);transition:.25s;pointer-events:none}.cb-bubble::before{content:"";position:absolute;left:-7px;bottom:16px;border:7px solid transparent;border-right-color:#fff;border-left:0}.cb-bubble.show{opacity:1;transform:none}
.cb-panel{position:fixed;left:18px;bottom:180px;z-index:9999;width:min(360px,calc(100vw - 36px));height:460px;display:none;flex-direction:column;background:#fff;border:1px solid #e2e8f0;border-radius:16px;box-shadow:0 16px 40px rgba(0,0,0,.25);overflow:hidden;direction:rtl}
.cb-panel.open{display:flex}
.cb-panel.cb-big{width:min(720px,calc(100vw - 36px));height:min(920px,calc(100vh - 200px))}
.cb-head{background:#0f766e;color:#fff;padding:12px 14px;font-weight:700;display:flex;align-items:center;justify-content:space-between;gap:8px}
.cb-resize{border:0;background:rgba(255,255,255,.2);color:#fff;border-radius:8px;padding:3px 9px;cursor:pointer;font:inherit;font-size:13px}
.cb-log{flex:1;overflow-y:auto;padding:12px;display:flex;flex-direction:column;gap:8px;background:#f8fafc}
.cb-msg{max-width:85%;padding:8px 11px;border-radius:12px;white-space:pre-wrap;line-height:1.8;font-size:14px}
.cb-user{align-self:flex-start;background:#dbeafe}
.cb-bot{align-self:flex-end;background:#fff;border:1px solid #e2e8f0}
.cb-fb{display:flex;gap:6px;margin-top:4px}
.cb-fb button{border:1px solid #cbd5e1;background:#fff;border-radius:8px;padding:2px 8px;cursor:pointer;font:inherit;font-size:12px}
.cb-form{display:flex;gap:6px;padding:10px;border-top:1px solid #e2e8f0}
.cb-form input{flex:1;padding:8px 10px;border:1px solid #cbd5e1;border-radius:10px;font:inherit}
.cb-form button{background:#0f766e;color:#fff;border:0;border-radius:10px;padding:8px 14px;cursor:pointer;font:inherit}
</style>
<div class="cb-bot-launch" id="cb-open" role="button" tabindex="0" aria-label="راهنمای هوشمند" title="راهنمای هوشمند"><div class="cb-bubble" id="cb-bubble">سلام<br>کمکی از دستم بر میاد ؟؟؟</div><div class="cb-bot-body"><img class="cb-layer" alt="" src="assets/chatbot/robot-body.png"><img class="cb-layer cb-arm" alt="" src="assets/chatbot/robot-arm.png"></div></div>
<div class="cb-panel" id="cb-panel" role="dialog" aria-label="بات پاسخ‌گو">
  <div class="cb-head"><span>بات پاسخ‌گوی سامانه</span><span style="display:flex;gap:6px"><button class="cb-resize" type="button" id="cb-resize" title="بزرگ/کوچک">⤢</button><button class="cb-resize" type="button" id="cb-close" aria-label="بستن" title="بستن">✕</button></span></div>
  <div class="cb-log" id="cb-log"><div class="cb-msg cb-bot">سلام! سؤال خود را درباره کار با سامانه بنویسید.</div></div>
  <form class="cb-form" id="cb-form"><input id="cb-input" maxlength="500" placeholder="سؤال خود را بنویسید..." autocomplete="off"><button type="submit">ارسال</button></form>
</div>
<script nonce="' . e(csp_nonce()) . '">
(function(){
  var csrf = ' . $token . ';
  var panel = document.getElementById("cb-panel"), log = document.getElementById("cb-log");
  var form = document.getElementById("cb-form"), input = document.getElementById("cb-input");
  var bot = document.getElementById("cb-open"), bubble = document.getElementById("cb-bubble"), ac = null;
  function beep(){ try { ac = ac || new (window.AudioContext || window.webkitAudioContext)(); var o = ac.createOscillator(), g = ac.createGain(); o.type = "sawtooth"; o.frequency.value = 130; g.gain.setValueAtTime(0.0001, ac.currentTime); g.gain.exponentialRampToValueAtTime(0.2, ac.currentTime + 0.02); g.gain.exponentialRampToValueAtTime(0.0001, ac.currentTime + 0.45); o.connect(g); g.connect(ac.destination); o.start(); o.stop(ac.currentTime + 0.5); } catch (e) {} }
  function greet(){ if (panel.classList.contains("open")) return; bot.classList.remove("cb-wave", "cb-buzz"); void bot.offsetWidth; bot.classList.add("cb-wave", "cb-buzz"); bubble.classList.add("show"); beep(); setTimeout(function(){ bubble.classList.remove("show"); }, 5000); setTimeout(function(){ bot.classList.remove("cb-wave"); }, 2700); }
  function toggleBot(){ panel.classList.toggle("open"); bubble.classList.remove("show"); if (panel.classList.contains("open")) input.focus(); }
  bot.addEventListener("click", toggleBot);
  bot.addEventListener("keydown", function(e){ if (e.key === "Enter" || e.key === " ") { e.preventDefault(); toggleBot(); } });
  setInterval(greet, 60000);
  document.getElementById("cb-resize").addEventListener("click", function(){ var big = panel.classList.toggle("cb-big"); this.textContent = big ? "⤡" : "⤢"; });
  function add(text, cls){ var d = document.createElement("div"); d.className = "cb-msg " + cls; d.textContent = text; log.appendChild(d); log.scrollTop = log.scrollHeight; return d; }
  function post(op, params){
    var body = new URLSearchParams(params); body.append("csrf", csrf);
    return fetch("index.php?page=chatbot-api&op=" + op, {method:"POST", body: body, credentials:"same-origin"}).then(function(r){ return r.json(); });
  }
  function feedback(wrap, logId){
    var row = document.createElement("div"); row.className = "cb-fb";
    [["1","مفید بود"],["0","کمکی نکرد"]].forEach(function(p){
      var b = document.createElement("button"); b.type = "button"; b.textContent = p[1];
      b.addEventListener("click", function(){ row.textContent = "ممنون از بازخوردتان."; post("feedback", {log_id: logId, helpful: p[0]}); });
      row.appendChild(b);
    });
    wrap.appendChild(row);
  }
  form.addEventListener("submit", function(e){
    e.preventDefault();
    var q = input.value.trim(); if (!q) return;
    add(q, "cb-user"); input.value = "";
    var pending = add("در حال جست‌وجو...", "cb-bot");
    post("ask", {question: q}).then(function(j){
      pending.textContent = j.ok ? j.answer : (j.error || "خطا در ارتباط با بات.");
      if (j.ok) {
        feedback(pending, j.log_id);
        if (j.link) {
          var lk = document.createElement("a"); lk.href = j.link.url; lk.textContent = j.link.label;
          lk.style.display = "inline-block"; lk.style.marginTop = "6px"; pending.appendChild(lk);
        } else if (!j.matched && j.can_ticket) {
          var a = document.createElement("a"); a.href = "index.php?page=new-ticket"; a.textContent = "ثبت تیکت جدید";
          a.style.display = "inline-block"; a.style.marginTop = "6px"; pending.appendChild(a);
        }
      }
    }).catch(function(){ pending.textContent = "ارتباط با سرور برقرار نشد. دوباره تلاش کنید."; });
  });
})();
</script><script src="assets/chatbot-drag.js?v=2" defer></script>';
}
