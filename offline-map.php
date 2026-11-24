<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';
$user = require_login();
require_once __DIR__ . '/modules/offline-map/MapRepository.php';

$respond = static function (int $status, array $payload): never {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
};
$canView = user_can($user, 'map.view');
$canManage = user_can($user, 'map.features.manage') || user_can($user, 'map.admin');
$canAdmin = user_can($user, 'map.admin');
if (!$canView) {
    security_log_access_denied($user, 'map.view', 'offline-map');
    $respond(403, ['error' => 'دسترسی به نقشه مجاز نیست.']);
}

$repo = new OfflineMapRepository(db());
if (!$repo->ready() && isset($_GET['attachment'])) { http_response_code(503); exit('ساختار پایگاه‌دادهٔ نقشه نصب نشده است.'); }

if (isset($_GET['attachment'])) {
    $attachment = $repo->attachment(max(0, (int) $_GET['attachment']));
    if (!$attachment || (!$canAdmin && (int) $attachment['feature_owner'] !== (int) $user['id'])) {
        http_response_code(404);
        exit('فایل پیدا نشد.');
    }
    $stored = (string) $attachment['stored_name'];
    if (!preg_match('/^[a-f0-9]{32}\\.(pdf|png|jpg|webp)$/', $stored)) {
        http_response_code(404); exit('فایل معتبر نیست.');
    }
    $filePath = __DIR__ . '/storage/private/offline-map/' . $stored;
    if (!is_file($filePath)) { http_response_code(404); exit('فایل روی سرور موجود نیست.'); }
    header('Content-Type: ' . $attachment['mime_type']);
    header('Content-Length: ' . (string) filesize($filePath));
    header("Content-Disposition: attachment; filename*=UTF-8''" . rawurlencode((string) $attachment['original_name']));
    header('X-Content-Type-Options: nosniff');
    header('Cache-Control: private, no-store');
    readfile($filePath);
    exit;
}
if (!$repo->ready()) {
    if (($_GET['api'] ?? '') === '1') $respond(503, ['error' => 'ساختار پایگاه‌دادهٔ نقشه نصب نشده است.']);
    require __DIR__ . '/modules/offline-map/Pages/index.php';
    exit;
}

