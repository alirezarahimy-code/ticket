<?php
declare(strict_types=1);

/*
 * آریا، بات سخنگوی سامانه (فاز ۱): تطبیق قانون‌محور با پرسش‌های متداول.
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
const CHATBOT_WORDS_STATUS = ['وضعیت', 'پیگیری', 'تیکتم', 'تیکت های من', 'تیکتهای من', 'تیکت من', 'درخواستم', 'درخواست های من', 'درخواستهای من', 'چی شد', 'چی شده', 'جواب تیکت', 'نتیجه تیکت', 'جواب درخواست', 'نتیجه درخواست'];
const CHATBOT_WORDS_CREATE = ['ثبت تیکت', 'تیکت جدید', 'تیکت بزنم', 'تیکت باز کنم', 'درخواست جدید', 'ثبت درخواست', 'درخواست بزنم', 'درخواست باز کنم', 'درخواست ثبت کن', 'درخواست ثبت کنم', 'درخواست بفرستم', 'تیکت ثبت کن', 'تیکت ثبت کنم', 'تیکت بفرستم'];
const CHATBOT_WORDS_TICKET = ['تیکت', 'درخواست', 'شماره پیگیری', 'پیگیری'];
const CHATBOT_WORDS_CANCEL = ['انصراف', 'لغو', 'بیخیال', 'ولش کن'];
const CHATBOT_FLOW_TTL = 900;
const CHATBOT_WORDS_GREET = ['سلام', 'درود', 'صبح بخیر', 'عصر بخیر', 'شب بخیر', 'خداحافظ', 'خدافظ'];
const CHATBOT_WORDS_THANKS = ['ممنون', 'مرسی', 'متشکرم', 'سپاس', 'تشکر'];
const CHATBOT_WORDS_ABOUT = ['خودت را معرفی', 'خودت رو معرفی', 'خودتان را معرفی', 'معرفی کن', 'معرفی کنی', 'معرفی خود', 'تو کی هستی', 'شما کی هستید', 'کی هستی', 'اسمت چیه', 'اسم شما چیست', 'چه کار میکنی', 'چیکار میکنی', 'چه کاری میکنی', 'آریا کی', 'تو چی هستی'];
const CHATBOT_SCOPE_FALLBACK = 'من فقط دربارهٔ کارهای همین سامانه راهنمایی می‌کنم؛ مثلاً ثبت و پیگیری درخواست، سفارش غذا، فیش، کنترل تردد، CD/DVD، گزارش‌ها یا به‌روزرسانی. سؤال خود را دربارهٔ یکی از این موارد بنویسید، یا «ثبت درخواست» را بنویسید تا از کارشناسان کمک بگیرید.';

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

/** گام‌های ثبت درخواست از داخل بات: موضوع ← شرح ← انتقال به فرم موجود ثبت درخواست. */
function chatbot_flow_step(array $flow, array $user, string $question): array
{
    if (chatbot_has_any(chatbot_normalize($question), CHATBOT_WORDS_CANCEL)) {
        unset($_SESSION['chatbot_flow']);
        return chatbot_reply($user, $question, 'ثبت درخواست لغو شد. هر وقت خواستید دوباره «ثبت درخواست» را بنویسید.', true, null, 'ticket_cancel');
    }
    if (!user_can($user, 'ticket.create')) {
        unset($_SESSION['chatbot_flow']);
        return chatbot_reply($user, $question, 'دسترسی ثبت درخواست برای حساب شما فعال نیست.', false, null, 'ticket_create');
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
        'label' => 'ادامه و ثبت نهایی درخواست',
    ];
    return chatbot_reply($user, $question, 'متن درخواست آماده شد. با دکمهٔ زیر فرم ثبت درخواست باز می‌شود؛ نوع خدمت را انتخاب کنید و ثبت نهایی را بزنید.', true, null, 'ticket_create', 0.0, $link);
}

/** شماره درخواست (مثل SBU1-86-000123) یا شناسهٔ عددی را از پرسش بیرون می‌کشد. */
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

