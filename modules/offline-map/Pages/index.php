<?php
declare(strict_types=1);

/** Offline map workspace; rendered inside the authenticated system shell. */
$user = require_permission('map.view');
require_once __DIR__ . '/../MapRepository.php';
$mapRepo = new OfflineMapRepository(db());
$schemaReady = $mapRepo->ready();
$mapDataPath = __DIR__ . '/../data/iran.pmtiles';
$mapStylePath = __DIR__ . '/../data/style.json';
$mapLibraryPath = __DIR__ . '/../vendor/maplibre-gl.js';
$pmtilesLibraryPath = __DIR__ . '/../vendor/pmtiles.js';
$mapReady = is_file($mapDataPath) && is_file($mapStylePath) && is_file($mapLibraryPath) && is_file($pmtilesLibraryPath);
$features = $schemaReady ? $mapRepo->search(trim((string) ($_GET['q'] ?? ''))) : [];
render_header('نقشهٔ آفلاین ایران', $user);
?>
<link rel="stylesheet" href="modules/offline-map/Assets/offline-map.css?v=1">
<?php if ($mapReady): ?><link rel="stylesheet" href="modules/offline-map/vendor/maplibre-gl.css"><?php endif; ?>
<section class="page-heading offline-map-heading">
  <div><span class="eyebrow">سامانهٔ اطلاعات مکانی</span><h1>نقشهٔ آفلاین ایران</h1><p>نمایش نقشهٔ پایه از بستهٔ محلی و نگهداری مستقل نقاط و اطلاعات ثبت‌شدهٔ سازمان.</p></div>
  <a class="button secondary" href="index.php">بازگشت به داشبورد</a>
</section>
<?php if (!$schemaReady): ?>
<section class="card offline-map-notice"><h2>پایگاه‌دادهٔ ماژول آماده نیست</h2><p>ابتدا فایل <code>migrations/038-offline-map-features.sql</code> را روی نسخهٔ پشتیبان‌شدهٔ پایگاه‌داده اجرا کنید. این صفحه عمداً ساختار پایگاه‌داده را خودکار تغییر نمی‌دهد.</p></section>
<?php endif; ?>
<?php if (!$mapReady): ?>
<section class="card offline-map-notice" role="status"><h2>بستهٔ نقشه هنوز نصب نشده است</h2><p>کتابخانهٔ محلی MapLibre، فایل سبک محلی و بستهٔ PMTiles ایران در مسیر <code>modules/offline-map/data/</code> نصب نشده‌اند. برای جلوگیری از وابستگی اینترنتی، نقشهٔ راه‌دور یا CDN بارگذاری نمی‌شود.</p><p class="muted">پس از آماده‌شدن بستهٔ داده، نقشهٔ پایه در همین بخش فعال می‌شود. ثبت نقاط سازمانی مستقل از بستهٔ نقشه باقی می‌ماند.</p></section>
<?php endif; ?>
<div class="offline-map-layout">
  <section class="card offline-map-canvas" aria-label="محیط نقشه">
    <div class="offline-map-canvas-inner"><div class="offline-map-compass">N</div><div id="offline-map-map" data-map-ready="<?= $mapReady ? '1' : '0' ?>" aria-label="نقشهٔ برداری آفلاین ایران"></div><?php if (!$mapReady): ?><div class="offline-map-empty"><span class="offline-map-globe">◎</span><strong>محیط نقشهٔ ایران</strong><span>در انتظار نصب داده‌های برداری آفلاین</span></div><?php endif; ?></div>
    <div class="offline-map-legend"><span><i></i> نقاط ثبت‌شدهٔ سازمان</span><span>مختصات جغرافیایی WGS84</span></div>
  </section>
  <aside class="card offline-map-sidebar">
    <h2>ثبت نقطهٔ جدید</h2>
    <p class="muted">مختصات را به درجهٔ اعشاری وارد کنید. طول جغرافیایی بین ۴۴ تا ۶۴ و عرض جغرافیایی بین ۲۴ تا ۴۰ برای محدودهٔ تقریبی ایران پذیرفته می‌شود.</p>
    <form id="offline-map-create" class="offline-map-form">
      <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
      <label>عنوان نقطه<input name="title" maxlength="190" required placeholder="مثلاً ساختمان مرکزی"></label>
      <label>توضیحات<textarea name="description" rows="3" maxlength="4000" placeholder="اطلاعات تکمیلی"></textarea></label>
      <div class="offline-map-coordinates"><label>طول جغرافیایی<input name="longitude" type="number" min="44" max="64" step="any" required placeholder="51.3890"></label><label>عرض جغرافیایی<input name="latitude" type="number" min="24" max="40" step="any" required placeholder="35.6892"></label></div>
      <button class="button" type="submit" <?= $schemaReady ? '' : 'disabled' ?>>ثبت نقطه</button>
      <p id="offline-map-feedback" class="muted" aria-live="polite"></p>
    </form>
    <hr>
    <form method="get" class="offline-map-search"><input type="hidden" name="page" value="offline-map"><label>جست‌وجوی نقاط ثبت‌شده<input name="q" value="<?= e((string) ($_GET['q'] ?? '')) ?>" placeholder="عنوان یا توضیحات"></label><button class="button secondary" type="submit">جست‌وجو</button></form>
  </aside>
</div>
<section class="list-section offline-map-feature-list"><div class="section-title"><div><h2>نقاط ثبت‌شده</h2><p><?= count($features) ?> نقطه در فهرست فعلی</p></div></div>
<?php if (!$schemaReady): ?><div class="card empty-state">پس از اجرای مهاجرت، فهرست نقاط در این قسمت نمایش داده می‌شود.</div>
<?php elseif (!$features): ?><div class="card empty-state">هنوز نقطه‌ای ثبت نشده است.</div>
<?php else: ?><div class="card offline-map-feature-table"><?php foreach ($features as $feature): ?><article class="offline-map-feature" data-feature-id="<?= (int) $feature['id'] ?>"><div><strong><?= e($feature['title']) ?></strong><p><?= e((string) ($feature['description'] ?? '')) ?></p><small><?= e(number_format((float) $feature['geometry']['coordinates'][1], 6)) ?> ، <?= e(number_format((float) $feature['geometry']['coordinates'][0], 6)) ?></small></div><?php if ((int) $feature['created_by'] === (int) $user['id'] || user_can($user, 'map.admin')): ?><button type="button" class="button danger small" data-delete-feature="<?= (int) $feature['id'] ?>">حذف</button><?php endif; ?></article><?php endforeach; ?></div><?php endif; ?>
</section>
<?php if ($mapReady): ?><script src="modules/offline-map/vendor/maplibre-gl.js" defer></script><script src="modules/offline-map/vendor/pmtiles.js" defer></script><?php endif; ?>
<script src="modules/offline-map/Assets/offline-map.js?v=2" defer></script>
<?php render_footer(); ?>
