<?php
/**
 * ۱.۶۳.۴ — گروه‌بندی گزارش‌ها بر اساس معاونت.
 *
 * گزارش‌های غذا (تجمیعی، تحویلی، سفارش‌های امروز، غایبین نهار) هر کدام فهرستی از
 * افراد را برمی‌گردانند. کاربر خواسته این فهرست‌ها را می‌توان «بر اساس معاونت»
 * دید: هر فرد زیر سرشاخهٔ سازمانی خودش (معاونت) بیاید، و همهٔ افراد آن معاونت
 * — مدیر اداره و کارشناس‌ها — زیر همان سرشاخه فهرست شوند. فقط سرشاخه به‌عنوان
 * «سرگروه» چاپ می‌شود، نه ادارهٔ میانی.
 *
 * شش بخش سرشاخه وجود دارد و هر شش باید صریحاً در گزارش نام‌گذاری شوند:
 *   • مدیرعامل          (unit_type = ceo)
 *   • واحدهای مستقل (نمایندگی)  (unit_type = liaison)
 *   • معاونت            (unit_type = deputy)
 *   • مدیریت مستقل      (unit_type = management، والدش مدیرعامل است)
 *   • دفتر مدیرعامل     (unit_type = office، فرزند ریشهٔ شرکت است)
 *   • شرکت همکار        (company_id = 2 — فقط یک ردیف؛ زیربخش‌هایش تفکیک نمی‌شود)
 *
 * این فایل هیچ نوشتنی در پایگاه‌داده ندارد و فقط می‌خواند؛ اگر جدول‌های چارت
 * سازمانی موجود نباشند همه‌چیز به «بدون جایگاه» برمی‌گردد تا گزارش‌ها هرگز
 * به‌خاطر چارت خالی نشوند.
 */

if (!function_exists('org_group_company_roots')) {
    /**
     * سرشاخه‌های سازمان‌های دیگر (غیر شرکت اصلی). کلید = company_id، مقدار = برچسب.
     * شرکت با company_type = partner «شرکت همکار» نام می‌گیرد.
     */
    function org_group_company_roots(): array
    {
        $roots = [];
        if (!function_exists('org_companies_all') || !function_exists('org_has_company_schema') || !org_has_company_schema()) {
            return $roots;
        }
        foreach (org_companies_all() as $company) {
            $id = (int) ($company['id'] ?? 0);
            if ($id <= 0 || $id === 1) {
                continue; // شرکت اصلی با ساختار معاونت‌ها نمایش داده می‌شود.
            }
            $roots[$id] = [
                'key'      => 'company-' . $id,
                'label'    => (string) ($company['name'] ?? ('شرکت ' . $id)),
                'type'     => 'company',
                'unit_id'  => 0,
                'company'  => $id,
            ];
        }
        return $roots;
    }
}

if (!function_exists('org_group_unit_index')) {
    /**
     * ایندکس همهٔ واحدهای فعال: id ⇒ [id, name, parent_id, unit_type, company_id].
     * یک کوئری برای کل گزارش، نه یکی برای هر فرد.
     */
    function org_group_unit_index(): array
    {
        static $index = null;
        if ($index !== null) {
            return $index;
        }
        $index = [];
        if (!function_exists('db_table_exists') || !db_table_exists('org_units')) {
            return $index;
        }
        try {
            $hasCompany = function_exists('org_has_company_schema') && org_has_company_schema();
            $columns = 'id, name, parent_id, unit_type, is_active' . ($hasCompany ? ', company_id' : '');
            $rows = db()->query('SELECT ' . $columns . ' FROM org_units WHERE is_active = 1')->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as $row) {
                $id = (int) ($row['id'] ?? 0);
                if ($id <= 0) {
                    continue;
                }
                $index[$id] = [
                    'id'         => $id,
                    'name'       => (string) ($row['name'] ?? ''),
                    'parent_id'  => (int) ($row['parent_id'] ?? 0),
                    'unit_type'  => (string) ($row['unit_type'] ?? 'department'),
                    'company_id' => $hasCompany ? (int) ($row['company_id'] ?? 1) : 1,
                ];
            }
        } catch (Throwable $e) {
            $index = [];
        }
        return $index;
    }
}