$normaliseGeometry = static function (array $geometry): array {
    $type = $geometry['type'] ?? '';
    $coords = $geometry['coordinates'] ?? null;
    if (!in_array($type, ['Point', 'LineString', 'Polygon'], true) || !is_array($coords)) {
        throw new InvalidArgumentException('نوع هندسه پشتیبانی نمی‌شود.');
    }
    $count = 0;
    $walk = static function ($value, int $depth = 0) use (&$walk, &$count): void {
        if ($depth > 5) throw new InvalidArgumentException('ساختار هندسه نامعتبر است.');
        if (!is_array($value)) throw new InvalidArgumentException('مختصات هندسه نامعتبر است.');
        if (isset($value[0]) && is_numeric($value[0])) {
            if (count($value) < 2 || !is_numeric($value[1])) throw new InvalidArgumentException('مختصات باید طول و عرض داشته باشد.');
            $lon = (float) $value[0]; $lat = (float) $value[1];
            if (!is_finite($lon) || !is_finite($lat) || $lon < 44 || $lon > 64 || $lat < 24 || $lat > 40) {
                throw new InvalidArgumentException('مختصات خارج از محدودهٔ تقریبی ایران است.');
            }
            $count++;
            return;
        }
        foreach ($value as $child) $walk($child, $depth + 1);
    };
    $walk($coords);
    if ($count > 10000) throw new InvalidArgumentException('هندسه بیش از ۱۰ هزار رأس دارد.');
    if ($type === 'Point' && $count !== 1) throw new InvalidArgumentException('نقطه باید دقیقاً یک مختصات داشته باشد.');
    if ($type === 'LineString' && (count($coords) < 2 || count($coords) > 10000)) throw new InvalidArgumentException('خط باید حداقل دو رأس داشته باشد.');
    if ($type === 'Polygon') {
        if (!$coords || !isset($coords[0])) throw new InvalidArgumentException('محدوده باید حداقل یک حلقه داشته باشد.');
        foreach ($coords as $ring) {
            if (!is_array($ring) || count($ring) < 4) throw new InvalidArgumentException('هر حلقهٔ محدوده باید حداقل سه رأس و یک نقطهٔ پایانی بسته داشته باشد.');
            $first = $ring[0]; $last = $ring[count($ring) - 1];
            if (!is_array($first) || !is_array($last)
                || (float) $first[0] !== (float) $last[0]
                || (float) $first[1] !== (float) $last[1]) {
                throw new InvalidArgumentException('تمام حلقه‌های چندضلعی باید بسته باشند.');
            }
        }
    }
    return ['type' => $type, 'coordinates' => $coords];
};

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET' && (string) ($_GET['api'] ?? '') === '1') {
    $kind = (string) ($_GET['kind'] ?? 'features');
    if ($kind === 'attachments') {
        $featureId = max(0, (int) ($_GET['feature_id'] ?? 0));
        $feature = $repo->find($featureId);
        if (!$feature || (!$canAdmin && (int) $feature['created_by'] !== (int) $user['id'])) $respond(404, ['error' => 'عارضه پیدا نشد.']);
        $items = $repo->attachments($featureId);
        foreach ($items as &$item) {
            $item['id'] = (int) $item['id'];
            $item['size_bytes'] = (int) $item['size_bytes'];
            $item['download_url'] = 'offline-map.php?attachment=' . $item['id'];
        }
        unset($item);
        $respond(200, ['attachments' => $items]);
    }
    if ($kind === 'places') {
        $q = trim((string) ($_GET['q'] ?? ''));
        if ($q === '' || mb_strlen($q) < 2 || mb_strlen($q) > 100) $respond(200, ['results' => []]);
        $searchPath = __DIR__ . '/modules/offline-map/data/search.sqlite';
        if (!is_file($searchPath) || !extension_loaded('pdo_sqlite')) $respond(503, ['error' => 'شاخص جست‌وجوی آفلاین نصب نشده یا PDO SQLite فعال نیست.']);
        try {
            $db = new PDO('sqlite:' . $searchPath, null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
            $normalise = static function (string $value): string {
                $value = strtr($value, ['ي'=>'ی','ى'=>'ی','ك'=>'ک','ۀ'=>'ه','ة'=>'ه','ؤ'=>'و','إ'=>'ا','أ'=>'ا','ٱ'=>'ا','٠'=>'0','١'=>'1','٢'=>'2','٣'=>'3','٤'=>'4','٥'=>'5','٦'=>'6','٧'=>'7','٨'=>'8','٩'=>'9','۰'=>'0','۱'=>'1','۲'=>'2','۳'=>'3','۴'=>'4','۵'=>'5','۶'=>'6','۷'=>'7','۸'=>'8','۹'=>'9']);
                $value = preg_replace('/[\\x{064B}-\\x{065F}\\x{0670}\\x{0640}]/u', '', $value) ?? $value;
                return trim(preg_replace('/[\\s\\x{200c}\\x{200d}]+/u', ' ', $value) ?? $value);
            };
            $term = $normalise($q);
            $results = [];
            try {
                $tokens = preg_split('/\\s+/u', $term, -1, PREG_SPLIT_NO_EMPTY) ?: [];
                $ftsQuery = implode(' AND ', array_map(static fn(string $token): string => '"' . str_replace('"', '""', $token) . '"*', $tokens));
                if ($ftsQuery !== '') {
                    $stmt = $db->prepare('SELECT p.id,p.name,p.name_fa,p.kind,p.category,p.lat,p.lon FROM places_fts JOIN places p ON p.rowid=places_fts.rowid WHERE places_fts MATCH :q ORDER BY p.rank DESC LIMIT 30');
                    $stmt->execute([':q' => $ftsQuery]);
                    $results = $stmt->fetchAll();
                }
            } catch (Throwable) {
                $results = [];
            }
            if (!$results) {
                $stmt = $db->prepare('SELECT id,name,name_fa,kind,category,lat,lon FROM places WHERE normalized LIKE :q ORDER BY rank DESC LIMIT 30');
                $stmt->execute([':q' => '%' . $term . '%']);
                $results = $stmt->fetchAll();
            }
            $respond(200, ['results' => $results]);
        } catch (Throwable $e) {
            error_log('[offline-map] search index: ' . $e->getMessage());
            $respond(500, ['error' => 'جست‌وجوی آفلاین با خطا مواجه شد.']);
        }
    }
    $q = trim((string) ($_GET['q'] ?? ''));
    if (mb_strlen($q) > 120) $respond(422, ['error' => 'عبارت جست‌وجو بیش از حد طولانی است.']);
    $owner = (($_GET['mine'] ?? '') === '1') ? (int) $user['id'] : null;
    $respond(200, ['features' => $repo->search($q, 1000, $owner)]);
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET') {
    require __DIR__ . '/modules/offline-map/Pages/index.php';
    exit;
}
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    header('Allow: GET, POST');
    $respond(405, ['error' => 'روش درخواست پشتیبانی نمی‌شود.']);
}
require_csrf(false);
if (!$canManage) $respond(403, ['error' => 'مجوز مدیریت عوارض نقشه را ندارید.']);