/** وضعیت یک درخواست مشخص؛ فقط اگر کاربر طبق ticket_scope (همان قاعدهٔ صفحهٔ درخواست‌ها) دسترسی دارد. */
function chatbot_ticket_status(array $user, array $ref): ?array
{
    [$where, $params] = ticket_scope($user, 't');
    $condition = isset($ref['no']) ? 'UPPER(t.ticket_no) = ?' : 't.id = ?';
    $params[] = isset($ref['no']) ? $ref['no'] : (int) $ref['id'];
    $sql = 'SELECT t.id, t.ticket_no, t.subject, t.status, t.updated_at, t.cancel_requested_at, t.assigned_to, t.first_response_at, t.resolved_at FROM tickets t '
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

/** بهترین پاسخ را پیدا می‌کند؛ اول مهلت درخواست غذای مهمان و نیت‌های درخواست، بعد پرسش‌های متداول. */
function chatbot_answer(string $question, array $user): array
{
    $question = mb_substr(trim($question), 0, 500, 'UTF-8');
    $norm = chatbot_normalize($question);

    $flow = chatbot_flow_active();
    if ($flow !== null) {
        return chatbot_flow_step($flow, $user, $question);
    }

    if (chatbot_has_any($norm, CHATBOT_WORDS_ABOUT)) {
        $botName = chatbot_widget_config()['name'];
        $about = $botName . ' هستم؛ راهنمای همین سامانه. دربارهٔ ثبت و پیگیری درخواست، سفارش غذا، فیش، کنترل تردد، CD/DVD، گزارش‌ها و به‌روزرسانی می‌توانم کمک کنم. سؤال مشخص خود را بنویسید؛ اگر ندانم، حدس نمی‌زنم و بخش بی‌ربط راهنما را نمی‌خوانم.';
        return chatbot_reply($user, $question, $about, true, null, 'about');
    }

    // سلام و تشکر: پاسخ کوتاه و در محدوده (بدون حدس دربارهٔ موضوع‌های خارج از سامانه).
    if (mb_strlen($norm, 'UTF-8') <= 25 && count(explode(' ', $norm)) <= 4) {
        if (chatbot_has_any($norm, CHATBOT_WORDS_GREET)) {
            return chatbot_reply($user, $question, 'سلام! من راهنمای این سامانه هستم. دربارهٔ ثبت درخواست، پیگیری درخواست، سفارش غذا، فیش، تردد، CD/DVD، گزارش‌ها یا به‌روزرسانی بپرسید.', true, null, 'greeting');
        }
        if (chatbot_has_any($norm, CHATBOT_WORDS_THANKS)) {
            return chatbot_reply($user, $question, 'خواهش می‌کنم. اگر سؤال دیگری دربارهٔ سامانه دارید، بپرسید.', true, null, 'greeting');
        }
    }

    $mentionsGuestRequest = chatbot_has_any($norm, ['مهمان'])
        && chatbot_has_any($norm, ['درخواست', 'سفارش']);
    $asksGuestRequestDeadline = chatbot_has_any($norm, ['مهلت', 'ساعت', 'تا کی', 'آخرین زمان', 'چه وقت', 'تا چه موقع', 'مجاز']);
    if ($mentionsGuestRequest && $asksGuestRequestDeadline) {
        $cutoff = function_exists('food_guest_request_same_day_cutoff')
            ? food_guest_request_same_day_cutoff()
            : '09:30';
        if (function_exists('food_ticket_fa_digits')) {
            $cutoff = food_ticket_fa_digits($cutoff);
        }
        $answer = 'درخواست غذای مهمان برای امروز تا پایان دقیقهٔ ' . $cutoff
            . ' پذیرفته می‌شود؛ پس از آن، فقط برای روزهای آینده می‌توان درخواست ثبت کرد. این مهلت در «سامانه مدیریت غذا ← تنظیمات ← کارت مهمان» قابل تغییر است. ساعت ۱۲:۰۱ مربوط به محاسبهٔ سقف فیش مهمان است، نه مهلت ثبت درخواست.';
        return chatbot_reply($user, $question, $answer, true, null, 'food_guest_request_deadline', 0.0, [
            'url' => 'index.php?page=food-order',
            'label' => 'رفتن به سفارش غذا',
        ]);
    }

    $mentionsTicket = chatbot_has_any($norm, CHATBOT_WORDS_TICKET);
    $asksTicketCancellation = $mentionsTicket && chatbot_has_any($norm, CHATBOT_WORDS_CANCEL);
    if ($asksTicketCancellation) {
        $cancelRef = chatbot_ticket_ref($question);
        $cancelRow = $cancelRef ? chatbot_ticket_status($user, $cancelRef) : null;
        if (!$cancelRow) {
            $cancelAnswer = user_can($user, 'ticket.create')
                ? 'اگر شما ثبت‌کنندهٔ درخواست هستید، جزئیات درخواست را باز کنید و دلیل لغو را بنویسید. پیش از ارجاع، لغو مستقیم است؛ پس از ارجاع (حتی در وضعیت حل‌شده)، درخواست برای تصمیم مسئول همان حوزه ثبت می‌شود و تا تصمیم او گفت‌وگو ادامه دارد اما تغییر وضعیت و ارجاع متوقف است. لغو، حذف فیزیکی نیست و تاریخچه حفظ می‌شود.'
                : 'لغو درخواست فقط برای ثبت‌کنندهٔ مجاز انجام می‌شود. در صورت ارجاع درخواست، درخواست باید به تأیید مسئول همان حوزه برسد و تا آن زمان گفت‌وگو ادامه دارد اما تغییر وضعیت متوقف است؛ تاریخچه حذف نمی‌شود.';
        } elseif ((string) $cancelRow['status'] === 'cancelled') {
            $cancelAnswer = 'این درخواست قبلاً لغو شده است. تاریخچهٔ آن حفظ می‌شود و برای نیاز تازه باید درخواست جدید ثبت کنید. ' . chatbot_ticket_line($cancelRow);
        } elseif ((string) $cancelRow['status'] === 'closed') {
            $cancelAnswer = 'این درخواست بسته شده و از مسیر لغو قابل لغو نیست. اگر مشکل همچنان باقی است، از جزئیات درخواست گزینهٔ بازگشایی/اعلام حل‌نشدن را بررسی کنید. ' . chatbot_ticket_line($cancelRow);
        } elseif (!empty($cancelRow['cancel_requested_at'])) {
            $cancelAnswer = 'لغو این درخواست در انتظار تصمیم مسئول است؛ تا آن زمان گفت‌وگو ادامه دارد اما تغییر وضعیت و ارجاع متوقف است. ' . chatbot_ticket_line($cancelRow);
        } elseif (in_array((string) $cancelRow['status'], ['new', 'manager_review'], true) && empty($cancelRow['assigned_to']) && empty($cancelRow['first_response_at']) && empty($cancelRow['resolved_at'])) {
            $cancelAnswer = 'این درخواست هنوز به کارشناس ارجاع نشده و رسیدگی آغاز نشده است؛ ثبت‌کنندهٔ مجاز می‌تواند از صفحهٔ جزئیات آن را مستقیماً لغو کند. سابقه حذف نمی‌شود. ' . chatbot_ticket_line($cancelRow);
        } else {
            $cancelAnswer = 'چون درخواست ارجاع شده یا رسیدگی در جریان است، ثبت‌کننده می‌تواند درخواست لغو را با ذکر دلیل ثبت کند؛ تا تصمیم مسئول همان حوزه، گفت‌وگو ادامه دارد اما تغییر وضعیت و ارجاع متوقف است. ' . chatbot_ticket_line($cancelRow);
        }
        $cancelLink = $cancelRow
            ? ['url' => 'index.php?page=ticket&id=' . (int) $cancelRow['id'], 'label' => 'بازکردن جزئیات درخواست']
            : ['url' => 'index.php', 'label' => 'رفتن به فهرست درخواست‌ها'];
        return chatbot_reply($user, $question, $cancelAnswer, true, null, 'ticket_cancellation', 0.0, $cancelLink);
    }

    $ref = chatbot_ticket_ref($question);
    $asksStatus = chatbot_has_any($norm, CHATBOT_WORDS_STATUS);
    if ($ref && ($mentionsTicket || $asksStatus)) {
        $row = chatbot_ticket_status($user, $ref);
        $answer = $row
            ? 'وضعیت درخواست ' . chatbot_ticket_line($row)
            : 'درخواستی با این شماره پیدا نشد یا دسترسی شما به آن ندارد. شماره را دقیق از روی پیامک یا فرم درخواست کپی کنید.';
        $link = $row ? ['url' => 'index.php?page=ticket&id=' . (int) $row['id'], 'label' => 'مشاهدهٔ جزئیات درخواست'] : null;
        return chatbot_reply($user, $question, $answer, true, null, 'ticket_status', 0.0, $link);
    }

    // «چطور ثبت درخواست کنم؟» یک سؤال راهنما است؛ فقط درخواست مستقیم فرایند ثبت را شروع می‌کند.
    if (chatbot_has_any($norm, CHATBOT_WORDS_CREATE) && !chatbot_has_any($norm, CHATBOT_WORDS_HOW)) {
        if (!user_can($user, 'ticket.create')) {
            return chatbot_reply($user, $question, 'دسترسی ثبت درخواست برای حساب شما فعال نیست. برای فعال شدن با مدیر سامانه تماس بگیرید.', true, null, 'ticket_create');
        }
        $_SESSION['chatbot_flow'] = ['step' => 'subject', 'at' => time()];
        return chatbot_reply($user, $question, 'با چند سؤال کوتاه درخواست شما را آماده می‌کنم. ابتدا موضوع درخواست را در یک جمله بنویسید. (برای لغو «انصراف» بنویسید)', true, null, 'ticket_create');
    }

    if ($asksStatus && !chatbot_has_any($norm, CHATBOT_WORDS_HOW)) {
        $stmt = db()->prepare('SELECT id, ticket_no, subject, status, updated_at FROM tickets WHERE requester_id = ? ORDER BY updated_at DESC LIMIT 5');
        $stmt->execute([(int) $user['id']]);
        $rows = $stmt->fetchAll();
        $answer = $rows
            ? "آخرین درخواست‌های شما:\n" . implode("\n", array_map('chatbot_ticket_line', $rows))
            : 'شما درخواست ثبت‌شده‌ای ندارید. برای ثبت درخواست جدید، «ثبت درخواست» را بنویسید.';
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
    // فقط وقتی عنوان بخش واقعاً در سؤال آمده؛ تطابق بدنه به‌تنهایی جواب کامل نمی‌سازد.
    if ($guide !== null && (int) $guide['title_hits'] >= 1) {
        return chatbot_guide_reply($user, $question, $guide);
    }
    $suggestions = chatbot_guide_suggestions($question);
    $text = chatbot_widget_config()['unmatched_text'] . ' ';
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
    // ورود گروهی سوابق قدیمی کنترل تردد از فایل Excel/CSV (فرم مستقیم و محدود به مجوز مدیریت تردد).
    'آپلود' => ['بارگذاری'], 'آپلودو' => ['بارگذاری'], 'ایمپورت' => ['ورود اطلاعات', 'بارگذاری'],
    'اکسل' => ['Excel', 'xlsx'], 'ترددهای' => ['تردد', 'سوابق'], 'سوابق' => ['سوابق تردد'],
    'گذشته' => ['قدیمی'], 'قدیمیه' => ['قدیمی', 'گذشته'],
    'افکت' => ['افکت نمایشی'], 'انیمیشن' => ['افکت نمایشی'],
    'پسزمینه' => ['افکت نمایشی'], 'پسزمینهای' => ['افکت نمایشی'], 'بکگراند' => ['افکت نمایشی'],
    'مثلثی' => ['مثلث', 'افکت نمایشی'], 'ذرات' => ['ذرات معلق', 'افکت نمایشی'],
    'ذره' => ['ذرات معلق', 'افکت نمایشی'], 'معلق' => ['ذرات معلق', 'افکت نمایشی'],
    'پارتیکل' => ['ذرات معلق', 'افکت نمایشی'], 'particle' => ['ذرات معلق', 'افکت نمایشی'],

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
const CHATBOT_GUIDE_STOPWORDS = ['کجا', 'کی', 'کدوم', 'کدام', 'چی', 'چیه', 'چیست', 'چند', 'چرا', 'آیا', 'چطوره', 'چطورن', 'چطوریه', 'امروز', 'فردا', 'دیروز', 'الان', 'هوا', 'ساعت', 'لطفا', 'لطفاً', 'کمک', 'بگو', 'بگید', 'بگین', 'میشه', 'بشه', 'میخوام', 'می‌خوام', 'خوبه', 'چنده', 'کجاست', 'خودت', 'خودم', 'شما', 'کامل', 'بکن', 'کنم', 'کنید', 'میکنم', 'هستی', 'هستم', 'باشه', 'میخواد'];

/** واژهٔ کامل در متن نرمال‌شده (نه زیررشتهٔ «کن» داخل «کنید»). */
function chatbot_contains_word(string $haystack, string $needle): bool
{
    if ($needle === '' || $haystack === '') {
        return false;
    }
    return (bool) preg_match('/(?:^| )' . preg_quote($needle, '/') . '(?: |$)/u', $haystack);
}

/** کلمه‌های معنی‌دار پرسش برای جست‌وجوی راهنما. */
function chatbot_guide_tokens(string $question): array
{
    $words = [];
    foreach (chatbot_expand_words(explode(' ', chatbot_normalize($question))) as $w) {
        if (mb_strlen($w, 'UTF-8') < 3) {
            continue;
        }
        if (in_array($w, CHATBOT_STOPWORDS, true) || in_array($w, CHATBOT_GUIDE_STOPWORDS, true)) {
            continue;
        }
        $words[$w] = true;
    }
    return $words;
}

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
    $words = chatbot_guide_tokens($question);
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
            if (chatbot_contains_word($sec['title_norm'], $w) || chatbot_typo_match($w, $sec['title_norm'])) {
                $titleHits++;
            } elseif (chatbot_contains_word($sec['body_norm'], $w)) {
                $bodyHits++;
            }
        }
        // عنوان باید حداقل یک واژهٔ مشترک داشته باشد؛ سه واژه در بدنه به‌تنهایی کافی نیست.
        if ($titleHits < 1) {
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
    $words = chatbot_guide_tokens($question);
    if (!$words) {
        return [];
    }
    $scored = [];
    foreach (chatbot_guide_index() as $sec) {
        $titleHits = 0;
        foreach (array_keys($words) as $w) {
            if (chatbot_contains_word($sec['title_norm'], $w) || chatbot_typo_match($w, $sec['title_norm'])) {
                $titleHits++;
            }
        }
        if ($titleHits < 1) {
            continue;
        }
        $scored[] = [$titleHits * 3, $sec['title']];
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
    if ((string) ($_GET['op'] ?? 'ask') === 'say_audio') {
        check_api_rate_limit('chatbot_say_audio_' . (int) $user['id'], 60, 60);
        $clipId = (string) ($_GET['id'] ?? '');
        if (!preg_match('/^[0-9a-f]{40}$/', $clipId)) {
            $send(400, ['ok' => false, 'error' => 'شناسهٔ صوتی نامعتبر است.']);
        }
        $clipFile = APP_ROOT . '/storage/tts/' . $clipId . '.wav';
        $clipHead = (is_file($clipFile) && (int) filesize($clipFile) > 100)
            ? (string) @file_get_contents($clipFile, false, null, 0, 12)
            : '';
        if ($clipHead === '' || substr($clipHead, 0, 4) !== 'RIFF' || substr($clipHead, 8, 4) !== 'WAVE') {
            if (is_file($clipFile)) {
                @unlink($clipFile);
            }
            $send(404, ['ok' => false, 'error' => 'فایل صوتی یافت نشد؛ دوباره تلاش کنید.']);
        }
        $clipAudio = (string) file_get_contents($clipFile);
        header('Content-Type: audio/wav', true);
        header('Content-Length: ' . strlen($clipAudio));
        header('Accept-Ranges: bytes');
        header('Cache-Control: public, max-age=31536000, immutable');
        echo $clipAudio;
        exit;
    }
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        $send(405, ['ok' => false, 'error' => 'فقط POST مجاز است.']);
    }
    $provided = (string) ($_POST['csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? ''));
    if ($provided === '' || !hash_equals(csrf_token(), $provided)) {
        $send(403, ['ok' => false, 'error' => 'نشست منقضی شده است؛ صفحه را دوباره باز کنید.']);
    }
    check_api_rate_limit('chatbot_' . (int) $user['id'], chatbot_widget_config()['rate_limit'], 60);

    $op = (string) ($_GET['op'] ?? 'ask');
    if ($op === 'feedback') {
        $logId = (int) ($_POST['log_id'] ?? 0);
        $helpful = ($_POST['helpful'] ?? '') === '1' ? 1 : 0;
        db()->prepare('UPDATE chatbot_logs SET helpful = ? WHERE id = ? AND user_id = ?')->execute([$helpful, $logId, (int) $user['id']]);
        $send(200, ['ok' => true]);
    }
    if ($op === 'voice-status') {
        $send(200, ['ok' => true, 'voice' => chatbot_tts_status()]);
    }
    if ($op === 'say') {
        $sayText = trim((string) ($_POST['text'] ?? ''));
        if ($sayText === '' || mb_strlen($sayText, 'UTF-8') > 2000) {
            $send(400, ['ok' => false, 'error' => 'متن صوتی باید بین ۱ تا ۲۰۰۰ نویسه باشد.']);
        }
        $spoken = chatbot_tts_say($sayText);
        if (!$spoken['ok']) {
            $send(503, ['ok' => false, 'error' => $spoken['error']]);
        }
        $send(200, ['ok' => true, 'url' => 'index.php?page=chatbot-api&op=say_audio&id=' . $spoken['id']]);
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

/** ذخیرهٔ تنظیمات گویندگی بات (صفحهٔ مدیریت بات). */
function chatbot_save_tts(array $input, array $admin): void
{
    $mode = valid_choice(trim((string) ($input['tts_mode'] ?? 'auto')), ['auto', 'server', 'browser', 'off'], 'auto');
    $speed = max(80, min(300, (int) ($input['tts_speed'] ?? 140)));
    $voice = valid_choice(trim((string) ($input['tts_voice'] ?? 'default')), ['default', 'male', 'female'], 'default');
    $path = trim((string) ($input['tts_espeak_path'] ?? ''));
    $piperPath = trim((string) ($input['tts_piper_path'] ?? ''));
    $piperModel = trim((string) ($input['tts_piper_model'] ?? ''));
    $piperModelMale = trim((string) ($input['tts_piper_model_male'] ?? ''));
    $kind = valid_choice(trim((string) ($input['tts_kind'] ?? 'auto')), ['auto', 'piper', 'espeak'], 'auto');
    if (mb_strlen($path, 'UTF-8') > 500 || mb_strlen($piperPath, 'UTF-8') > 500 || mb_strlen($piperModel, 'UTF-8') > 500 || mb_strlen($piperModelMale, 'UTF-8') > 500) {
        throw new RuntimeException('مسیر موتور صوتی بیش از حد طولانی است.');
    }
    if ($path !== '' && !is_file($path)) {
        throw new RuntimeException('فایل موتور eSpeak در این مسیر پیدا نشد.');
    }
    if ($path !== '') {
        $probe = chatbot_tts_probe_engine($path);
        if (!$probe['ok']) {
            throw new RuntimeException('موتور eSpeak در این مسیر پاسخ معتبر نداد (' . $probe['error'] . '). مسیر را بررسی کنید.');
        }
    }
    if ($piperPath !== '' && !is_file($piperPath)) {
        throw new RuntimeException('فایل piper.exe در این مسیر پیدا نشد.');
    }
    if ($piperPath !== '') {
        $probePiper = chatbot_tts_probe_piper($piperPath);
        if (!$probePiper['ok']) {
            throw new RuntimeException('موتور Piper در این مسیر پاسخ معتبر نداد (' . $probePiper['error'] . '). مسیر را بررسی کنید.');
        }
    }
    if ($piperModel !== '' && !is_file($piperModel)) {
        throw new RuntimeException('فایل مدل فارسی (.onnx) در این مسیر پیدا نشد.');
    }
    if ($piperModel !== '' && !preg_match('/\.onnx$/i', $piperModel)) {
        throw new RuntimeException('مدل Piper باید فایل .onnx باشد (فایل .onnx.json را کنارش بگذارید، نه به‌جایش).');
    }
    if ($piperModelMale !== '' && !is_file($piperModelMale)) {
        throw new RuntimeException('فایل مدل مرد (.onnx) در این مسیر پیدا نشد.');
    }
    if ($piperModelMale !== '' && !preg_match('/\.onnx$/i', $piperModelMale)) {
        throw new RuntimeException('مدل مرد Piper باید فایل .onnx باشد (فایل .onnx.json را کنارش بگذارید، نه به‌جایش).');
    }
    save_setting('chatbot_tts_mode', $mode);
    save_setting('chatbot_tts_speed', (string) $speed);
    save_setting('chatbot_tts_voice', $voice);
    save_setting('chatbot_tts_espeak_path', $path);
    save_setting('chatbot_tts_piper_path', $piperPath);
    save_setting('chatbot_tts_piper_model', $piperModel);
    save_setting('chatbot_tts_piper_model_male', $piperModelMale);
    save_setting('chatbot_tts_kind', $kind);
    save_audit((int) $admin['id'], 'chatbot_tts_saved', null, ['mode' => $mode, 'speed' => $speed, 'voice' => $voice, 'kind' => $kind]);
}

/** پیش‌فرض‌های نام، متن‌ها و رفتار آریا. */
function chatbot_widget_defaults(): array
{
    return [
        'name' => 'آریا',
        'greet_enabled' => true,
        'greet_interval' => 60,
        'greet_text' => "سلام\nمن آریا هستم، کمکی از دستم بر میاد؟",
        'greet_duration' => 5,
        'greet_sound' => false,
        'welcome_text' => 'سلام! من آریا هستم؛ سؤال خود را درباره کار با سامانه بنویسید.',
        'input_placeholder' => 'سؤال خود را بنویسید...',
        'send_label' => 'ارسال',
        'pending_text' => 'در حال جست‌وجو...',
        'feedback_yes' => 'مفید بود',
        'feedback_no' => 'کمکی نکرد',
        'feedback_thanks' => 'ممنون از بازخوردتان.',
        'unmatched_text' => 'متأسفانه پاسخ مشخصی برای این سؤال پیدا نکردم.',
        'autospeak' => false,
        'rate_limit' => 30,
        'position' => 'left',
    ];
}

/** تنظیمات نام/متن/رفتار آریا از جدول settings (با اعتبارسنجی و پیش‌فرض امن). */
function chatbot_widget_config(): array
{
    $defaults = chatbot_widget_defaults();
    $text = static function (string $key, int $min, int $max) use ($defaults): string {
        $value = trim((string) (setting('chatbot_' . $key, '') ?? ''));
        if ($value === '') {
            $value = $defaults[$key];
        }
        $len = mb_strlen($value, 'UTF-8');
        if ($len < $min || $len > $max) {
            return $defaults[$key];
        }
        return $value;
    };
    $bool = static function (string $key) use ($defaults): bool {
        $raw = setting('chatbot_' . $key, null);
        if ($raw === null || $raw === '') {
            return $defaults[$key];
        }
        return $raw === '1';
    };
    $int = static function (string $key, int $min, int $max) use ($defaults): int {
        $raw = setting('chatbot_' . $key, null);
        if ($raw === null || $raw === '' || !is_numeric($raw)) {
            return $defaults[$key];
        }
        return max($min, min($max, (int) $raw));
    };
    return [
        'name' => $text('name', 2, 40),
        'greet_enabled' => $bool('greet_enabled'),
        'greet_interval' => $int('greet_interval', 15, 600),
        'greet_text' => $text('greet_text', 3, 200),
        'greet_duration' => $int('greet_duration', 2, 15),
        'greet_sound' => $bool('greet_sound'),
        'welcome_text' => $text('welcome_text', 3, 500),
        'input_placeholder' => $text('input_placeholder', 3, 120),
        'send_label' => $text('send_label', 2, 20),
        'pending_text' => $text('pending_text', 2, 120),
        'feedback_yes' => $text('feedback_yes', 2, 40),
        'feedback_no' => $text('feedback_no', 2, 40),
        'feedback_thanks' => $text('feedback_thanks', 2, 120),
        'unmatched_text' => $text('unmatched_text', 10, 2000),
        'autospeak' => $bool('autospeak'),
        'rate_limit' => $int('rate_limit', 5, 120),
        'position' => valid_choice((string) (setting('chatbot_position', 'left') ?? 'left'), ['left', 'right'], 'left'),
    ];
}

/** ذخیرهٔ تنظیمات نام/متن/رفتار آریا (صفحهٔ مدیریت بات سخنگو). */
function chatbot_save_widget(array $input, array $admin): void
{
    $get = static function (string $key) use ($input): string {
        return trim((string) ($input[$key] ?? ''));
    };
    $texts = [
        'w_name' => ['key' => 'chatbot_name', 'label' => 'نام ربات', 'min' => 2, 'max' => 40],
        'w_greet_text' => ['key' => 'chatbot_greet_text', 'label' => 'متن حباب خوش‌آمد', 'min' => 3, 'max' => 200],
        'w_welcome_text' => ['key' => 'chatbot_welcome_text', 'label' => 'پیام آغازین گفتگو', 'min' => 3, 'max' => 500],
        'w_input_placeholder' => ['key' => 'chatbot_input_placeholder', 'label' => 'متن راهنمای کادر سؤال', 'min' => 3, 'max' => 120],
        'w_send_label' => ['key' => 'chatbot_send_label', 'label' => 'برچسب دکمهٔ ارسال', 'min' => 2, 'max' => 20],
        'w_pending_text' => ['key' => 'chatbot_pending_text', 'label' => 'متن «در حال جست‌وجو»', 'min' => 2, 'max' => 120],
        'w_feedback_yes' => ['key' => 'chatbot_feedback_yes', 'label' => 'برچسب «مفید بود»', 'min' => 2, 'max' => 40],
        'w_feedback_no' => ['key' => 'chatbot_feedback_no', 'label' => 'برچسب «کمکی نکرد»', 'min' => 2, 'max' => 40],
        'w_feedback_thanks' => ['key' => 'chatbot_feedback_thanks', 'label' => 'پیام تشکر از بازخورد', 'min' => 2, 'max' => 120],
        'w_unmatched_text' => ['key' => 'chatbot_unmatched_text', 'label' => 'پاسخ پیش‌فرضِ سؤال بی‌پاسخ', 'min' => 10, 'max' => 2000],
    ];
    foreach ($texts as $field => $rule) {
        $len = mb_strlen($get($field), 'UTF-8');
        if ($len < $rule['min'] || $len > $rule['max']) {
            throw new RuntimeException($rule['label'] . ' باید بین ' . $rule['min'] . ' تا ' . $rule['max'] . ' نویسه باشد.');
        }
        save_setting($rule['key'], $get($field));
    }
    $ints = [
        'w_greet_interval' => ['key' => 'chatbot_greet_interval', 'label' => 'فاصلهٔ پیام خوش‌آمد (ثانیه)', 'min' => 15, 'max' => 600],
        'w_greet_duration' => ['key' => 'chatbot_greet_duration', 'label' => 'مدت نمایش حباب (ثانیه)', 'min' => 2, 'max' => 15],
        'w_rate_limit' => ['key' => 'chatbot_rate_limit', 'label' => 'سقف درخواست در دقیقه', 'min' => 5, 'max' => 120],
    ];
    foreach ($ints as $field => $rule) {
        $number = (int) $get($field);
        if ($number < $rule['min'] || $number > $rule['max']) {
            throw new RuntimeException($rule['label'] . ' باید بین ' . $rule['min'] . ' تا ' . $rule['max'] . ' باشد.');
        }
        save_setting($rule['key'], (string) $number);
    }
    save_setting('chatbot_greet_enabled', !empty($input['w_greet_enabled']) ? '1' : '0');
    save_setting('chatbot_greet_sound', '0');
    save_setting('chatbot_autospeak', !empty($input['w_autospeak']) ? '1' : '0');
    save_setting('chatbot_position', valid_choice($get('w_position'), ['left', 'right'], 'left'));
    save_audit((int) $admin['id'], 'chatbot_widget_saved', null, ['name' => $get('w_name')]);
}

/** تنظیمات گویندگی آفلاین بات (موتور سرور eSpeak-NG + جایگزین مرورگر). */
function chatbot_tts_config(): array
{
    $mode = valid_choice((string) (setting('chatbot_tts_mode', 'auto') ?? 'auto'), ['auto', 'server', 'browser', 'off'], 'auto');
    $speed = max(80, min(300, (int) (setting('chatbot_tts_speed', '140') ?? '140')));
    $customPath = trim((string) (setting('chatbot_tts_espeak_path', '') ?? ''));
    $voice = valid_choice((string) (setting('chatbot_tts_voice', 'default') ?? 'default'), ['default', 'male', 'female'], 'default');
    $piperPath = trim((string) (setting('chatbot_tts_piper_path', '') ?? ''));
    $piperModel = trim((string) (setting('chatbot_tts_piper_model', '') ?? ''));
    $piperModelMale = trim((string) (setting('chatbot_tts_piper_model_male', '') ?? ''));
    $kind = valid_choice((string) (setting('chatbot_tts_kind', 'auto') ?? 'auto'), ['auto', 'piper', 'espeak'], 'auto');
    return ['mode' => $mode, 'speed' => $speed, 'espeak_path' => $customPath, 'voice' => $voice, 'piper_path' => $piperPath, 'piper_model' => $piperModel, 'piper_model_male' => $piperModelMale, 'kind' => $kind];
}

/** آیا PHP اجازهٔ اجرای موتور صوتی را دارد؟ */
function chatbot_tts_proc_available(): bool
{
    if (!function_exists('proc_open') || !function_exists('proc_close') || !function_exists('proc_get_status')) {
        return false;
    }
    $disabled = array_map('trim', explode(',', (string) ini_get('disable_functions')));
    return !in_array('proc_open', $disabled, true);
}

/** پیدا کردن فایل اجرایی eSpeak-NG (فقط بررسی وجود فایل؛ بدون اجرای دستور). */
function chatbot_tts_find_espeak(?string $configured = null): ?string
{
    $candidates = [];
    if ($configured !== null && trim($configured) !== '') {
        $candidates[] = trim($configured);
    }
    if (strtoupper(substr((string) PHP_OS, 0, 3)) === 'WIN') {
        foreach (['ProgramFiles', 'ProgramFiles(x86)', 'ProgramW6432'] as $envKey) {
            $base = getenv($envKey);
            if (is_string($base) && $base !== '') {
                $candidates[] = rtrim($base, '\\/') . DIRECTORY_SEPARATOR . 'eSpeak NG' . DIRECTORY_SEPARATOR . 'espeak-ng.exe';
            }
        }
        $candidates[] = 'C:\\Program Files\\eSpeak NG\\espeak-ng.exe';
        $candidates[] = 'C:\\Program Files (x86)\\eSpeak NG\\espeak-ng.exe';
    } else {
        $candidates[] = '/usr/bin/espeak-ng';
        $candidates[] = '/usr/local/bin/espeak-ng';
    }
    foreach ($candidates as $candidate) {
        if (is_string($candidate) && $candidate !== '' && is_file($candidate)) {
            return $candidate;
        }
    }
    return null;
}

/** پیدا کردن piper.exe (مسیر دستی، پوشهٔ کش، مسیرهای رایج ویندوز). */
function chatbot_tts_find_piper(?string $configured = null): ?string
{
    $candidates = [];
    if ($configured !== null && trim($configured) !== '') {
        $candidates[] = trim($configured);
    }
    $candidates[] = APP_ROOT . '/storage/tts/piper/piper.exe';
    $candidates[] = APP_ROOT . '/storage/tts/piper/piper';
    if (strtoupper(substr((string) PHP_OS, 0, 3)) === 'WIN') {
        $candidates[] = 'C:/piper/piper.exe';
        $candidates[] = 'C:/Program Files/piper/piper.exe';
        $candidates[] = 'C:/Program Files/Piper/piper.exe';
    } else {
        $candidates[] = '/usr/local/bin/piper';
        $candidates[] = '/usr/bin/piper';
    }
    foreach ($candidates as $candidate) {
        if (is_string($candidate) && $candidate !== '' && is_file($candidate)) {
            return $candidate;
        }
    }
    return null;
}

/** اولویت مدل: زن=مانا، مرد=امیر/gyro؛ در نبود مدل هم‌جنس، هر fa_IR. */
function chatbot_tts_piper_model_rank(string $path, string $want = 'female'): int
{
    $name = strtolower(basename($path));
    $isFa = str_contains($name, 'fa_ir') || str_contains($name, 'fa-ir') || str_contains($name, 'farsi') || str_contains($name, 'persian') || str_contains($name, 'mana') || str_contains($name, 'amir') || str_contains($name, 'gyro') || str_contains($name, 'ganji');
    if ($want === 'male') {
        if (str_contains($name, 'amir')) {
            return 0;
        }
        if (str_contains($name, 'gyro')) {
            return 1;
        }
        if (str_contains($name, 'ganji')) {
            return 2;
        }
        if (str_contains($name, 'mana')) {
            return 8;
        }
        return $isFa ? 4 : 9;
    }
    if (str_contains($name, 'mana')) {
        return 0;
    }
    if (str_contains($name, 'gyro')) {
        return 1;
    }
    if (str_contains($name, 'amir')) {
        return 8;
    }
    if (str_contains($name, 'ganji')) {
        return 3;
    }
    return $isFa ? 4 : 9;
}

/** پیدا کردن مدل فارسی .onnx (مسیر دستی یا کنار piper.exe / storage/tts/piper). */
function chatbot_tts_piper_model(?string $configured = null, ?string $exe = null, string $want = 'female'): ?string
{
    if ($configured !== null && trim($configured) !== '') {
        $manual = trim($configured);
        if (is_file($manual) && preg_match('/\.onnx$/i', $manual)) {
            return $manual;
        }
    }
    $found = [];
    $dirs = [APP_ROOT . '/storage/tts/piper'];
    if (is_string($exe) && $exe !== '') {
        $dirs[] = dirname($exe);
        $dirs[] = dirname($exe) . DIRECTORY_SEPARATOR . 'models';
    }
    foreach ($dirs as $dir) {
        if (!is_dir($dir)) {
            continue;
        }
        $names = @scandir($dir);
        if (!is_array($names)) {
            continue;
        }
        foreach ($names as $name) {
            if (!preg_match('/\.onnx$/i', (string) $name)) {
                continue;
            }
            $file = $dir . DIRECTORY_SEPARATOR . $name;
            if (is_file($file) && (int) filesize($file) > 1000000) {
                $found[] = $file;
            }
        }
    }
    if ($found === []) {
        return null;
    }
    usort($found, static function (string $a, string $b) use ($want): int {
        $rank = chatbot_tts_piper_model_rank($a, $want) <=> chatbot_tts_piper_model_rank($b, $want);
        return $rank !== 0 ? $rank : strcasecmp(basename($a), basename($b));
    });
    return $found[0];
}

/** موتور فعال: Piper اگر آماده باشد، وگرنه eSpeak (مگر اینکه مدیر یکی را اجبار کرده باشد). */
function chatbot_tts_active(?array $config = null): array
{
    $config = $config ?? chatbot_tts_config();
    $piperExe = chatbot_tts_find_piper($config['piper_path'] ?? null);
    $want = (($config['voice'] ?? 'default') === 'male') ? 'male' : 'female';
    $manual = $want === 'male' ? (string) ($config['piper_model_male'] ?? '') : (string) ($config['piper_model'] ?? '');
    $piperModel = chatbot_tts_piper_model($manual !== '' ? $manual : null, $piperExe, $want);
    $espeakExe = chatbot_tts_find_espeak($config['espeak_path'] ?? null);
    $proc = chatbot_tts_proc_available();
    $piperReady = $proc && $piperExe !== null && $piperModel !== null;
    $espeakReady = $proc && $espeakExe !== null;
    $want = (string) ($config['kind'] ?? 'auto');
    if ($want === 'piper') {
        return $piperReady
            ? ['kind' => 'piper', 'exe' => $piperExe, 'model' => $piperModel]
            : ['kind' => 'none', 'exe' => null, 'model' => null];
    }
    if ($want === 'espeak') {
        return $espeakReady
            ? ['kind' => 'espeak', 'exe' => $espeakExe, 'model' => null]
            : ['kind' => 'none', 'exe' => null, 'model' => null];
    }
    if ($piperReady) {
        return ['kind' => 'piper', 'exe' => $piperExe, 'model' => $piperModel];
    }
    if ($espeakReady) {
        return ['kind' => 'espeak', 'exe' => $espeakExe, 'model' => null];
    }
    return ['kind' => 'none', 'exe' => null, 'model' => null];
}

/** نگاشت سرعت کلمه/دقیقه به length_scale پایپر (۱۴۰ = طبیعی؛ کمتر = شمرده‌تر). */
function chatbot_tts_length_scale(int $wpm): string
{
    $scale = 140.0 / max(80, min(300, $wpm));
    $scale *= 1.25;
    $scale = max(0.5, min(2.0, $scale));
    return number_format($scale, 2, '.', '');
}

/** وضعیت موتور گویندگی برای ویجت و صفحهٔ مدیریت. */
function chatbot_tts_status(): array
{
    $config = chatbot_tts_config();
    $exe = chatbot_tts_find_espeak($config['espeak_path']);
    $active = chatbot_tts_active($config);
    $serverReady = $active['kind'] !== 'none';
    $engine = 'none';
    if ($config['mode'] === 'server') {
        $engine = 'server';
    } elseif ($config['mode'] === 'browser') {
        $engine = 'browser';
    } elseif ($config['mode'] === 'auto') {
        $engine = $serverReady ? 'server' : 'browser';
    }
    return [
        'mode' => $config['mode'],
        'speed' => $config['speed'],
        'voice' => $config['voice'],
        'kind' => $config['kind'],
        'active_kind' => $active['kind'],
        'server_ready' => $serverReady,
        'espeak' => $exe,
        'piper' => chatbot_tts_find_piper($config['piper_path']),
        'piper_model' => $active['kind'] === 'piper' ? (string) $active['model'] : $config['piper_model'],
        'engine' => $engine,
    ];
}

/** رقم‌های فارسی و عربی را به ASCII. */
function chatbot_tts_ascii_digits(string $s): string
{
    return strtr($s, [
        '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4', '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
        '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4', '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
    ]);
}

/** یک رقم ASCII را با حروف می‌گوید. */
function chatbot_tts_digits_spoken(string $digits): string
{
    static $w = ['0' => 'صفر', '1' => 'یک', '2' => 'دو', '3' => 'سه', '4' => 'چهار', '5' => 'پنج', '6' => 'شش', '7' => 'هفت', '8' => 'هشت', '9' => 'نه'];
    $digits = chatbot_tts_ascii_digits($digits);
    $out = [];
    $len = strlen($digits);
    for ($i = 0; $i < $len; $i++) {
        $out[] = $w[$digits[$i]] ?? '';
    }
    return trim(implode(' ', $out));
}

/** ۰ تا ۹۹۹ را به حروف. */
function chatbot_tts_three_digit(int $n): string
{
    static $ones = ['', 'یک', 'دو', 'سه', 'چهار', 'پنج', 'شش', 'هفت', 'هشت', 'نه'];
    static $teens = ['ده', 'یازده', 'دوازده', 'سیزده', 'چهارده', 'پانزده', 'شانزده', 'هفده', 'هجده', 'نوزده'];
    static $tens = ['', '', 'بیست', 'سی', 'چهل', 'پنجاه', 'شصت', 'هفتاد', 'هشتاد', 'نود'];
    static $hundreds = ['', 'صد', 'دویست', 'سیصد', 'چهارصد', 'پانصد', 'ششصد', 'هفتصد', 'هشتصد', 'نهصد'];
    $parts = [];
    $h = intdiv($n, 100);
    $r = $n % 100;
    if ($h > 0) {
        $parts[] = $hundreds[$h];
    }
    if ($r === 0) {
        return implode(' و ', $parts);
    }
    if ($r < 10) {
        $parts[] = $ones[$r];
    } elseif ($r < 20) {
        $parts[] = $teens[$r - 10];
    } else {
        $t = intdiv($r, 10);
        $o = $r % 10;
        $parts[] = $o === 0 ? $tens[$t] : ($tens[$t] . ' و ' . $ones[$o]);
    }
    return implode(' و ', $parts);
}

/** عدد صحیح را به حروف فارسی (قاعدهٔ نرمال‌ساز TTSهای فارسی، نه اعراب داخل واژه). */
function chatbot_tts_int_to_words(int $n): string
{
    if ($n < 0) {
        return 'منفی ' . chatbot_tts_int_to_words(-$n);
    }
    if ($n === 0) {
        return 'صفر';
    }
    $parts = [];
    foreach ([1000000000 => 'میلیارد', 1000000 => 'میلیون', 1000 => 'هزار'] as $div => $name) {
        if ($n >= $div) {
            $chunk = intdiv($n, $div);
            $n %= $div;
            if ($chunk === 1 && $name === 'هزار') {
                $parts[] = 'هزار';
            } else {
                $parts[] = trim(chatbot_tts_three_digit($chunk) . ' ' . $name);
            }
        }
    }
    if ($n > 0) {
        $parts[] = chatbot_tts_three_digit($n);
    }
    return implode(' و ', $parts);
}

/** رشتهٔ رقمی (با صفر پیشوند یا اعشار) را به گفتار. */
function chatbot_tts_number_token(string $token): string
{
    $token = strtr(chatbot_tts_ascii_digits($token), ['٫' => '.', '٬' => '']);
    if (str_contains($token, '.')) {
        $bits = explode('.', $token, 2);
        $left = chatbot_tts_number_int_string($bits[0] === '' ? '0' : $bits[0]);
        $frac = $bits[1] ?? '0';
        $right = ($frac !== '' && $frac[0] === '0')
            ? chatbot_tts_digits_spoken($frac)
            : chatbot_tts_number_int_string($frac === '' ? '0' : $frac);
        return $left . ' ممیز ' . $right;
    }
    return chatbot_tts_number_int_string($token);
}

function chatbot_tts_number_int_string(string $s): string
{
    $s = chatbot_tts_ascii_digits($s);
    if ($s === '' || !ctype_digit($s)) {
        return 'صفر';
    }
    if ((strlen($s) > 1 && $s[0] === '0') || strlen($s) > 9) {
        return chatbot_tts_digits_spoken($s);
    }
    return chatbot_tts_int_to_words((int) $s);
}

/** همهٔ عددهای متن را با حروف می‌گوید؛ درصد را «درصد». */
function chatbot_tts_numbers_to_words(string $text): string
{
    $text = (string) preg_replace('/([0-9۰-۹٠-٩])[٬,](?=[0-9۰-۹٠-٩])/u', '$1', $text);
    return (string) preg_replace_callback(
        '/[0-9۰-۹٠-٩]+(?:[.,٫][0-9۰-۹٠-٩]+)?[٪%]?/u',
        static function (array $m): string {
            $raw = $m[0];
            $percent = str_ends_with($raw, '%') || str_ends_with($raw, '٪');
            if ($percent) {
                $raw = mb_substr($raw, 0, -1, 'UTF-8');
            }
            $spoken = chatbot_tts_number_token($raw);
            return $percent ? ($spoken . ' درصد') : $spoken;
        },
        $text
    );
}

/** مخفف لاتین رایج در پاسخ‌های IT، به گفتار فارسی. */
function chatbot_tts_spell_acronyms(string $text): string
{
    $pairs = [
        'DVD' => 'دی وی دی',
        'USB' => 'یو اس بی',
        'PDF' => 'پی دی اف',
        'CPU' => 'سی پی یو',
        'RAM' => 'رم',
        'SMS' => 'اس ام اس',
        'URL' => 'آدرس',
        'ID' => 'شناسه',
        'OK' => 'اوکی',
        'IT' => 'آی تی',
        'CD' => 'سی دی',
    ];
    foreach ($pairs as $from => $to) {
        $text = (string) preg_replace('/\b' . $from . '\b/i', $to, $text);
    }
    return $text;
}

/** آماده‌سازی متن پاسخ برای خوانده شدن: پیوند، مخفف، عدد به حروف، ی/ک عربی، شکلک. */
function chatbot_tts_clean(string $text): string
{
    $text = (string) preg_replace('~https?://\S+~u', ' پیوند ', $text);
    $text = (string) preg_replace('~\S*\.php\S*~u', ' پیوند ', $text);
    $text = (string) preg_replace_callback(
        '~\b([A-Z]{2,}\d?)-(\d+)-(\d+)\b~',
        static fn(array $m): string => ' شماره پیگیری ' . $m[2] . ' ' . $m[3],
        $text
    );
    $text = (string) preg_replace_callback(
        '~\b([A-Z]{2,})-(\d+)\b~',
        static fn(array $m): string => ' شماره ' . $m[2],
        $text
    );
    $text = chatbot_tts_spell_acronyms($text);
    $text = strtr($text, ['ي' => 'ی', 'ى' => 'ی', 'ك' => 'ک', 'ة' => 'ه', 'ۀ' => 'ه', 'أ' => 'ا', 'إ' => 'ا', 'ؤ' => 'و']);
    $text = chatbot_tts_numbers_to_words($text);
    $text = (string) preg_replace('~[\x{1F000}-\x{1FAFF}\x{2600}-\x{27BF}\x{2B00}-\x{2BFF}\x{FE00}-\x{FE0F}]+~u', ' ', $text);
    $text = (string) preg_replace('~[^\p{L}\p{N}\s.,;:!?؟،؛٪٫٬\-()«»"\'/+=%*_]~u', ' ', $text);
    $text = (string) preg_replace('~\s+~u', ' ', trim($text));
    return mb_substr($text, 0, 800, 'UTF-8');
}

/**
 * بازنویسی متن برای خواندن محاوره‌ای تهرانی (فقط صدا؛ متن روی صفحه عوض نمی‌شود).
 * انقباض‌های رایج گفتاری؛ اصطلاحات اداری و «شما»ی مؤدبانه حفظ می‌شوند.
 */
function chatbot_tts_colloquial(string $text): string
{
    $text = str_replace("\xE2\x80\x8C", '', $text);
    $pairs = [
        'نمی توانید' => 'نمی تونید', 'نمیتوانید' => 'نمی تونید', 'نمی‌توانید' => 'نمی تونید',
        'نمی توانم' => 'نمی تونم', 'نمیتوانم' => 'نمی تونم', 'نمی‌توانم' => 'نمی تونم',
        'نمی تواند' => 'نمی تونه', 'نمیتواند' => 'نمی تونه', 'نمی‌تواند' => 'نمی تونه',
        'نمی توانیم' => 'نمی تونیم', 'نمیتوانیم' => 'نمی تونیم', 'نمی‌توانیم' => 'نمی تونیم',
        'می توانید' => 'می تونید', 'میتوانید' => 'می تونید', 'می‌توانید' => 'می تونید',
        'می توانم' => 'می تونم', 'میتوانم' => 'می تونم', 'می‌توانم' => 'می تونم',
        'می تواند' => 'می تونه', 'میتواند' => 'می تونه', 'می‌تواند' => 'می تونه',
        'می توانیم' => 'می تونیم', 'میتوانیم' => 'می تونیم', 'می‌توانیم' => 'می تونیم',
        'می خواهید' => 'می خواید', 'میخواهید' => 'می خواید', 'می‌خواهید' => 'می خواید',
        'می خواهم' => 'می خوام', 'میخواهم' => 'می خوام', 'می‌خواهم' => 'می خوام',
        'می خواهد' => 'می خواد', 'میخواهد' => 'می خواد', 'می‌خواهد' => 'می خواد',
        'می خواهیم' => 'می خوایم', 'میخواهیم' => 'می خوایم', 'می‌خواهیم' => 'می خوایم',
        'می خواهی' => 'می خوای', 'میخواهی' => 'می خوای', 'می‌خواهی' => 'می خوای',
        'نمی شود' => 'نمی شه', 'نمیشود' => 'نمی شه', 'نمی‌شود' => 'نمی شه',
        'می شود' => 'می شه', 'میشود' => 'می شه', 'می‌شود' => 'می شه',
        'می روید' => 'می رید', 'میروید' => 'می رید', 'می‌روید' => 'می رید',
        'می روم' => 'می رم', 'میروم' => 'می رم', 'می‌روم' => 'می رم',
        'می رود' => 'می ره', 'میرود' => 'می ره', 'می‌رود' => 'می ره',
        'می رویم' => 'می ریم', 'میرویم' => 'می ریم', 'می‌رویم' => 'می ریم',
        'می باشد' => 'هست', 'میباشد' => 'هست', 'می‌باشد' => 'هست',
        'بدهید' => 'بدید',
        'چگونه' => 'چطور',
        'چیست' => 'چی هست',
        'کیست' => 'کی هست',
        'آن ها' => 'اونا', 'آن‌ها' => 'اونا', 'آنها' => 'اونا',
        'این ها' => 'اینا', 'این‌ها' => 'اینا', 'اینها' => 'اینا',
    ];
    uksort($pairs, static fn(string $a, string $b): int => mb_strlen($b, 'UTF-8') <=> mb_strlen($a, 'UTF-8'));
    foreach ($pairs as $from => $to) {
        $text = str_replace($from, $to, $text);
    }
    $text = (string) preg_replace('/(^|[\s])دیگر(?=[\s.؟!،؛]|$)/u', '$1دیگه', $text);
    $text = (string) preg_replace('/(^|[\s])آن(?=[\s.؟!،؛]|$)/u', '$1اون', $text);
    $text = (string) preg_replace('/(^|[\s])را(?=[\s.؟!،؛]|$)/u', '$1رو', $text);
    $text = (string) preg_replace('/\s+است(?=[\s.؟!،؛]|$)/u', ' هست', $text);
    $text = (string) preg_replace('~\s+~u', ' ', trim($text));
    return $text;
}

/** نقطه‌گذاری برای مکث طبیعی Piper (جمله به جمله، نه یک نفس طولانی). */
function chatbot_tts_phrase(string $text): string
{
    $text = strtr($text, ['؛' => '.', ';' => '.', '…' => '.']);
    $text = str_replace('...', '.', $text);
    $text = (string) preg_replace('/([.؟!،,])(?!\s|$)/u', '$1 ', $text);
    $text = (string) preg_replace('~\s+~u', ' ', trim($text));
    if ($text !== '' && !preg_match('/[.؟!]$/u', $text)) {
        $text .= '.';
    }
    return $text;
}

/**
 * فقط دو کار: سِ در ابتدای واژه (سَلام نه سسسس)، و کسره بین دو واژهٔ جدا (سفارشِ غذا).
 * داخل واژه اعراب نمی‌گذاریم تا راهنمای / سفارش‌های تکه نشوند.
 */
function chatbot_tts_vocalize(string $text): string
{
    $text = str_replace("\xE2\x80\x8C", '', $text);
    $text = (string) preg_replace('/(^|[^\p{L}\p{M}])س(?=[بتثجچحخدذرزژسشصضطظعغفقکگلمنهیپچژگ])/u', '$1سَ', $text);
    return chatbot_tts_ezafe($text);
}

/** کسره فقط بین دو کلمهٔ جدا با فاصله. پسوند ها/های و کلمه‌های مختوم به ی/ا/و/ه را لمس نمی‌کند. */
function chatbot_tts_ezafe(string $text): string
{
    $skipFirst = ['از', 'در', 'به', 'که', 'و', 'با', 'تا', 'یا', 'اگر', 'نه', 'را', 'رو', 'هم', 'برای', 'من', 'تو', 'او', 'ما', 'شما', 'این', 'آن', 'اون', 'اینا', 'اونا'];
    $skipSecond = ['است', 'هست', 'هستم', 'هستید', 'هستند', 'بود', 'باشد', 'شد', 'شود', 'کنید', 'کنم', 'کنیم', 'کن', 'بپرسید', 'باید', 'می', 'ها', 'های', 'تر', 'ترین', 'در', 'به', 'از', 'با', 'تا', 'برای', 'که', 'و', 'یا', 'اگر', 'روی'];
    $skipFirst = array_fill_keys($skipFirst, true);
    $skipSecond = array_fill_keys($skipSecond, true);
    $parts = preg_split('/(\s+|[.؟!،,;:]+)/u', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
    if (!is_array($parts)) {
        return $text;
    }
    $isWord = static fn(string $w): bool => (bool) preg_match('/^[\p{L}\p{M}]+$/u', $w);
    $count = count($parts);
    for ($i = 0; $i < $count; $i++) {
        if (!$isWord($parts[$i])) {
            continue;
        }
        $j = $i + 1;
        while ($j < $count && trim($parts[$j]) === '') {
            $j++;
        }
        if ($j >= $count || !$isWord($parts[$j])) {
            continue;
        }
        $a = $parts[$i];
        $b = $parts[$j];
        $aBare = preg_replace('/[\p{M}]+/u', '', $a) ?? $a;
        $bBare = preg_replace('/[\p{M}]+/u', '', $b) ?? $b;
        if (isset($skipFirst[$aBare]) || isset($skipSecond[$bBare])) {
            continue;
        }
        if (preg_match('/[اآویىیهةۀ]$/u', $aBare)) {
            continue;
        }
        $parts[$i] = $a . 'ِ';
    }
    return implode('', $parts);
}

/** نگاشت نوع صدا به شناسهٔ صدای eSpeak-NG (فقط مقادیر مجاز؛ هرگز ورودی خام). */
function chatbot_tts_voice_id(string $voice): string
{
    $map = ['default' => 'fa', 'male' => 'fa+m2', 'female' => 'fa+f2'];
    return $map[$voice] ?? 'fa';
}

/** انتظار حداکثر چند ثانیه‌ای برای پایان فرآیند (بدون گیر کردن). */
function chatbot_tts_wait_exit($process, float $seconds): bool
{
    $deadline = microtime(true) + $seconds;
    do {
        $status = @proc_get_status($process);
        if (!is_array($status) || !$status['running']) {
            return true;
        }
        usleep(100000);
    } while (microtime(true) < $deadline);
    $status = @proc_get_status($process);
    return !is_array($status) || !$status['running'];
}

/** خاتمهٔ اجباری درخت فرآیند در ویندوز (آخرین راه‌حل در برابر گیر کردن). */
function chatbot_tts_taskkill(int $pid): void
{
    if ($pid <= 0) {
        return;
    }
    $taskkill = @proc_open(
        ['taskkill', '/F', '/T', '/PID', (string) $pid],
        [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
        $tkPipes
    );
    if (!is_resource($taskkill)) {
        return;
    }
    fclose($tkPipes[0]);
    @stream_set_blocking($tkPipes[1], false);
    @stream_set_blocking($tkPipes[2], false);
    $deadline = microtime(true) + 5.0;
    do {
        @fread($tkPipes[1], 8192);
        @fread($tkPipes[2], 8192);
        $status = @proc_get_status($taskkill);
        if (!is_array($status) || !$status['running']) {
            break;
        }
        usleep(50000);
    } while (microtime(true) < $deadline);
    @fclose($tkPipes[1]);
    @fclose($tkPipes[2]);
    @proc_close($taskkill);
}

/**
 * اجرای امن یک دستور با ورودی stdin و مهلت مشخص.
 * در همهٔ مسیرها (پایان عادی، تایم‌اوت، خروجی بزرگ) برمی‌گردد و گیر نمی‌کند.
 */
function chatbot_tts_exec(array $command, string $stdin, float $timeoutSec): array
{
    $descriptors = [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
    $process = @proc_open($command, $descriptors, $pipes);
    if (!is_resource($process)) {
        return ['ok' => false, 'stdout' => '', 'stderr' => '', 'exit' => null, 'error' => 'start'];
    }
    $isWin = strtoupper(substr((string) PHP_OS, 0, 3)) === 'WIN';
    $pid = 0;
    $boot = @proc_get_status($process);
    if (is_array($boot) && isset($boot['pid'])) {
        $pid = (int) $boot['pid'];
    }
    @stream_set_blocking($pipes[0], false);
    $rest = $stdin;
    $writeDeadline = microtime(true) + 5.0;
    while ($rest !== '') {
        $written = @fwrite($pipes[0], $rest);
        if (is_int($written) && $written > 0) {
            $rest = substr($rest, $written);
            continue;
        }
        if (microtime(true) > $writeDeadline) {
            break;
        }
        usleep(20000);
    }
    @fclose($pipes[0]);
    @stream_set_blocking($pipes[1], false);
    @stream_set_blocking($pipes[2], false);
    $stdout = '';
    $stderr = '';
    $deadline = microtime(true) + $timeoutSec;
    $exit = null;
    $timedOut = false;
    while (true) {
        $out = @fread($pipes[1], 8192);
        if (is_string($out) && $out !== '') {
            $stdout .= $out;
            if (strlen($stdout) > 12 * 1024 * 1024) {
                $timedOut = true;
                break;
            }
        }
        $err = @fread($pipes[2], 8192);
        if (is_string($err) && $err !== '') {
            $stderr .= $err;
            if (strlen($stderr) > 65536) {
                $stderr = substr($stderr, -65536);
            }
        }
        $status = @proc_get_status($process);
        if (!is_array($status) || !$status['running']) {
            $exit = is_array($status) ? (int) $status['exitcode'] : null;
            break;
        }
        if (microtime(true) > $deadline) {
            $timedOut = true;
            break;
        }
        usleep(50000);
    }
    if ($timedOut) {
        @proc_terminate($process);
        chatbot_tts_wait_exit($process, 3.0);
        $still = @proc_get_status($process);
        if ($isWin && is_array($still) && $still['running'] && $pid > 0) {
            chatbot_tts_taskkill($pid);
            chatbot_tts_wait_exit($process, 2.0);
        }
    }
    @fclose($pipes[1]);
    @fclose($pipes[2]);
    $code = @proc_close($process);
    if ($exit === null && is_int($code)) {
        $exit = $code;
    }
    if ($timedOut) {
        return ['ok' => false, 'stdout' => $stdout, 'stderr' => $stderr, 'exit' => $exit, 'error' => 'timeout'];
    }
    if ($exit !== 0) {
        return ['ok' => false, 'stdout' => $stdout, 'stderr' => $stderr, 'exit' => $exit, 'error' => 'exit-' . (string) $exit];
    }
    return ['ok' => true, 'stdout' => $stdout, 'stderr' => $stderr, 'exit' => $exit, 'error' => ''];
}

/** اعتبارسنجی سراند WAV (جلوگیری از پخش یا کش بایت خراب). */
function chatbot_tts_is_valid_wav(string $wav): bool
{
    return strlen($wav) > 100 && substr($wav, 0, 4) === 'RIFF' && substr($wav, 8, 4) === 'WAVE';
}

/** اجرای موتور با خروجی فایل WAV (بدون باینری در pipe) و مهلت ۲۰ ثانیه‌ای. */
function chatbot_tts_run_espeak(string $exe, string $text, int $wpm, string $voiceId): array
{
    $tmp = rtrim((string) sys_get_temp_dir(), '\\/') . DIRECTORY_SEPARATOR . 'aria-' . bin2hex(random_bytes(8)) . '.wav';
    $result = chatbot_tts_exec([$exe, '-v', $voiceId, '-s', (string) $wpm, '-w', $tmp], $text, 20.0);
    $wav = is_file($tmp) ? (string) @file_get_contents($tmp) : '';
    if (is_file($tmp)) {
        @unlink($tmp);
    }
    if (!$result['ok']) {
        if ($result['error'] === 'timeout') {
            app_error_log('chatbot TTS timeout: engine did not finish in 20s');
            return ['ok' => false, 'error' => 'موتور صوتی در زمان مجاز پاسخ نداد.'];
        }
        app_error_log('chatbot TTS failed (' . $result['error'] . '): ' . mb_substr((string) $result['stderr'], 0, 500, 'UTF-8'));
        return ['ok' => false, 'error' => 'خطای موتور صوتی؛ از صحت نصب صدای فارسی (fa) مطمئن شوید.'];
    }
    if (!chatbot_tts_is_valid_wav($wav)) {
        app_error_log('chatbot TTS invalid wav (bytes ' . strlen($wav) . ', head ' . bin2hex(substr($wav, 0, 32)) . '); stderr: ' . mb_substr((string) $result['stderr'], 0, 500, 'UTF-8'));
        return ['ok' => false, 'error' => 'موتور صوتی خروجی معتبر تولید نکرد.'];
    }
    return ['ok' => true, 'wav' => $wav];
}

/** سنتز با Piper (خروجی فایل WAV؛ دو گویش CLI: باینری C++ و پایتون). */
function chatbot_tts_run_piper(string $exe, string $model, string $text, string $lengthScale): array
{
    $tmp = rtrim((string) sys_get_temp_dir(), '\\/') . DIRECTORY_SEPARATOR . 'aria-' . bin2hex(random_bytes(8));
    $wavPath = $tmp . '.wav';
    $txtPath = $tmp . '.txt';
    if (@file_put_contents($txtPath, $text . "\n") === false) {
        return ['ok' => false, 'error' => 'ساخت فایل موقت متن ممکن نشد.'];
    }
    $qCpp = ['--noise_scale', '0.500', '--noise_w', '0.35', '--sentence_silence', '0.35'];
    $qPy = ['--noise-scale', '0.500', '--noise-w', '0.35', '--sentence-silence', '0.35'];
    $attempts = [
        ['cmd' => array_merge([$exe, '--model', $model, '--output_file', $wavPath, '--length_scale', $lengthScale], $qCpp), 'stdin' => $text . "\n"],
        ['cmd' => array_merge([$exe, '-m', $model, '-f', $wavPath, '--length-scale', $lengthScale, '-i', $txtPath], $qPy), 'stdin' => ''],
        ['cmd' => [$exe, '--model', $model, '--output_file', $wavPath, '--length_scale', $lengthScale], 'stdin' => $text . "\n"],
    ];
    $result = ['ok' => false, 'stdout' => '', 'stderr' => '', 'exit' => null, 'error' => 'start'];
    $wav = '';
    foreach ($attempts as $attempt) {
        $result = chatbot_tts_exec($attempt['cmd'], $attempt['stdin'], 40.0);
        $wav = is_file($wavPath) ? (string) @file_get_contents($wavPath) : '';
        if (chatbot_tts_is_valid_wav($wav)) {
            break;
        }
        if (is_file($wavPath)) {
            @unlink($wavPath);
        }
        if ($result['error'] === 'timeout') {
            break;
        }
        $wav = '';
    }
    if (is_file($wavPath)) {
        @unlink($wavPath);
    }
    if (is_file($txtPath)) {
        @unlink($txtPath);
    }
    if (!chatbot_tts_is_valid_wav($wav)) {
        if ($result['error'] === 'timeout') {
            app_error_log('chatbot Piper timeout: engine did not finish in 40s');
            return ['ok' => false, 'error' => 'موتور عصبی در زمان مجاز پاسخ نداد.'];
        }
        app_error_log('chatbot Piper failed (' . $result['error'] . '): ' . mb_substr((string) $result['stderr'], 0, 500, 'UTF-8'));
        return ['ok' => false, 'error' => 'موتور عصبی خروجی معتبر تولید نکرد. مسیر piper.exe و فایل مدل .onnx را بررسی کنید.'];
    }
    return ['ok' => true, 'wav' => $wav];
}

/** راستی‌آزمایی مسیر دستی Piper. */
function chatbot_tts_probe_piper(string $exe): array
{
    $help = chatbot_tts_exec([$exe, '--help'], '', 10.0);
    $banner = (string) $help['stdout'] . "\n" . (string) $help['stderr'];
    if (stripos($banner, 'model') !== false || stripos($banner, 'piper') !== false) {
        return ['ok' => true, 'error' => ''];
    }
    $ver = chatbot_tts_exec([$exe, '--version'], '', 10.0);
    $banner2 = (string) $ver['stdout'] . "\n" . (string) $ver['stderr'];
    if (stripos($banner2, 'piper') !== false) {
        return ['ok' => true, 'error' => ''];
    }
    return ['ok' => false, 'error' => 'خروجی موتور Piper شناخته نشد'];
}

/** راستی‌آزمایی مسیر دستی موتور (اجرای --version با مهلت ۱۰ ثانیه‌ای). */
function chatbot_tts_probe_engine(string $exe): array
{
    $result = chatbot_tts_exec([$exe, '--version'], '', 10.0);
    if (!$result['ok']) {
        return ['ok' => false, 'error' => $result['error'] === 'timeout' ? 'در زمان مجاز پاسخ نداد' : 'اجرا نشد'];
    }
    $banner = (string) $result['stdout'] . "\n" . (string) $result['stderr'];
    if (stripos($banner, 'espeak') === false) {
        return ['ok' => false, 'error' => 'خروجی موتور شناخته نشد'];
    }
    return ['ok' => true, 'error' => ''];
}

/** تولید (با کش) فایل صوتی یک پاسخ. */
/** بررسی وجود صدای فارسی در موتور (yes/no/unknown؛ کش ۲۴ ساعته برای yes و ۱ ساعته برای بقیه). */
function chatbot_tts_voice_status(string $exe, string $voiceId): array
{
    $base = strtolower((string) explode('+', $voiceId)[0]);
    if ($base === '') {
        $base = 'fa';
    }
    $dir = APP_ROOT . '/storage/tts';
    $cacheFile = $dir . '/.voices.json';
    $key = sha1($exe . '|' . $base);
    $now = time();
    if (is_file($cacheFile)) {
        $decoded = json_decode((string) @file_get_contents($cacheFile), true);
        if (is_array($decoded) && isset($decoded[$key]) && is_array($decoded[$key])) {
            $entry = $decoded[$key];
            $ttl = ((string) ($entry['status'] ?? '') === 'yes') ? 86400 : 3600;
            if (($now - (int) ($entry['at'] ?? 0)) < $ttl) {
                return ['status' => (string) ($entry['status'] ?? 'unknown'), 'detail' => (string) ($entry['detail'] ?? '')];
            }
        }
    }
    $status = 'unknown';
    $detail = '';
    $probe = chatbot_tts_exec([$exe, '--voices'], '', 20.0);
    if ($probe['ok'] && trim((string) $probe['stdout']) !== '') {
        $status = 'no';
        foreach (preg_split('/\r?\n/', (string) $probe['stdout']) ?: [] as $line) {
            if (preg_match('/(^|\s)' . preg_quote($base, '/') . '(\s|$)/i', (string) $line)) {
                $status = 'yes';
                $detail = trim((string) $line);
                break;
            }
        }
    }
    if (is_dir($dir) || @mkdir($dir, 0775, true)) {
        $all = [];
        if (is_file($cacheFile)) {
            $decoded = json_decode((string) @file_get_contents($cacheFile), true);
            if (is_array($decoded)) {
                $all = $decoded;
            }
        }
        $all[$key] = ['status' => $status, 'detail' => mb_substr($detail, 0, 200, 'UTF-8'), 'at' => $now];
        @file_put_contents($cacheFile, json_encode($all, JSON_UNESCAPED_UNICODE), LOCK_EX);
    }
    return ['status' => $status, 'detail' => $detail];
}

function chatbot_tts_say(string $text): array
{
    $config = chatbot_tts_config();
    if ($config['mode'] === 'off') {
        return ['ok' => false, 'error' => 'گویندگی بات خاموش است.'];
    }
    $clean = chatbot_tts_phrase(chatbot_tts_vocalize(chatbot_tts_colloquial(chatbot_tts_clean($text))));
    if ($clean === '') {
        return ['ok' => false, 'error' => 'متنی برای خواندن وجود ندارد.'];
    }
    $active = chatbot_tts_active($config);
    if ($active['kind'] === 'none') {
        return ['ok' => false, 'error' => 'موتور صوتی روی سرور نصب/فعال نیست. Piper (پیشنهادی) یا eSpeak را نصب کنید.'];
    }
    if ($active['kind'] === 'espeak') {
        $voiceStatus = chatbot_tts_voice_status((string) $active['exe'], chatbot_tts_voice_id($config['voice']));
        if ($voiceStatus['status'] === 'no') {
            return ['ok' => false, 'error' => 'صدای فارسی در موتور صوتی یافت نشد؛ موتور را کامل نصب کنید یا حالت «فقط صدای مرورگر» را امتحان کنید.'];
        }
    }
    $dir = APP_ROOT . '/storage/tts';
    if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
        return ['ok' => false, 'error' => 'ساخت پوشهٔ کش صدا ممکن نشد.'];
    }
    $voiceId = $active['kind'] === 'piper'
        ? ('piper:' . basename((string) $active['model']) . '|q6')
        : chatbot_tts_voice_id($config['voice']);
    $id = sha1($clean . '|' . $config['speed'] . '|' . $voiceId);
    $file = $dir . '/' . $id . '.wav';
    if (is_file($file) && (int) filesize($file) > 100) {
        $head = (string) @file_get_contents($file, false, null, 0, 12);
        if (substr($head, 0, 4) === 'RIFF' && substr($head, 8, 4) === 'WAVE') {
            return ['ok' => true, 'file' => $file, 'id' => $id];
        }
        @unlink($file);
    }
    if ($active['kind'] === 'piper') {
        $result = chatbot_tts_run_piper((string) $active['exe'], (string) $active['model'], $clean, chatbot_tts_length_scale($config['speed']));
    } else {
        $result = chatbot_tts_run_espeak((string) $active['exe'], $clean, $config['speed'], chatbot_tts_voice_id($config['voice']));
    }
    if (!$result['ok']) {
        return $result;
    }
    if (@file_put_contents($file, $result['wav'], LOCK_EX) === false) {
        return ['ok' => false, 'error' => 'ذخیرهٔ فایل صوتی ممکن نشد.'];
    }
    return ['ok' => true, 'file' => $file, 'id' => $id];
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

    render_header('مدیریت آریا (بات سخنگو)', $admin);
    echo '<section class="page-heading"><div><span class="eyebrow">پشتیبانی هوشمند</span><h1>مدیریت آریا (بات سخنگو)</h1><p>پرسش‌های متداول و کلیدواژه‌های آن‌ها را مدیریت کنید. سؤال‌های بی‌پاسخ کاربران را از جدول پایین می‌توانید به‌سرعت پاسخ دهید.</p></div></section>';

    $wcfg = chatbot_widget_config();
    $wChecked = static fn(bool $on): string => $on ? ' checked' : '';
    echo '<div class="card form-card"><h2>تنظیمات آریا</h2>'
        . '<p class="muted">نام، پیام خوش‌آمد، متن‌ها و رفتار ربات. همهٔ تغییرات بلافاصله برای کاربران اعمال می‌شود.</p>'
        . '<form method="post" action="index.php?page=chatbot-admin">' . csrf_field()
        . '<input type="hidden" name="widget_settings" value="1">'
        . '<h3>نام و هویت</h3>'
        . '<label class="field">نام ربات (در سربرگ گفتگو)<input name="w_name" required maxlength="40" value="' . e($wcfg['name']) . '"></label>'
        . '<h3>پیام خوش‌آمد (حباب کنار ربات)</h3>'
        . '<label class="check"><input type="checkbox" name="w_greet_enabled" value="1"' . $wChecked($wcfg['greet_enabled']) . '> نمایش دوره‌ای حباب خوش‌آمد</label>'
        . '<label class="field">فاصلهٔ تکرار (ثانیه)<input type="number" name="w_greet_interval" min="15" max="600" value="' . (int) $wcfg['greet_interval'] . '"></label>'
        . '<label class="field">متن حباب (هر خط در یک سطر)<textarea name="w_greet_text" rows="2" maxlength="200">' . e($wcfg['greet_text']) . '</textarea></label>'
        . '<label class="field">مدت نمایش حباب (ثانیه)<input type="number" name="w_greet_duration" min="2" max="15" value="' . (int) $wcfg['greet_duration'] . '"></label>'
        . '<h3>متن‌های گفتگو</h3>'
        . '<label class="field">پیام آغازین گفتگو<textarea name="w_welcome_text" rows="2" maxlength="500">' . e($wcfg['welcome_text']) . '</textarea></label>'
        . '<label class="field">متن راهنمای کادر سؤال<input name="w_input_placeholder" required maxlength="120" value="' . e($wcfg['input_placeholder']) . '"></label>'
        . '<label class="field">برچسب دکمهٔ ارسال<input name="w_send_label" required maxlength="20" value="' . e($wcfg['send_label']) . '"></label>'
        . '<label class="field">متن «در حال جست‌وجو»<input name="w_pending_text" required maxlength="120" value="' . e($wcfg['pending_text']) . '"></label>'
        . '<label class="field">برچسب «مفید بود»<input name="w_feedback_yes" required maxlength="40" value="' . e($wcfg['feedback_yes']) . '"></label>'
        . '<label class="field">برچسب «کمکی نکرد»<input name="w_feedback_no" required maxlength="40" value="' . e($wcfg['feedback_no']) . '"></label>'
        . '<label class="field">پیام تشکر از بازخورد<input name="w_feedback_thanks" required maxlength="120" value="' . e($wcfg['feedback_thanks']) . '"></label>'
        . '<label class="field">پاسخ پیش‌فرضِ سؤال بی‌پاسخ<textarea name="w_unmatched_text" rows="3" maxlength="2000">' . e($wcfg['unmatched_text']) . '</textarea></label>'
        . '<h3>رفتار</h3>'
        . '<label class="check"><input type="checkbox" name="w_autospeak" value="1"' . $wChecked($wcfg['autospeak']) . '> خواندن خودکار پاسخ‌ها با صدا (بدون زدن دکمهٔ بلندگو)</label>'
        . '<label class="field">سقف درخواست در دقیقه برای هر کاربر<input type="number" name="w_rate_limit" min="5" max="120" value="' . (int) $wcfg['rate_limit'] . '"></label>'
        . '<label class="field">موقعیت ربات در صفحه<select name="w_position"><option value="left"' . ($wcfg['position'] === 'left' ? ' selected' : '') . '>پایین-چپ</option><option value="right"' . ($wcfg['position'] === 'right' ? ' selected' : '') . '>پایین-راست</option></select></label>'
        . '<button class="button" type="submit">ذخیره تنظیمات آریا</button></form></div>';
    $tts = chatbot_tts_status();
    $ttsModes = ['auto' => 'خودکار (اول سرور، وگرنه مرورگر)', 'server' => 'فقط موتور سرور (آفلاین قطعی)', 'browser' => 'فقط صدای مرورگر', 'off' => 'خاموش'];
    $ttsVoiceId = chatbot_tts_voice_id($tts['voice']);
    $ttsVoiceLine = 'نامشخص (موتور پیدا نشد)';
    $ttsCfg = chatbot_tts_config();
    if (($tts['active_kind'] ?? '') === 'piper') {
        $ttsVoiceLine = 'موتور عصبی Piper آماده است (' . basename((string) ($tts['piper_model'] ?? '')) . ')';
    } elseif (is_string($tts['espeak']) && $tts['espeak'] !== '') {
        $ttsVoiceCheck = chatbot_tts_voice_status((string) $tts['espeak'], $ttsVoiceId);
        if ($ttsVoiceCheck['status'] === 'yes') {
            $ttsVoiceLine = 'eSpeak: یافت شد (' . $ttsVoiceId . ')';
        } elseif ($ttsVoiceCheck['status'] === 'no') {
            $ttsVoiceLine = 'eSpeak: یافت نشد! خروجی موتور نامفهوم می‌شود — موتور را کامل نصب کنید';
        } else {
            $ttsVoiceLine = 'eSpeak: نامشخص (بررسی نشد)';
        }
    }
    $ttsServerLine = 'پیدا نشد — راهنمای نصب پایین همین کارت';
    if (($tts['active_kind'] ?? '') === 'piper') {
        $ttsServerLine = 'فعال · Piper (' . e((string) ($tts['piper'] ?? '')) . ')';
    } elseif ($tts['server_ready'] && is_string($tts['espeak']) && $tts['espeak'] !== '') {
        $ttsServerLine = 'فعال · eSpeak (' . e((string) $tts['espeak']) . ')';
    }
    $ttsKinds = ['auto' => 'خودکار (اول Piper عصبی، وگرنه eSpeak)', 'piper' => 'فقط Piper (صدای طبیعی)', 'espeak' => 'فقط eSpeak (ربات‌گونه)'];
    echo '<div class="card form-card"><h2>گویندگی بات (آفلاین)</h2>'
        . '<p class="muted">وضعیت موتور سرور: ' . $ttsServerLine . '</p>'
        . '<p class="muted">وضعیت صدای فارسی: ' . e($ttsVoiceLine) . '</p>'
        . '<p class="muted">متن روی صفحه عوض نمی‌شود؛ فقط صدا محاوره‌ای خوانده می‌شود. عددها با حروف گفته می‌شوند و کسره فقط بین دو کلمهٔ جدا می‌آید (سفارشِ غذا)، نه داخل واژه (راهنمای / سفارش‌های).</p>'
        . '<form method="post" action="index.php?page=chatbot-admin">' . csrf_field()
        . '<input type="hidden" name="tts_settings" value="1">'
        . '<label class="field">حالت گویندگی<select name="tts_mode">';
    foreach ($ttsModes as $ttsModeValue => $ttsModeLabel) {
        echo '<option value="' . $ttsModeValue . '"' . ($tts['mode'] === $ttsModeValue ? ' selected' : '') . '>' . e($ttsModeLabel) . '</option>';
    }
    echo '</select></label>'
        . '<label class="field">موتور صدا<select name="tts_kind">';
    foreach ($ttsKinds as $ttsKindValue => $ttsKindLabel) {
        echo '<option value="' . $ttsKindValue . '"' . (($tts['kind'] ?? 'auto') === $ttsKindValue ? ' selected' : '') . '>' . e($ttsKindLabel) . '</option>';
    }
    echo '</select></label>'
        . '<label class="field">مسیر piper.exe (خالی = جست‌وجوی خودکار)<input name="tts_piper_path" dir="ltr" maxlength="500" value="' . e((string) $ttsCfg['piper_path']) . '" placeholder="C:\piper\piper.exe"></label>'
        . '<label class="field">مدل زن (مانا) — خالی = جست‌وجوی خودکار<input name="tts_piper_model" dir="ltr" maxlength="500" value="' . e((string) $ttsCfg['piper_model']) . '" placeholder="C:\piper\fa_IR-mana-medium.onnx"></label>'
        . '<label class="field">مدل مرد (امیر) — خالی = جست‌وجوی خودکار<input name="tts_piper_model_male" dir="ltr" maxlength="500" value="' . e((string) ($ttsCfg['piper_model_male'] ?? '')) . '" placeholder="C:\piper\fa_IR-amir-medium.onnx"></label>'
        . '<label class="field">مسیر فایل espeak-ng روی سرور (خالی = جست‌وجوی خودکار)<input name="tts_espeak_path" dir="ltr" maxlength="500" value="' . e((string) $ttsCfg['espeak_path']) . '" placeholder="C:\Program Files\eSpeak NG\espeak-ng.exe"></label>'
        . '<label class="field">سرعت گفتار (کلمه در دقیقه؛ کمتر = شمرده‌تر و واضح‌تر)<input type="number" name="tts_speed" min="80" max="300" value="' . (int) $tts['speed'] . '"></label>'
        . '<label class="field">نوع صدا (Piper: زن=مانا، مرد=امیر)<select name="tts_voice">';
    foreach (['default' => 'زن (مانا)', 'female' => 'زن (مانا)', 'male' => 'مرد (امیر)'] as $ttsVoiceValue => $ttsVoiceLabel) {
        echo '<option value="' . $ttsVoiceValue . '"' . ($tts['voice'] === $ttsVoiceValue ? ' selected' : '') . '>' . e($ttsVoiceLabel) . '</option>';
    }
    echo '</select></label>'
        . '<button class="button" type="submit">ذخیره تنظیمات صدا</button></form>'
        . '<form method="post" action="index.php?page=chatbot-admin" style="margin-top:8px">' . csrf_field()
        . '<input type="hidden" name="tts_test" value="1">'
        . '<button class="button secondary" type="submit">تست موتور سرور</button></form>'
        . '<form method="post" action="index.php?page=chatbot-admin" style="margin-top:8px">' . csrf_field()
        . '<input type="hidden" name="tts_sample" value="1">'
        . '<button class="button secondary" type="submit">دانلود نمونه صدا</button></form>'
        . '<p class="muted">نصب صدای طبیعی (یک‌بار روی سرور، بعدش آفلاین): ۱) فایل <a href="https://github.com/rhasspy/piper/releases/download/2023.11.14-2/piper_windows_amd64.zip" dir="ltr">piper_windows_amd64.zip</a> را در <code dir="ltr">C:\piper</code> باز کنید تا <code dir="ltr">C:\piper\piper.exe</code> دیده شود. ۲) مدل زن (مانا) — هر دو فایل در همان پوشه: <a href="https://huggingface.co/MahtaFetrat/Mana-Persian-Piper/resolve/main/fa_IR-mana-medium.onnx" dir="ltr">fa_IR-mana-medium.onnx</a> و <a href="https://huggingface.co/MahtaFetrat/Mana-Persian-Piper/resolve/main/fa_IR-mana-medium.onnx.json" dir="ltr">.onnx.json</a>. ۳) مدل مرد (امیر) — هر دو فایل: <a href="https://huggingface.co/rhasspy/piper-voices/resolve/v1.0.0/fa/fa_IR/amir/medium/fa_IR-amir-medium.onnx" dir="ltr">fa_IR-amir-medium.onnx</a> و <a href="https://huggingface.co/rhasspy/piper-voices/resolve/v1.0.0/fa/fa_IR/amir/medium/fa_IR-amir-medium.onnx.json" dir="ltr">.onnx.json</a>. ۴) مسیر piper.exe و دو مسیر مدل را وارد کنید. «نوع صدا» مرد/زن همان مدل را انتخاب می‌کند. ذخیره، سپس تست و دانلود نمونه.</p></div>';

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
    $intentLabels = ['ticket_create' => 'ثبت درخواست', 'ticket_status' => 'پیگیری درخواست', 'ticket_cancel' => 'انصراف از ثبت درخواست', 'ticket_cancellation' => 'راهنمای لغو درخواست', 'guide_help' => 'راهنمای مرحله‌ای'];

    $total = (int) ($stats['total'] ?? 0);
    $pct = static fn(int $part, int $whole): string => $whole > 0 ? round($part * 100 / $whole) . '٪' : '—';
    echo '<div class="card"><h2>گزارش بازه‌ای</h2>'
        . '<form method="get" action="index.php" class="inline-form"><input type="hidden" name="page" value="chatbot-admin">'
        . 'از <input type="date" name="from" value="' . e($from) . '"> تا <input type="date" name="to" value="' . e($to) . '"> '
        . '<button class="button secondary" type="submit">نمایش</button></form>'
        . '<div class="stat-grid">'
        . '<div><strong>' . $total . '</strong><small>کل پرسش‌ها</small></div>'
        . '<div><strong>' . $pct((int) ($stats['answered'] ?? 0), $total) . '</strong><small>پاسخ از FAQ</small></div>'
        . '<div><strong>' . $pct((int) ($stats['intents'] ?? 0), $total) . '</strong><small>ثبت/پیگیری درخواست</small></div>'
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
    $wcfg = chatbot_widget_config();
    $wjs = static function (array $c): array {
        return [
            'greetOn' => (bool) $c['greet_enabled'],
            'greetMs' => (int) $c['greet_interval'] * 1000,
            'bubbleMs' => (int) $c['greet_duration'] * 1000,
            'sound' => false,
            'pending' => (string) $c['pending_text'],
            'autospeak' => (bool) $c['autospeak'],
            'fbYes' => (string) $c['feedback_yes'],
            'fbNo' => (string) $c['feedback_no'],
            'fbThanks' => (string) $c['feedback_thanks'],
        ];
    };
    $wjson = json_encode($wjs($wcfg), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
    if (!is_string($wjson) || $wjson === '') {
        $wjson = (string) json_encode($wjs(chatbot_widget_defaults()), JSON_UNESCAPED_UNICODE);
    }
    $wName = e($wcfg['name']);
    $wBubble = str_replace(chr(10), '<br>', e(str_replace([Chr(13) . Chr(10), Chr(13)], Chr(10), $wcfg['greet_text'])));
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
.cb-fb button{border:1px solid #cbd5e1;background:#fff;border-radius:8px;padding:2px 8px;cursor:pointer;font:inherit;font-size:12px}.cb-say{border:0;background:transparent;cursor:pointer;font-size:15px;padding:0 2px;vertical-align:middle;opacity:.8}.cb-say:hover{opacity:1}.cb-say:disabled{opacity:.45;cursor:default}
.cb-form{display:flex;gap:6px;padding:10px;border-top:1px solid #e2e8f0}
.cb-form input{flex:1;padding:8px 10px;border:1px solid #cbd5e1;border-radius:10px;font:inherit}
.cb-form button{background:#0f766e;color:#fff;border:0;border-radius:10px;padding:8px 14px;cursor:pointer;font:inherit}
</style>' . ($wcfg['position'] === 'right' ? '<style>#cb-open{left:auto;right:20px}#cb-panel{left:auto;right:18px}#cb-bubble{left:auto;right:150px}#cb-bubble::before{left:auto;right:-7px;border:7px solid transparent;border-left-color:#fff;border-right:0}</style>' : '') . '
<div class="cb-bot-launch" id="cb-open" role="button" tabindex="0" aria-label="' . $wName . '" title="' . $wName . '"><div class="cb-bubble" id="cb-bubble">' . $wBubble . '</div><div class="cb-bot-body"><img class="cb-layer" alt="" src="assets/chatbot/robot-body.png"><img class="cb-layer cb-arm" alt="" src="assets/chatbot/robot-arm.png"></div></div>
<div class="cb-panel" id="cb-panel" role="dialog" aria-label="' . $wName . '">
  <div class="cb-head"><span>' . $wName . '</span><span style="display:flex;gap:6px"><button class="cb-resize" type="button" id="cb-resize" title="بزرگ/کوچک">⤢</button><button class="cb-resize" type="button" id="cb-close" aria-label="بستن" title="بستن">✕</button></span></div>
  <div class="cb-log" id="cb-log"><div class="cb-msg cb-bot">' . e($wcfg['welcome_text']) . '</div></div>
  <form class="cb-form" id="cb-form"><input id="cb-input" maxlength="500" placeholder="' . e($wcfg['input_placeholder']) . '" autocomplete="off"><button type="submit">' . e($wcfg['send_label']) . '</button></form>
</div>
<script nonce="' . e(csp_nonce()) . '">
(function(){
  var csrf = ' . $token . ';
  var cfg = ' . $wjson . ';
  var panel = document.getElementById("cb-panel"), log = document.getElementById("cb-log");
  var form = document.getElementById("cb-form"), input = document.getElementById("cb-input");
  var bot = document.getElementById("cb-open"), bubble = document.getElementById("cb-bubble");
  function greet(){ if (panel.classList.contains("open")) return; bot.classList.remove("cb-wave", "cb-buzz"); void bot.offsetWidth; bot.classList.add("cb-wave", "cb-buzz"); bubble.classList.add("show"); setTimeout(function(){ bubble.classList.remove("show"); }, cfg.bubbleMs); setTimeout(function(){ bot.classList.remove("cb-wave"); }, 2700); }
  function toggleBot(){ var willOpen = !panel.classList.contains("open"); panel.classList.toggle("open"); bubble.classList.remove("show"); if (willOpen) { input.focus(); } else { stopVoice(); } }
  bot.addEventListener("click", toggleBot);
  bot.addEventListener("keydown", function(e){ if (e.key === "Enter" || e.key === " ") { e.preventDefault(); toggleBot(); } });
  document.getElementById("cb-close").addEventListener("click", function(){ panel.classList.remove("open"); bubble.classList.remove("show"); stopVoice(); });
  if (cfg.greetOn) { setInterval(greet, cfg.greetMs); }
  document.getElementById("cb-resize").addEventListener("click", function(){ var big = panel.classList.toggle("cb-big"); this.textContent = big ? "⤡" : "⤢"; });
  function add(text, cls){ var d = document.createElement("div"); d.className = "cb-msg " + cls; d.textContent = text; log.appendChild(d); log.scrollTop = log.scrollHeight; return d; }
  function post(op, params){
    var body = new URLSearchParams(params); body.append("csrf", csrf);
    return fetch("index.php?page=chatbot-api&op=" + op, {method:"POST", body: body, credentials:"same-origin"}).then(function(r){ return r.json(); });
  }
  function feedback(wrap, logId){
    var row = document.createElement("div"); row.className = "cb-fb";
    [["1",cfg.fbYes],["0",cfg.fbNo]].forEach(function(p){
      var b = document.createElement("button"); b.type = "button"; b.textContent = p[1];
      b.addEventListener("click", function(){ row.textContent = cfg.fbThanks; post("feedback", {log_id: logId, helpful: p[0]}); });
      row.appendChild(b);
    });
    wrap.appendChild(row);
  }
  var voiceInfo = null, voiceLoading = null, currentAudio = null, speakingUtterance = null;
  function stopVoice(){
    try { if (currentAudio) { currentAudio.pause(); currentAudio = null; } } catch (e) {}
    try { if (window.speechSynthesis) { window.speechSynthesis.cancel(); } } catch (e) {}
    speakingUtterance = null;
    var says = document.querySelectorAll(".cb-say");
    for (var i = 0; i < says.length; i++) { says[i].textContent = "🔊"; says[i].disabled = false; }
  }
  function voiceStatus(){
    if (voiceInfo) { return Promise.resolve(voiceInfo); }
    if (!voiceLoading) {
      voiceLoading = post("voice-status", {}).then(function(j){
        voiceInfo = (j && j.ok && j.voice) ? j.voice : {engine:"none"};
        return voiceInfo;
      }).catch(function(){ voiceLoading = null; return {engine:"none"}; });
    }
    return voiceLoading;
  }
  function browserFaVoice(){
    try {
      var voices = window.speechSynthesis ? window.speechSynthesis.getVoices() : [];
      for (var i = 0; i < voices.length; i++) {
        var lang = (voices[i].lang || "").toLowerCase();
        if (lang === "fa" || lang.indexOf("fa-") === 0 || lang.indexOf("fa_") === 0) { return voices[i]; }
      }
    } catch (e) {}
    return null;
  }
  function withVoicesReady(fn){
    try {
      var synth = window.speechSynthesis;
      if (!synth || synth.getVoices().length > 0) { fn(); return; }
      var done = false;
      var timer = setTimeout(function(){ if (!done) { done = true; fn(); } }, 1200);
      synth.onvoiceschanged = function(){ if (!done) { done = true; clearTimeout(timer); fn(); } };
    } catch (e) { fn(); }
  }
  function speakServer(btn, text){
    btn.textContent = "⏳"; btn.disabled = true;
    var body = new URLSearchParams({text: text}); body.append("csrf", csrf);
    fetch("index.php?page=chatbot-api&op=say", {method:"POST", body: body, credentials:"same-origin"}).then(function(r){
      return r.text().then(function(t){
        var j = null;
        try { j = JSON.parse(t); } catch (e) { j = null; }
        if (!r.ok || !j || !j.ok || !j.url) { throw new Error((j && j.error) || ("خطای سرور (" + r.status + ")")); }
        return j.url;
      });
    }).then(function(url){
      stopVoice();
      var audio = new Audio();
      audio.preload = "auto";
      audio.src = url;
      currentAudio = audio;
      btn.textContent = "⏹"; btn.disabled = false;
      audio.onended = function(){ stopVoice(); };
      audio.onerror = function(){ stopVoice(); add("پخش صدا ممکن نشد.", "cb-bot"); };
      var played = null;
      try { played = audio.play(); } catch (e) { played = null; }
      if (played && played.catch) { played.catch(function(){ stopVoice(); }); }
    }).catch(function(err){
      btn.textContent = "🔊"; btn.disabled = false;
      add("گویندگی سرور در دسترس نیست (" + (err.message || "خطا") + ").", "cb-bot");
    });
  }
  function speakBrowser(btn, text){
    withVoicesReady(function(){
      var voice = browserFaVoice();
      if (!voice) { add("صدای فارسی در این دستگاه/مرورگر نصب نیست؛ از مدیر بخواهید موتور صوتی سرور را فعال کند.", "cb-bot"); return; }
      stopVoice();
      try {
        var u = new SpeechSynthesisUtterance(text);
        u.lang = "fa-IR"; u.voice = voice; u.rate = 1;
        speakingUtterance = u;
        btn.textContent = "⏹";
        u.onend = function(){ if (speakingUtterance === u) { stopVoice(); } };
        u.onerror = function(){ if (speakingUtterance === u) { stopVoice(); } };
        window.speechSynthesis.speak(u);
      } catch (e) { add("پخش صدای مرورگر ممکن نشد.", "cb-bot"); }
    });
  }
  function sayButtonClicked(btn){
    var wrap = btn.parentNode;
    var text = wrap ? (wrap.getAttribute("data-say") || "") : "";
    if (!text) { return; }
    if (btn.textContent === "⏹") { stopVoice(); return; }
    voiceStatus().then(function(info){
      var engine = (info && info.engine) || "none";
      if (engine === "server" && !(info && info.server_ready)) { engine = "none"; }
      if (engine === "server") { speakServer(btn, text); }
      else if (engine === "browser") { speakBrowser(btn, text); }
      else { add("گویندگی فعال نیست. مدیر سامانه می‌تواند آن را از صفحهٔ مدیریت بات سخنگو روشن کند.", "cb-bot"); }
    });
  }
  function attachSay(wrap, text){
    voiceStatus().then(function(info){
      if (!info || info.engine === "none") { return; }
      var b = document.createElement("button");
      b.type = "button"; b.className = "cb-say"; b.textContent = "🔊"; b.title = "شنیدن پاسخ";
      b.setAttribute("aria-label", "شنیدن پاسخ");
      b.addEventListener("click", function(){ sayButtonClicked(b); });
      wrap.setAttribute("data-say", text);
      wrap.appendChild(document.createTextNode(" "));
      wrap.appendChild(b);
      if (cfg.autospeak) { sayButtonClicked(b); }
    });
  }
  form.addEventListener("submit", function(e){
    e.preventDefault();
    var q = input.value.trim(); if (!q) return;
    add(q, "cb-user"); input.value = "";
    var pending = add(cfg.pending, "cb-bot");
    post("ask", {question: q}).then(function(j){
      pending.textContent = j.ok ? j.answer : (j.error || "خطا در ارتباط با بات.");
      if (j.ok) {
        feedback(pending, j.log_id);
        attachSay(pending, j.answer);
        if (j.link) {
          var lk = document.createElement("a"); lk.href = j.link.url; lk.textContent = j.link.label;
          lk.style.display = "inline-block"; lk.style.marginTop = "6px"; pending.appendChild(lk);
        } else if (!j.matched && j.can_ticket) {
          var a = document.createElement("a"); a.href = "index.php?page=new-ticket"; a.textContent = "ثبت درخواست جدید";
          a.style.display = "inline-block"; a.style.marginTop = "6px"; pending.appendChild(a);
        }
      }
    }).catch(function(){ pending.textContent = "ارتباط با سرور برقرار نشد. دوباره تلاش کنید."; });
  });
})();
</script><script src="assets/chatbot-drag.js?v=2" defer></script>';
}