if (!function_exists('org_group_for_unit')) {
    /**
     * از یک واحد شروع می‌کند و تا رسیدن به سرشاخه بالا می‌رود.
     *
     * قاعده: اگر در مسیر به «معاونت» برسیم، همان معاونت سرشاخه است و بالاتر
     * نمی‌رویم. «مدیریت مستقل» فقط وقتی سرشاخه است که معاونتِ بالاسری نداشته
     * باشد (یعنی مستقل واقعی). «واحد مستقل (نمایندگی)» همیشه خودش سرشاخه است.
     *
     * @return array{key:string,label:string,type:string,unit_id:int,company:int,path:string}
     */
    function org_group_for_unit(int $unitId, ?array $index = null, ?array $companyRoots = null): array
    {
        $index = $index ?? org_group_unit_index();
        $companyRoots = $companyRoots ?? org_group_company_roots();
        $fallback = ['key' => 'none', 'label' => 'بدون جایگاه', 'type' => 'none', 'unit_id' => 0, 'company' => 1, 'path' => ''];

        $current = $unitId;
        $chain = [];
        $guard = 0;
        while ($current > 0 && isset($index[$current]) && $guard++ < 20) {
            $unit = $index[$current];
            $chain[] = $unit;
            // هر واحدی که به شرکت دوم تعلق دارد، زیر «شرکت دوم» گزارش می‌شود —
            // نه زیر معاونت/مدیریت خودش — چون ستون‌های ستادی دو شرکت جدا هستند.
            if (isset($companyRoots[$unit['company_id']])) {
                $root = $companyRoots[$unit['company_id']];
                $root['path'] = org_group_chain_path($chain);
                return $root;
            }
            // معاونت در هر عمقی از مسیر: همان سرشاخه است.
            if ($unit['unit_type'] === 'deputy') {
                return [
                    'key'      => 'unit-' . $unit['id'],
                    'label'    => $unit['name'],
                    'type'     => 'deputy',
                    'unit_id'  => $unit['id'],
                    'company'  => $unit['company_id'],
                    'path'     => org_group_chain_path($chain),
                ];
            }
            // مدیریت مستقل: چون در هر گام ابتدا «معاونت» بررسی می‌شود، رسیدن به
            // یک management در مسیر یعنی معاونتی بالاسرش نیست و خودش مستقل است.
            if ($unit['unit_type'] === 'management') {
                return [
                    'key'      => 'unit-' . $unit['id'],
                    'label'    => $unit['name'],
                    'type'     => 'management',
                    'unit_id'  => $unit['id'],
                    'company'  => $unit['company_id'],
                    'path'     => org_group_chain_path($chain),
                ];
            }
            // واحد مستقل (نمایندگی) همیشه خودش سرشاخه است و بالاتر نمی‌رود.
            if ($unit['unit_type'] === 'liaison') {
                return [
                    'key'      => 'unit-' . $unit['id'],
                    'label'    => $unit['name'],
                    'type'     => 'liaison',
                    'unit_id'  => $unit['id'],
                    'company'  => $unit['company_id'],
                    'path'     => org_group_chain_path($chain),
                ];
            }
            // دفتر مدیرعامل دفتر ریشه است نه معاونت؛ خودش سرشاخه می‌شود تا زیر «مدیرعامل» نرم.
            if ($unit['unit_type'] === 'office') {
                return [
                    'key'      => 'unit-' . $unit['id'],
                    'label'    => $unit['name'],
                    'type'     => 'office',
                    'unit_id'  => $unit['id'],
                    'company'  => $unit['company_id'],
                    'path'     => org_group_chain_path($chain),
                ];
            }
            // رسیدن به ریشهٔ شرکت: اگر شرکت دوم باشد، برچسب همان شرکت را بده.
            if ($unit['parent_id'] <= 0) {
                if (isset($companyRoots[$unit['company_id']])) {
                    $root = $companyRoots[$unit['company_id']];
                    $root['path'] = org_group_chain_path($chain);
                    return $root;
                }
                if ($unit['unit_type'] === 'management') {
                    return [
                        'key'     => 'unit-' . $unit['id'],
                        'label'   => $unit['name'],
                        'type'    => 'management',
                        'unit_id' => $unit['id'],
                        'company' => $unit['company_id'],
                        'path'    => org_group_chain_path($chain),
                    ];
                }
                if ($unit['unit_type'] === 'ceo') {
                    return [
                        'key'     => 'ceo',
                        'label'   => 'مدیرعامل',
                        'type'    => 'ceo',
                        'unit_id' => $unit['id'],
                        'company' => $unit['company_id'],
                        'path'    => org_group_chain_path($chain),
                    ];
                }
                return $fallback;
            }
            $current = $unit['parent_id'];
        }
        // به هیچ سرشاخه‌ای نرسیدیم: واحد بالاترین سطحِ موجود سرشاخه می‌شود تا فرد
        // از دست گزارش نیفتد (شرط اصلی: هیچ فردی حذف نشود).
        if ($chain) {
            $top = $chain[count($chain) - 1];
            return [
                'key'     => 'unit-' . $top['id'],
                'label'   => $top['name'],
                'type'    => $top['unit_type'],
                'unit_id' => $top['id'],
                'company' => $top['company_id'],
                'path'    => org_group_chain_path($chain),
            ];
        }
        return $fallback;
    }
}

