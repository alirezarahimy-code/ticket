<?php
declare(strict_types=1);

/** Offline GIS workspace rendered inside the authenticated application shell. */
$user = require_permission('map.view');
require_once __DIR__ . '/../MapRepository.php';
$mapRepo = new OfflineMapRepository(db());
$schemaReady = $mapRepo->ready();
$canManageFeatures = user_can($user, 'map.features.manage') || user_can($user, 'map.admin');
$dataDir = __DIR__ . '/../data';
$mapDataPath = $dataDir . '/iran.pmtiles';
$manifestPath = $dataDir . '/manifest.json';
$mapStylePath = $dataDir . '/style.json';
$manifest = is_file($manifestPath) ? json_decode((string) file_get_contents($manifestPath), true) : null;
$styleJson = is_file($mapStylePath) ? json_decode((string) file_get_contents($mapStylePath), true) : null;
$styleText = is_file($mapStylePath) ? (string) file_get_contents($mapStylePath) : '';
$manifestReady = is_array($manifest)
    && ($manifest['tiles']['file'] ?? '') === 'iran.pmtiles'
    && ($manifest['tiles']['format'] ?? '') === 'PMTiles'
    && (int) ($manifest['tiles']['bytes'] ?? 0) === (is_file($mapDataPath) ? (int) filesize($mapDataPath) : 0);
$styleHasPmtiles = is_array($styleJson) && preg_match('~pmtiles://[^"\\\\]*iran\\.pmtiles~i', $styleText) === 1;
$styleTextWithoutPmtiles = str_ireplace('pmtiles://', '', $styleText);
$styleIsOffline = is_array($styleJson)
    && !preg_match('~(?:https?:)?//|mapbox://~i', $styleTextWithoutPmtiles)
    && $styleHasPmtiles;
$mapLibraryPath = __DIR__ . '/../vendor/maplibre-gl.js';
$pmtilesLibraryPath = __DIR__ . '/../vendor/pmtiles.js';
$mapCssPath = __DIR__ . '/../vendor/maplibre-gl.css';
$glyphStackPath = $dataDir . '/fonts/Noto Sans Arabic Regular';
$glyphsReady = is_file($glyphStackPath . '/0-255.pbf') && is_file($glyphStackPath . '/1536-1791.pbf');
$mapReady = is_file($mapDataPath) && $manifestReady && $styleIsOffline && $glyphsReady && is_file($mapLibraryPath) && is_file($pmtilesLibraryPath) && is_file($mapCssPath);
$searchReady = is_file($dataDir . '/search.sqlite');
$overlayDir = $dataDir . '/overlays';
$overlayManifestPath = $overlayDir . '/manifest.json';
$overlayManifest = is_file($overlayManifestPath) ? json_decode((string) file_get_contents($overlayManifestPath), true) : null;
$overlayReady = static function (string $key, string $file) use ($overlayDir, $overlayManifest): bool {
    $path = $overlayDir . '/' . $file;
    $entry = is_array($overlayManifest) ? ($overlayManifest['datasets'][$key] ?? null) : null;
    return is_file($path) && is_array($entry)
        && ($entry['file'] ?? '') === $file
        && trim((string) ($entry['source'] ?? '')) !== ''
        && trim((string) ($entry['license'] ?? '')) !== ''
        && trim((string) ($entry['updated_at'] ?? '')) !== ''
        && (int) ($entry['bytes'] ?? 0) === (int) filesize($path);
};
$officialBoundariesReady = $overlayReady('official_boundaries', 'official-boundaries.geojson');
$municipalDistrictsReady = $overlayReady('municipal_districts', 'municipal-districts.geojson');
$transitOverlayReady = $overlayReady('transit', 'transit.geojson');
$features = $schemaReady ? $mapRepo->search(trim((string) ($_GET['q'] ?? '')), 1000) : [];
render_header('سامانه اطلاعات مکانی آفلاین ایران', $user);
?>
<link rel="stylesheet" href="modules/offline-map/Assets/offline-map.css?v=3">
<?php if ($mapReady): ?><link rel="stylesheet" href="modules/offline-map/vendor/maplibre-gl.css"><?php endif; ?>
<section class="page-heading offline-map-heading">
  <div><span class="eyebrow">سامانه اطلاعات مکانی</span><h1>نقشهٔ آفلاین ایران</h1><p>نقشهٔ پایه، جست‌وجوی محلی و عوارض مکانی سازمان در یک محیط مستقل.</p></div>
  <div class="offline-map-heading-actions"><span class="offline-map-status <?= $mapReady ? 'is-ready' : 'is-pending' ?>"><?= $mapReady ? 'بستهٔ پایه نصب شده' : 'بستهٔ پایه نصب نشده' ?></span><a class="button secondary" href="index.php">بازگشت</a></div>
