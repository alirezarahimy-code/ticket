<?php
declare(strict_types=1);

/**
 * tests/test_ticket_number_autofill_unit.php
 *
 * ticket_number_autofill() را با یک دیتابیس شبیه‌سازی‌شده در حافظه آزمایش می‌کند —
 * هرگز به دیتابیس تولید وصل نمی‌شود.
 *
 * باگی که این تست نگهبانش است: شمارنده از صفر شروع می‌شد، پس روی جدولی که از قبل
 * IT-00001 داشت شمارهٔ تکراری می‌ساخت، به uq_tickets_no می‌خورد، استثنا کل حلقه را
 * قطع می‌کرد و نشانگر «ticket_autofill_done» هرگز ثبت نمی‌شد — یعنی در هر بارگذاری
 * صفحه دوباره همین اسکن ناموفق تکرار می‌شد.
 */

const APP_ROOT = __DIR__ . '/..';

$GLOBALS['tn_tickets'] = [
    ['id' => 1, 'service_group' => 'it',      'ticket_no' => 'IT-00001'],
    ['id' => 2, 'service_group' => 'it',      'ticket_no' => 'IT-00002'],
    ['id' => 3, 'service_group' => 'it',      'ticket_no' => 'IT-00003'],
    ['id' => 4, 'service_group' => 'it',      'ticket_no' => null],
    ['id' => 5, 'service_group' => 'support', 'ticket_no' => null],
    ['id' => 6, 'service_group' => 'it',      'ticket_no' => ''],
];
$GLOBALS['tn_seq'] = ['IT' => 0];          // شمارندهٔ عقب‌مانده — بدترین حالت
$GLOBALS['tn_settings'] = [];
$GLOBALS['tn_update_attempts'] = 0;

function tn_missing_rows(): array
{
    $out = [];
    foreach ($GLOBALS['tn_tickets'] as $row) {
        if ($row['ticket_no'] === null || $row['ticket_no'] === '') {
            $out[] = ['id' => $row['id'], 'service_group' => $row['service_group']];
        }
    }
    usort($out, static fn(array $a, array $b): int => $a['id'] <=> $b['id']);
    return $out;
}

class TnStatement
{
    public array $bound = [];
    public function __construct(public string $sql) {}

    public function execute(array $parameters = []): bool
    {
        $this->bound = $parameters;
        if (str_contains($this->sql, 'UPDATE tickets SET ticket_no')) {
            $GLOBALS['tn_update_attempts']++;
            $number = (string) $parameters[0];
            $id = (int) $parameters[1];
            foreach ($GLOBALS['tn_tickets'] as $row) {   // شبیه‌سازی UNIQUE KEY uq_tickets_no
                if ($row['ticket_no'] === $number) {
                    throw new RuntimeException('Duplicate entry for key uq_tickets_no');
                }
            }
            foreach ($GLOBALS['tn_tickets'] as $index => $row) {
                if ($row['id'] === $id && ($row['ticket_no'] === null || $row['ticket_no'] === '')) {
                    $GLOBALS['tn_tickets'][$index]['ticket_no'] = $number;
                }
            }
            return true;
        }
        if (str_contains($this->sql, 'INSERT INTO ticket_number_seq')) {
            $prefix = (string) $parameters[0];
            $GLOBALS['tn_seq'][$prefix] = max($GLOBALS['tn_seq'][$prefix] ?? 0, (int) $parameters[1]);
            return true;
        }
        return true;
    }