if (!function_exists('org_group_chain_path')) {
    /** مسیر خوانا از واحد خودِ فرد تا سرشاخه، برای راهنمای زیر نام. */
    function org_group_chain_path(array $chain): string
    {
        $names = [];
        foreach ($chain as $unit) {
            $name = (string) $unit['name'];
            // «مدیر شرکت همکار» و «مدیریت شرکت همکار» نباید پشت هم تکرار شوند.
            if ($name !== '' && ($names === [] || end($names) !== $name)) {
                $names[] = $name;
            }
        }
        return implode(' ← ', $names);
    }
}

if (!function_exists('org_group_meta_for_users')) {
    /**
     * برای فهرستی از شناسهٔ کاربر، «معاونت» هر نفر را برمی‌گرداند.
     * یک کوئری برای کاربران + یک کوئری برای واحدها (کش‌شده) — نه N+1.
     *
     * @param  array<int,int|string> $userIds
     * @return array<int,array{key:string,label:string,type:string,unit_id:int,company:int,path:string,unit_name:string}>
     */
    function org_group_meta_for_users(array $userIds): array
    {
        $ids = [];
        foreach ($userIds as $id) {
            $id = (int) $id;
            if ($id > 0) {
                $ids[$id] = true;
            }
        }
        if (!$ids) {
            return [];
        }
        $unitOf = [];
        $companyOf = [];
        try {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $stmt = db()->prepare('SELECT id, org_unit_id, company_id FROM users WHERE id IN (' . $placeholders . ')');
            $stmt->execute(array_keys($ids));
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $uid = (int) ($row['id'] ?? 0);
                $unitOf[$uid] = (int) ($row['org_unit_id'] ?? 0);
                $companyOf[$uid] = (int) ($row['company_id'] ?? 1);
            }
        } catch (Throwable $e) {
            return [];
        }
        $index = org_group_unit_index();
        $companyRoots = org_group_company_roots();
        $meta = [];
        foreach ($unitOf as $uid => $unitId) {
            $group = org_group_for_unit($unitId, $index, $companyRoots);
            // کاربر بدون واحد ولی متعلق به شرکت دوم: همان شرکت سرشاخه است.
            if ($unitId <= 0 && isset($companyRoots[$companyOf[$uid] ?? 1])) {
                $group = $companyRoots[$companyOf[$uid]];
                $group['path'] = '';
            }
            $group['unit_name'] = $unitId > 0 && isset($index[$unitId]) ? $index[$unitId]['name'] : '';
            $meta[$uid] = $group;
        }
        return $meta;
    }
}

if (!function_exists('org_group_sort_key')) {
    /** ترتیب نمایش سرشاخه‌ها: مدیرعامل → مستقل‌ها → معاونت‌ها → مدیریت مستقل → دفتر مدیرعامل → شرکت همکار → بدون جایگاه. */
    function org_group_sort_key(array $group): array
    {
        $order = ['ceo' => 0, 'liaison' => 1, 'deputy' => 2, 'management' => 3, 'office' => 4, 'company' => 5, 'none' => 6, 'department' => 6];
        return [$order[$group['type'] ?? 'none'] ?? 9, (string) ($group['label'] ?? '')];
    }
}

if (!function_exists('org_group_type_label')) {
    /** برچسب فارسی نوع سرشاخه — کنار نام معاونت چاپ می‌شود. */
    function org_group_type_label(string $type): string
    {
        return match ($type) {
            'deputy'     => 'معاونت',
            'management' => 'مدیریت مستقل',
            'liaison'    => 'واحد مستقل (نمایندگی)',
            'office'     => 'دفتر مدیرعامل',
            'company'    => 'شرکت همکار',
            'ceo'        => 'مدیرعامل',
            default      => 'بدون جایگاه',
        };
    }
}