</section>
<?php if (!$schemaReady): ?><section class="card offline-map-notice"><h2>پایگاه‌دادهٔ ماژول آماده نیست</h2><p>مهاجرت‌های <code>migrations/038-offline-map-features.sql</code> و <code>migrations/039-offline-map-attachments.sql</code> را روی نسخهٔ پشتیبان‌شده اجرا کنید.</p></section><?php endif; ?>
<?php if (!$mapReady): ?><section class="card offline-map-notice" role="status"><h2>فایل‌های نقشهٔ پایه نصب نشده‌اند</h2><p>برای نمایش نقشه، PMTiles، manifest، استایل محلی، کتابخانه‌های MapLibre و PMTiles باید در مسیر <code>modules/offline-map/</code> نصب شوند. در حالت آفلاین هیچ سرویس اینترنتی جایگزین نمی‌شود.</p></section><?php endif; ?>
<div class="offline-map-workspace">
  <main class="offline-map-main card">
    <div class="offline-map-toolbar" role="toolbar" aria-label="ابزارهای نقشه">
      <div class="offline-map-tool-group">
        <button type="button" class="button secondary map-tool is-active" data-tool="pan" title="حرکت و انتخاب">انتخاب</button>
        <?php if ($canManageFeatures): ?>
        <button type="button" class="button secondary map-tool" data-tool="Point">نقطه</button>
        <button type="button" class="button secondary map-tool" data-tool="LineString">خط</button>
        <button type="button" class="button secondary map-tool" data-tool="Polygon">محدوده</button>
        <?php endif; ?>
        <button type="button" class="button secondary map-tool" data-tool="measure">طول مسیر</button>
        <button type="button" class="button secondary map-tool" data-tool="measure-area">مساحت</button>
      </div>
      <div class="offline-map-tool-group">
        <button type="button" class="button secondary" id="offline-map-undo" disabled>بازگشت رأس</button>
        <button type="button" class="button secondary" id="offline-map-finish" disabled>پایان ترسیم</button>
        <button type="button" class="button secondary" id="offline-map-cancel" disabled>لغو</button>
        <button type="button" class="button secondary" id="offline-map-export">خروجی GeoJSON</button>
        <button type="button" class="button secondary" id="offline-map-print">چاپ</button>
      </div>
    </div>
    <div class="offline-map-canvas-inner">
      <div id="offline-map-map" data-map-ready="<?= $mapReady ? '1' : '0' ?>" data-can-edit="<?= $canManageFeatures ? '1' : '0' ?>" data-can-admin="<?= user_can($user, 'map.admin') ? '1' : '0' ?>" data-user-id="<?= (int) $user['id'] ?>" data-official-boundaries="<?= $officialBoundariesReady ? '1' : '0' ?>" data-municipal-districts="<?= $municipalDistrictsReady ? '1' : '0' ?>" data-transit-overlay="<?= $transitOverlayReady ? '1' : '0' ?>" aria-label="نقشهٔ تعاملی آفلاین ایران">
      <?php if (!$mapReady): ?><div class="offline-map-empty"><span class="offline-map-globe">◎</span><strong>محیط نقشهٔ ایران</strong><span>پس از نصب بستهٔ داده فعال می‌شود.</span></div><?php endif; ?>
      </div>
      <div class="offline-map-compass" aria-hidden="true">N</div>
      <div class="offline-map-scale" id="offline-map-scale">—</div>
      <div class="offline-map-coordinate" id="offline-map-coordinate">مختصات: —</div>
    </div>
    <div class="offline-map-footer">
      <span id="offline-map-statusline" aria-live="polite"><?= $mapReady ? 'نقشه آمادهٔ بارگذاری است.' : 'نمایش نقشه در انتظار نصب بسته است.' ?></span>
      <span>© OpenStreetMap contributors</span>
    </div>
  </main>
  <aside class="offline-map-panel">
    <section class="card offline-map-panel-section">
      <h2>جست‌وجوی نقشه</h2>
      <form id="offline-map-place-search" class="offline-map-search">
        <label for="offline-map-search-input">خیابان، کوچه، محله، شهر یا مکان</label>
        <div class="offline-map-search-row"><input id="offline-map-search-input" name="q" type="search" minlength="2" maxlength="100" placeholder="مثلاً خیابان ولیعصر" autocomplete="off"><button class="button" type="submit">جست‌وجو</button></div>
      </form>
      <div id="offline-map-search-results" class="offline-map-search-results" aria-live="polite"><p class="muted"><?= $searchReady ? 'عبارت موردنظر را وارد کنید.' : 'شاخص جست‌وجوی آفلاین هنوز نصب نشده است.' ?></p></div>
    </section>
    <section class="card offline-map-panel-section">
      <h2>لایه‌ها</h2>
      <div class="offline-map-layer-list">
        <label><input type="checkbox" data-layer-toggle="base" checked disabled> نقشهٔ پایه</label>
        <label><input type="checkbox" data-layer-toggle="user" checked> عوارض سازمانی</label>
        <label><input type="checkbox" data-layer-toggle="roads" checked> معابر</label>
        <label><input type="checkbox" data-layer-toggle="places" checked> مکان‌ها و خدمات</label>
        <label><input type="checkbox" data-layer-toggle="boundaries" checked> مرزهای اداری پایه</label>
        <label><input type="checkbox" data-layer-toggle="official-boundaries" checked> مرزهای رسمی</label>
        <label><input type="checkbox" data-layer-toggle="districts" checked> مناطق شهرداری</label>
        <label><input type="checkbox" data-layer-toggle="transport" checked> حمل‌ونقل عمومی</label>
        <label class="offline-map-opacity-control">شفافیت مناطق <input id="offline-map-district-opacity" type="range" min="5" max="80" value="22"></label>
      </div>
      <p class="muted small">فعال‌سازی هر لایهٔ پایه به وجود دادهٔ متناظر در بستهٔ نقشه وابسته است.</p>
    </section>
    <section class="card offline-map-panel-section">
      <div class="offline-map-section-heading"><h2>عوارض سازمانی</h2><span><?= count($features) ?> مورد</span></div>
      <div class="offline-map-feature-filters">
        <label><input type="checkbox" id="offline-map-only-mine"> فقط موارد من</label>
      </div>
      <div id="offline-map-feature-list" class="offline-map-feature-list">
      <?php if (!$schemaReady): ?><p class="muted">پایگاه‌داده نصب نشده است.</p>
      <?php elseif (!$features): ?><p class="muted">هنوز عارضه‌ای ثبت نشده است.</p>
      <?php else: foreach ($features as $feature): ?>
        <button type="button" class="offline-map-feature-item" data-feature-focus="<?= (int) $feature['id'] ?>">
          <span class="offline-map-feature-symbol type-<?= e(strtolower((string) $feature['feature_type'])) ?>"></span>
          <span><strong><?= e($feature['title']) ?></strong><small><?= e((string) $feature['feature_type']) ?> · <?= e((string) $feature['updated_at']) ?></small></span>
        </button>
      <?php endforeach; endif; ?>
      </div>
    </section>
  </aside>