$action = (string) ($_POST['action'] ?? 'create');
if ($action === 'attachment') {
    $featureId = max(0, (int) ($_POST['feature_id'] ?? 0));
    $feature = $repo->find($featureId);
    if (!$feature || (!$canAdmin && (int) $feature['created_by'] !== (int) $user['id'])) $respond(404, ['error' => 'عارضه پیدا نشد یا اجازهٔ پیوست ندارید.']);
    if (empty($_FILES['files']) || !is_array($_FILES['files']['name'])) $respond(422, ['error' => 'فایلی برای بارگذاری انتخاب نشده است.']);
    $allowed = ['application/pdf' => 'pdf', 'image/png' => 'png', 'image/jpeg' => 'jpg', 'image/webp' => 'webp'];
    $storage = __DIR__ . '/storage/private/offline-map';
    if (!is_dir($storage) && !mkdir($storage, 0750, true) && !is_dir($storage)) $respond(500, ['error' => 'مسیر امن ذخیره‌سازی ساخته نشد.']);
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $saved = [];
    $total = count($_FILES['files']['name']);
    if ($total > 8) $respond(422, ['error' => 'در هر بار حداکثر ۸ فایل قابل بارگذاری است.']);
    for ($i = 0; $i < $total; $i++) {
        $error = (int) ($_FILES['files']['error'][$i] ?? UPLOAD_ERR_NO_FILE);
        if ($error !== UPLOAD_ERR_OK) $respond(422, ['error' => 'بارگذاری یکی از فایل‌ها ناموفق بود.']);
        $tmp = (string) $_FILES['files']['tmp_name'][$i];
        $size = (int) ($_FILES['files']['size'][$i] ?? 0);
        if ($size < 1 || $size > 10 * 1024 * 1024 || !is_uploaded_file($tmp)) $respond(422, ['error' => 'حجم فایل باید حداکثر ۱۰ مگابایت باشد.']);
        $mime = (string) $finfo->file($tmp);
        if (!isset($allowed[$mime])) $respond(415, ['error' => 'فقط PDF و تصویر PNG/JPEG/WEBP مجاز است.']);
        $stored = bin2hex(random_bytes(16)) . '.' . $allowed[$mime];
        if (!move_uploaded_file($tmp, $storage . '/' . $stored)) $respond(500, ['error' => 'ذخیرهٔ فایل روی سرور ناموفق بود.']);
        $name = basename((string) $_FILES['files']['name'][$i]);
        $name = preg_replace('/[\\x00-\\x1F\\x7F]/u', '', $name) ?: 'attachment';
        $attachmentId = $repo->addAttachment($featureId, mb_substr($name, 0, 255), $stored, $mime, $size, (int) $user['id']);
        $saved[] = ['id' => $attachmentId, 'name' => $name];
    }
    $respond(201, ['attachments' => $saved, 'message' => 'پیوست‌ها بارگذاری شدند.']);
}
if ($action === 'delete') {
    $deleted = $repo->delete(max(0, (int) ($_POST['id'] ?? 0)), (int) $user['id'], $canAdmin);
    $respond($deleted ? 200 : 404, ['deleted' => $deleted]);
}
if ($action === 'save') {
    $id = max(0, (int) ($_POST['id'] ?? 0));
    $title = trim((string) ($_POST['title'] ?? ''));
    $description = trim((string) ($_POST['description'] ?? ''));
    $rawGeometry = json_decode((string) ($_POST['geometry'] ?? ''), true);
    $properties = json_decode((string) ($_POST['properties'] ?? '{}'), true);
    if (!is_array($rawGeometry)) $respond(422, ['error' => 'هندسه ارسال نشده یا JSON آن نامعتبر است.']);
    if (!is_array($properties)) $properties = [];
    $properties = array_intersect_key($properties, array_flip(['category','color','icon','reference','phone','address','notes']));
    if ($title === '' || mb_strlen($title) > 190 || mb_strlen($description) > 4000) $respond(422, ['error' => 'عنوان یا توضیحات معتبر نیست.']);
    try {
        $geometry = $normaliseGeometry($rawGeometry);
        if ($id > 0) {
            $saved = $repo->update($id, $title, $description, $geometry, $properties, (int) $user['id'], $canAdmin);
            $respond($saved ? 200 : 404, ['id' => $id, 'message' => 'تغییرات ذخیره شد.']);
        }
        $newId = $repo->create($title, $description, $geometry, $properties, (int) $user['id']);
        $respond(201, ['id' => $newId, 'message' => 'عارضه ثبت شد.']);
    } catch (InvalidArgumentException $e) {
        $respond(422, ['error' => $e->getMessage()]);
    } catch (Throwable $e) {
        error_log('[offline-map] save failed: ' . $e->getMessage());
        $respond(500, ['error' => 'ذخیرهٔ عارضه انجام نشد. گزارش فنی در لاگ سرور ثبت شد.']);
    }
}
$respond(400, ['error' => 'عملیات درخواست‌شده شناخته نشد.']);
