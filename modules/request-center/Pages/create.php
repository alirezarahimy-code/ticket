<?php
declare(strict_types=1);

// محافظ: این فایل فقط از طریق نقاط ورود معتبر (index.php هسته یا request-center.php) بارگذاری می‌شود؛ دسترسی مستقیم قطعاً ممنوع.
if (!defined('APP_ROOT')) {
    http_response_code(404);
    exit;
}

/**
 * Request Center requester workspace: create-request page.
 *
 * This page intentionally executes in the legacy front-controller scope while
 * its ownership moves into the module. Existing helpers, permission checks,
 * URLs, form fields and rendering behavior are preserved verbatim.
 */

if ($page === 'new-ticket') {
    $user = require_permission('ticket.create');
    if (defined('REQUEST_CENTER_INDEPENDENT_ENTRY')) {
        request_center_assert_create_schema();
    }
    $cdDvdRequestRecord = null;
    $retryDraft = is_array($_SESSION['rc_create_retry'] ?? null) ? $_SESSION['rc_create_retry'] : [];
    if ($retryDraft !== [] && (int) ($retryDraft['saved_at'] ?? 0) < time() - 1800) {
        $retryDraft = [];
    }
    // Consume the draft on page render so it cannot unexpectedly reappear in a
    // later, unrelated request; the user can still submit this rendered form.
    unset($_SESSION['rc_create_retry']);
    $subjectDefault = (string) ($retryDraft['subject'] ?? '');
    $descriptionDefault = (string) ($retryDraft['description'] ?? '');
    if (($_GET['request'] ?? '') === 'cd_dvd_edit') {
        $candidate = cd_dvd_fetch_record((int) ($_GET['record_id'] ?? 0));
        if ($candidate && !cd_dvd_can_edit_records($user) && cd_dvd_can_view_record($candidate, $user)) {
            $cdDvdRequestRecord = $candidate;
            $subjectDefault = 'درخواست اصلاح رکورد CD/DVD ' . $candidate['media'] . ' ' . $candidate['serial'];
            $descriptionDefault = "لطفاً رکورد CD/DVD زیر را بررسی و در صورت تأیید اصلاح کنید.\n\n"
                . 'شناسه رکورد: ' . $candidate['id'] . "\n"
                . 'رسانه: ' . $candidate['media'] . "\n"
                . 'شماره: ' . $candidate['serial'] . "\n"
                . 'جهت: ' . ($candidate['direction'] === 'IN' ? 'ورود' : 'خروج') . "\n"
                . 'تاریخ: ' . $candidate['date_str'] . "\n"
                . 'شرح فعلی: ' . $candidate['info_desc'] . "\n\n"
                . 'شرح اصلاح موردنیاز: ';
        }
    }
    if ($cdDvdRequestRecord === null && $retryDraft === []) {
        // پیش‌پرکردن متن از آریا؛ در صورت وجود پیش‌نویس بازیابی‌شده، متن ارسالی کاربر اولویت دارد.
        $subjectDefault = mb_substr(trim((string) ($_GET['subject'] ?? '')), 0, 255, 'UTF-8');
        $descriptionDefault = mb_substr(trim((string) ($_GET['description'] ?? '')), 0, 5000, 'UTF-8');
    }
    $categories = db()->query('SELECT * FROM categories WHERE is_active = 1 ORDER BY service_group, name')->fetchAll();
    $clientIp = inventory_client_ip();
    $limitedAssetScope = !is_staff_role((string) $user['role']) && !is_it_agent($user);
    if ($limitedAssetScope) {
        // برای کاربر عادی فقط سیستم‌های متعلق به خودش یا همین کلاینت را حتی در HTML هم بارگذاری کن.
        $assetQuery = db()->prepare('SELECT id, asset_tag, hostname, owner_user_id, ip_address FROM assets WHERE owner_user_id = ? OR (? <> "" AND ip_address = ?) ORDER BY hostname');
        $assetQuery->execute([(int) $user['id'], $clientIp, $clientIp]);
        $allAssets = $assetQuery->fetchAll();
    } else {
        [$assetWhere, $assetParams] = asset_scope($user, 'a');
        $assetQuery = db()->prepare('SELECT a.id, a.asset_tag, a.hostname, a.owner_user_id, a.ip_address FROM assets a ' . ($assetWhere !== '' ? $assetWhere : '') . ' ORDER BY a.hostname');
        $assetQuery->execute($assetParams);
        $allAssets = $assetQuery->fetchAll();
    }
    $clientAssetId = 0;
    $ownAssets = [];
    $otherAssets = [];
    foreach ($allAssets as $assetRow) {
        $isOwner = (int) $assetRow['owner_user_id'] === (int) $user['id'];
        $isHere = $clientIp !== '' && trim((string) $assetRow['ip_address']) === $clientIp;
        if ($isHere && !$clientAssetId) { $clientAssetId = (int) $assetRow['id']; }
        if ($isOwner || $isHere) { $ownAssets[] = $assetRow; } else { $otherAssets[] = $assetRow; }
    }
    $userAssets = array_merge($ownAssets, $otherAssets);
    $autoAssetId = $clientAssetId ?: (count($userAssets) === 1 ? (int) $userAssets[0]['id'] : 0);
    $departments = [];
    // معاونت‌های سازمانی از چارت سازمانی (org_units) خوانده می‌شوند و برای همهٔ نقش‌ها
    // نمایش داده می‌شوند تا درخواست به معاونت انتخاب‌شده مسیردهی شود.
    if (defined('REQUEST_CENTER_INDEPENDENT_ENTRY')) {
        $departments = request_center_org_deputy_units($user);
    } else {
        org_ensure_schema();
        org_ticket_schema_ensure();
        $departments = org_deputy_units();
        ticket_service_ensure_schema();
    }
    $services = db()->query('SELECT id, name, code, service_group, requires_asset, default_priority, default_ticket_type, category_id FROM service_catalog WHERE is_active = 1 ORDER BY service_group, name')->fetchAll();
    $cdDvdServiceId = 0;
    if ($cdDvdRequestRecord) {
        $cdDvdServiceQuery = db()->prepare("SELECT id FROM service_catalog WHERE code = 'SUP-CDDVD' AND is_active = 1 LIMIT 1");
        $cdDvdServiceQuery->execute();
        $cdDvdServiceId = (int) ($cdDvdServiceQuery->fetchColumn() ?: 0);
    }
    $serviceFields = [];
    if ($services) {
        $serviceFieldQuery = db()->prepare('SELECT * FROM service_catalog_fields WHERE service_id = ? ORDER BY sort_order, id');
        foreach ($services as $service) {
            $serviceFieldQuery->execute([(int) $service['id']]);
            $serviceFields[(int) $service['id']] = $serviceFieldQuery->fetchAll();
        }
    }
    // ۱.۳۷.۲ (درخواست کاربر): فرم ثبت درخواست همیشه با حوزهٔ «خدمات کامپیوتری و IT» باز می‌شود.
    // تنها مسیر CD/DVD (که رسیدگی‌اش در پشتیبانی است) روی «پشتیبانی» می‌ماند.
    $ticketDefaultGroup = $cdDvdRequestRecord ? 'support' : 'it';
    $creationToken = '';
    if (session_status() === PHP_SESSION_ACTIVE) {
        $_SESSION['rc_create_tokens'] = is_array($_SESSION['rc_create_tokens'] ?? null)
            ? $_SESSION['rc_create_tokens']
            : [];
        foreach ($_SESSION['rc_create_tokens'] as $issuedToken => $issuedAt) {
            if (!is_string($issuedToken) || (int) $issuedAt < time() - 1800) {
                unset($_SESSION['rc_create_tokens'][$issuedToken]);
            }
        }
        $creationToken = bin2hex(random_bytes(32));
        $_SESSION['rc_create_tokens'][$creationToken] = time();
        // Keep the session bounded even if a user opens many create tabs.
        if (count($_SESSION['rc_create_tokens']) > 12) {
            asort($_SESSION['rc_create_tokens']);
            $_SESSION['rc_create_tokens'] = array_slice($_SESSION['rc_create_tokens'], -12, null, true);
        }
    }
    if ($creationToken === '') {
        http_response_code(503);
        exit('نشست کاربری برای صدور کلید ثبت امن در دسترس نیست؛ صفحه را دوباره باز کنید.');
    }
    render_header('ثبت درخواست', $user);
    echo '<section class="page-heading"><div><span class="eyebrow">درخواست جدید</span><h1>ثبت درخواست</h1><p>ابتدا حوزه خدمت را انتخاب کنید تا فرم و مسیر رسیدگی درست نمایش داده شود.</p></div><a class="button secondary" href="request-center.php">بازگشت به مرکز درخواست‌ها</a></section><section class="card form-card"><form method="post" enctype="multipart/form-data">' . csrf_field() . '<input type="hidden" name="action" value="create_ticket"><input type="hidden" name="request_token" value="' . e($creationToken) . '">' . ($cdDvdRequestRecord ? '<input type="hidden" name="cd_dvd_record_id" value="' . (int) $cdDvdRequestRecord['id'] . '">' : '');
    echo '<div class="ticket-number-box" data-next-it="' . e(request_center_ticket_number_peek('IT')) . '" data-next-support="' . e(request_center_ticket_number_peek('SUP')) . '"><span>شماره درخواست:</span><strong id="ticket-number-preview">' . e($ticketDefaultGroup === 'support' ? request_center_ticket_number_peek('SUP') : request_center_ticket_number_peek('IT')) . '</strong></div>';
    echo '<div class="form-grid"><label class="full">موضوع درخواست<input name="subject" required maxlength="255" value="' . e($subjectDefault) . '" placeholder="مثلاً: اختلال در اتصال به پرینتر واحد مالی"></label><label>حوزه خدمت<div class="group-combo"><label class="group-option"><input type="radio" name="service_group" value="it"' . ($ticketDefaultGroup === 'it' ? ' checked' : '') . '><span>خدمات کامپیوتری و IT</span></label><label class="group-option"><input type="radio" name="service_group" value="support"' . ($ticketDefaultGroup === 'support' ? ' checked' : '') . '><span>خدمات پشتیبانی</span></label></div></label><input type="hidden" name="category_id" id="category-selector" value=""><label>خدمت از کاتالوگ<select name="service_id" id="service-selector" required><option value="">انتخاب کنید…</option>';
    foreach ($services as $service) {
        $selected = ($cdDvdServiceId > 0 && (int) $service['id'] === $cdDvdServiceId) ? ' selected' : '';
        echo '<option value="' . (int) $service['id'] . '" data-service-group="' . e($service['service_group']) . '" data-category-id="' . (int) ($service['category_id'] ?? 0) . '" data-requires-asset="' . (int) $service['requires_asset'] . '"' . $selected . '>' . e($service['name']) . '</option>';
    }
    echo '</select><small class="field-help">ابتدا حوزه و سپس خدمت را انتخاب کنید؛ دسته‌بندی، اولویت و مسیر رسیدگی خودکار تنظیم می‌شود.</small></label>';
    if ($departments) {
        $myUnitId = function_exists('user_org_unit_id') ? user_org_unit_id($user) : 0;
        $myUnitIsDeputy = false;
        foreach ($departments as $department) {
            if ((int) $department['id'] === $myUnitId) {
                $myUnitIsDeputy = true;
                break;
            }
        }
        echo '<label>معاونت سازمانی درخواست‌کننده<select name="requesting_unit_id" required><option value="">انتخاب کنید…</option>';
        foreach ($departments as $department) {
            echo '<option value="' . (int) $department['id'] . '" ' . ((int) $department['id'] === $myUnitId ? 'selected' : '') . '>' . e($department['name']) . '</option>';
        }
        echo '</select><small class="field-help">' . ($myUnitIsDeputy ? 'معاونت شما به‌صورت پیش‌فرض انتخاب شده است؛ ' : '') . 'درخواست به معاونت انتخاب‌شده ارجاع داده می‌شود.</small></label>';
    } else {
        echo '<label>معاونت سازمانی درخواست‌کننده<input value="هیچ معاونتی در چارت سازمانی تعریف نشده است" readonly></label>';
    }
     echo '<label>اولویت<select name="priority"><option value="">پیش‌فرض خدمت</option><option value="normal">عادی</option><option value="urgent">فوری</option><option value="critical">حیاتی</option></select></label><label class="ticket-asset-field" data-asset-combo data-asset-required-group="it">سیستم مرتبط <span class="auto-label" data-asset-label>پس از انتخاب خدمت مشخص می‌شود</span><input type="text" id="asset-search" class="asset-search" placeholder="جستجوی سیستم (نام رایانه، شماره اموال یا پلاک)" autocomplete="off" role="combobox" aria-autocomplete="list" aria-expanded="false" aria-controls="asset-results" aria-required="false"><select name="asset_id" id="asset-selector" class="asset-native" tabindex="-1" aria-hidden="true"><option value="">بدون سیستم خاص / انتخاب نشده</option>';
     foreach ($userAssets as $asset) {
          $isCurrent = (int) $asset['id'] === $clientAssetId;
          $tagText = asset_tag_label($asset['asset_tag'], $asset['hostname']);
          if (asset_is_auto_domain_tag($asset['asset_tag'])) { $tagText = ''; }
          echo '<option value="' . (int) $asset['id'] . '" ' . ((int) $asset['id'] === $autoAssetId ? 'selected' : '') . '>' . e(asset_display_name($asset['hostname']) . ($tagText !== '' ? ' - ' . $tagText : '') . ($isCurrent ? ' (سیستم فعلی)' : '')) . '</option>';
     }
       echo '</select><div class="asset-results" id="asset-results" role="listbox" hidden></div><small class="field-help">برای همهٔ خدمات IT، انتخاب سیستم مرتبط از شناسنامهٔ فنی الزامی است. نام رایانه، شمارهٔ اموال یا پلاک را جست‌وجو و از فهرست انتخاب کنید.</small><small class="field-error" data-asset-error hidden></small></label><label class="support-field">محل خدمت / اتاق<span class="auto-label">برای پشتیبانی الزامی</span><input name="support_location" placeholder="ساختمان، طبقه، اتاق یا محل دقیق"></label><label class="support-field">تجهیز یا مورد درخواست<textarea name="support_equipment" rows="2" placeholder="مثلاً صندلی، میز، پرینتر عمومی یا تجهیزات اتاق"></textarea></label>' . ((is_staff_role($user['role']) || is_it_agent($user)) ? '<label>ارجاع به درخواست قبلی (اختیاری، کارشناسان)<input type="number" name="parent_ticket_id" placeholder="شناسه داخلی درخواست"></label>' : '') . '<label class="full">شرح کامل مشکل<textarea name="description" required rows="8" placeholder="چه اتفاقی افتاده؟ از چه زمانی؟ چه پیامی می‌بینید؟">' . e($descriptionDefault) . '</textarea></label>';
      foreach ($serviceFields as $serviceId => $fields) {
          if (!$fields) { continue; }
          echo '<fieldset class="service-fields" data-service-fields="' . $serviceId . '"><legend>اطلاعات تکمیلی خدمت</legend><div class="form-grid">';
          foreach ($fields as $field) {
              $required = (int) $field['is_required'] === 1 ? ' required' : '';
              $name = 'custom[' . e($field['field_key']) . ']';
              $options = json_decode((string) $field['options_json'], true) ?: [];
              if ($field['field_type'] === 'textarea') {
                  echo '<label class="full">' . e($field['label']) . '<textarea name="' . $name . '" rows="3"' . $required . '></textarea></label>';
              } elseif ($field['field_type'] === 'select') {
                  echo '<label>' . e($field['label']) . '<select name="' . $name . '"' . $required . '><option value="">انتخاب کنید</option>';
                  foreach ($options as $option) { echo '<option value="' . e($option) . '">' . e($option) . '</option>'; }
                  echo '</select></label>';
              } else {
                   $inputType = $field['field_type'] === 'number' ? 'number' : 'text';
                   $placeholder = $field['field_type'] === 'date' ? '۱۴۰۵/۰۱/۰۱' : '';
                   echo '<label>' . e($field['label']) . '<input type="' . $inputType . '" name="' . $name . '" placeholder="' . $placeholder . '"' . $required . '></label>';
              }
          }
          echo '</div></fieldset>';
      }
      echo '<label class="full file-input">پیوست اختیاری<input type="file" name="attachment"><small>حداکثر ۸ مگابایت؛ PDF، تصویر، Word، Excel، TXT و ZIP</small></label></div><div class="actions"><button class="button" type="submit">ثبت درخواست</button></div></form></section>';
    render_footer();
    exit;
}