</div>
<?php if ($canManageFeatures): ?>
<dialog id="offline-map-feature-dialog" class="offline-map-dialog">
  <form id="offline-map-feature-form" method="dialog">
    <input type="hidden" name="id" value="0"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
    <h2 id="offline-map-dialog-title">ثبت عارضهٔ جدید</h2>
    <label>عنوان <input name="title" maxlength="190" required></label>
    <label>توضیحات <textarea name="description" rows="3" maxlength="4000"></textarea></label>
    <label>دسته‌بندی
      <select name="category"><option value="general">عمومی</option><option value="office">اداری</option><option value="facility">خدماتی</option><option value="incident">رخداد</option><option value="asset">دارایی</option><option value="restricted">محدودهٔ محدود</option></select>
    </label>
    <label>نشانی یا توضیح مکانی <input name="address" maxlength="500"></label>
    <label>شماره تماس <input name="phone" maxlength="60"></label>
    <label>پیوست‌ها (PDF یا تصویر، حداکثر ۱۰ مگابایت برای هر فایل)<input type="file" name="files[]" accept=".pdf,.png,.jpg,.jpeg,.webp,application/pdf,image/png,image/jpeg,image/webp" multiple></label>
    <div id="offline-map-attachments" class="offline-map-attachments"></div>
    <div class="offline-map-dialog-actions"><button type="button" class="button secondary" data-dialog-close>انصراف</button><button type="submit" class="button">ذخیره</button></div>
    <p id="offline-map-form-feedback" aria-live="polite"></p>
  </form>
</dialog>
<?php endif; ?>
<?php if ($mapReady): ?><script src="modules/offline-map/vendor/maplibre-gl.js" defer></script><script src="modules/offline-map/vendor/pmtiles.js" defer></script><?php endif; ?>
<script src="modules/offline-map/Assets/offline-map.js?v=3" defer></script>
<?php render_footer(); ?>