    public function fetchColumn(int $column = 0): mixed
    {
        if (str_contains($this->sql, 'SELECT seq FROM ticket_number_seq')) {
            return $GLOBALS['tn_seq'][(string) ($this->bound[0] ?? '')] ?? false;
        }
        if (str_contains($this->sql, 'MAX(CAST(SUBSTRING_INDEX')) {
            $prefix = rtrim((string) ($this->bound[0] ?? ''), '-%');
            $maximum = 0;
            foreach ($GLOBALS['tn_tickets'] as $row) {
                if (is_string($row['ticket_no'])
                    && preg_match('/^' . preg_quote($prefix, '/') . '-(\d+)$/', $row['ticket_no'], $match)) {
                    $maximum = max($maximum, (int) $match[1]);
                }
            }
            return $maximum;
        }
        if (str_contains($this->sql, 'SELECT COUNT(*) FROM tickets')) {
            return count(tn_missing_rows());
        }
        return false;
    }

    public function fetchAll(int ...$args): array
    {
        if (str_contains($this->sql, 'SELECT id, service_group FROM tickets')) {
            return array_slice(tn_missing_rows(), 0, 500);
        }
        return [];
    }

    public function fetch(int ...$args): mixed { return false; }
}

class TnDb
{
    public function prepare(string $sql): TnStatement { return new TnStatement($sql); }
    public function query(string $sql): TnStatement { return new TnStatement($sql); }
    public function inTransaction(): bool { return false; }
    public function exec(string $sql): int { return 0; }
}

function db(): TnDb { static $db; return $db ??= new TnDb(); }
function setting(string $key, ?string $default = null): ?string { return $GLOBALS['tn_settings'][$key] ?? $default; }
function save_setting(string $key, string $value): void { $GLOBALS['tn_settings'][$key] = $value; }
function db_in_transaction(): bool { return false; }
function cfg(string $key, mixed $default = null): mixed { return $default; }

require APP_ROOT . '/lib/tickets-sla.php';

function tn_check(bool $condition, string $message): void
{
    if (!$condition) { throw new RuntimeException($message); }
    echo "ok - $message\n";
}

// ── ۱) کف شمارنده از بزرگ‌ترین شمارهٔ موجود خوانده می‌شود، نه از صفر ──
tn_check(ticket_number_sequence_floor('IT') === 3, 'sequence floor follows the highest existing number, not the stale counter');
tn_check(ticket_number_sequence_floor('SUP') === 0, 'an unused prefix starts from zero');

// ── ۲) ترمیم کامل بدون برخورد یکتایی ──
ticket_number_autofill(true);

$numbers = [];
foreach ($GLOBALS['tn_tickets'] as $row) { $numbers[$row['id']] = $row['ticket_no']; }
ksort($numbers);

tn_check($numbers[4] === 'IT-00004', 'the first unnumbered IT ticket continues after IT-00003');
tn_check($numbers[5] === 'SUP-00001', 'a different service group keeps its own counter');
tn_check($numbers[6] === 'IT-00005', 'an empty-string number is filled too');

$stored = array_values(array_filter($numbers, static fn($value): bool => (string) $value !== ''));
tn_check(count($stored) === count(array_unique($stored)), 'no duplicate ticket numbers are produced');
tn_check(tn_missing_rows() === [], 'no ticket is left without a number');

// ── ۳) هر ردیف دقیقاً یک تلاش: یعنی هیچ برخوردی رخ نداده ──
tn_check($GLOBALS['tn_update_attempts'] === 3, 'each row needs exactly one update attempt (no unique-key collisions)');

// ── ۴) نشانگر ثبت می‌شود تا اسکن در هر بارگذاری صفحه تکرار نشود ──
tn_check(($GLOBALS['tn_settings']['ticket_autofill_done'] ?? '') === '1', 'the completion marker is stored after a successful repair');

// ── ۵) شمارنده برای تولید شمارهٔ بعدی به‌روز می‌شود ──
tn_check(($GLOBALS['tn_seq']['IT'] ?? 0) === 5, 'the IT counter is persisted so the next new ticket continues from IT-00006');
tn_check(($GLOBALS['tn_seq']['SUP'] ?? 0) === 1, 'the support counter is persisted as well');

echo "\nهمهٔ بررسی‌های شماره‌گذاری خودکار موفق بود.\n";
