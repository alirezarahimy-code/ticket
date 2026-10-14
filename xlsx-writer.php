<?php
declare(strict_types=1);

/**
 * نوشتن فایل XLSX واقعی (Office Open XML) بدون کتابخانهٔ خارجی.
 * فقط از ZipArchive استفاده می‌کند که در PHP و XAMPP موجود است.
 *
 * ورودی هر شیت:
 *   name   : نام شیت (حداکثر ۳۱ نویسه، بدون []:*?/\)
 *   rows   : آرایهٔ ردیف‌ها؛ هر سلول یک مقدار اسکالر یا ['v' => مقدار, 's' => 'title'|'header'|'body']
 *   merges : مثل ['A1:D1']
 *   widths : عرض ستون‌ها بر حسب واحد اکسل، مثل [18, 30]
 *
 * متن‌ها همیشه به‌صورت رشته ذخیره می‌شوند؛ پس فرمول‌ها اجرا نمی‌شوند.
 */

function xlsx_col_name(int $index): string
{
    $name = '';
    $n = $index + 1;
    while ($n > 0) {
        $mod = ($n - 1) % 26;
        $name = chr(65 + $mod) . $name;
        $n = intdiv($n - 1, 26);
    }
    return $name;
}

function xlsx_clean_text(string $text): string
{
    $clean = preg_replace('/[^\x{9}\x{A}\x{D}\x{20}-\x{D7FF}\x{E000}-\x{FFFD}\x{10000}-\x{10FFFF}]/u', '', $text);
    return $clean ?? '';
}

function xlsx_sheet_name(string $name): string
{
    $name = trim(preg_replace('/[\[\]:*?\/\\\\]/u', ' ', $name) ?? '');
    if ($name === '') {
        $name = 'Sheet1';
    }
    return mb_substr($name, 0, 31, 'UTF-8');
}

/**
 * @param array<int,array{name?:string,rows:array,merges?:array,widths?:array}> $sheets
 */
function xlsx_build(array $sheets): string
{
    if ($sheets === [] || !class_exists('ZipArchive')) {
        throw new RuntimeException('ساخت فایل Excel در این سرور ممکن نیست.');
    }
    $tmp = tempnam(sys_get_temp_dir(), 'xlsx');
    if ($tmp === false) {
        throw new RuntimeException('فایل موقت برای ساخت Excel ساخته نشد.');
    }
    $zip = new ZipArchive();
    if ($zip->open($tmp, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        @unlink($tmp);
        throw new RuntimeException('فایل Excel ساخته نشد.');
    }

    $sheetEntries = [];
    $sheetRels = [];
    $index = 0;
    foreach ($sheets as $sheet) {
        $index++;
        $sheetEntries[] = ['name' => xlsx_sheet_name((string) ($sheet['name'] ?? 'Sheet' . $index)), 'id' => $index];
        $zip->addFromString('xl/worksheets/sheet' . $index . '.xml', xlsx_sheet_xml($sheet));
        $sheetRels[] = '<Relationship Id="rId' . $index . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet' . $index . '.xml"/>';
    }
    $styleRelId = count($sheets) + 1;

    $sheetList = '';
    foreach ($sheetEntries as $entry) {
        $sheetList .= '<sheet name="' . xlsx_xml($entry['name']) . '" sheetId="' . $entry['id'] . '" r:id="rId' . $entry['id'] . '"/>';
    }
    $zip->addFromString('xl/workbook.xml',
        '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
        '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">' .
        '<bookViews><workbookView/></bookViews><sheets>' . $sheetList . '</sheets></workbook>');

    $zip->addFromString('xl/_rels/workbook.xml.rels',
        '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
        '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' .
        implode('', $sheetRels) .
        '<Relationship Id="rId' . $styleRelId . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>' .
        '</Relationships>');

    $overrides = '';
    foreach ($sheetEntries as $entry) {
        $overrides .= '<Override PartName="/xl/worksheets/sheet' . $entry['id'] . '.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>';
    }
    $zip->addFromString('[Content_Types].xml',
        '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
        '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">' .
        '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>' .
        '<Default Extension="xml" ContentType="application/xml"/>' .
        '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>' .
        $overrides .
        '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>' .
        '</Types>');

    $zip->addFromString('_rels/.rels',
        '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
        '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' .
        '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>' .
        '</Relationships>');

    // ۰: متن ساده  ۱: سرستون (پررنگ، کادر، وسط‌چین)  ۲: عنوان (پررنگ‌تر، وسط‌چین)  ۳: متن بدنه (کادر، راست‌چین)
    $zip->addFromString('xl/styles.xml',
        '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
        '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">' .
        '<fonts count="3">' .
        '<font><sz val="11"/><name val="Tahoma"/></font>' .
        '<font><b/><sz val="11"/><name val="Tahoma"/></font>' .
        '<font><b/><sz val="14"/><name val="Tahoma"/></font>' .
        '</fonts>' .
        '<fills count="3"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill>' .
        '<fill><patternFill patternType="solid"><fgColor rgb="FFE8F3EE"/><bgColor indexed="64"/></patternFill></fill></fills>' .
        '<borders count="2"><border><left/><right/><top/><bottom/><diagonal/></border>' .
        '<border><left style="thin"><color rgb="FFBFCFC8"/></left><right style="thin"><color rgb="FFBFCFC8"/></right>' .
        '<top style="thin"><color rgb="FFBFCFC8"/></top><bottom style="thin"><color rgb="FFBFCFC8"/></bottom><diagonal/></border></borders>' .
        '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>' .
        '<cellXfs count="4">' .
        '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>' .
        '<xf numFmtId="0" fontId="1" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>' .
        '<xf numFmtId="0" fontId="2" fillId="0" borderId="0" xfId="0" applyFont="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>' .
        '<xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1" applyAlignment="1"><alignment horizontal="right" vertical="center" wrapText="1"/></xf>' .
        '</cellXfs>' .
        '<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>' .
        '</styleSheet>');

    $zip->close();
    $bytes = (string) file_get_contents($tmp);
    @unlink($tmp);
    return $bytes;
}

function xlsx_xml(string $text): string
{
    return htmlspecialchars(xlsx_clean_text($text), ENT_XML1 | ENT_QUOTES, 'UTF-8');
}

function xlsx_sheet_xml(array $sheet): string
{
    $styleIds = ['' => 0, 'body' => 3, 'header' => 1, 'title' => 2];
    $rowsXml = '';
    $maxCols = 1;
    $rowNumber = 0;
    foreach (($sheet['rows'] ?? []) as $row) {
        $rowNumber++;
        $cells = '';
        $col = 0;
        foreach ((array) $row as $cell) {
            $value = $cell;
            $style = '';
            if (is_array($cell)) {
                $value = $cell['v'] ?? '';
                $style = (string) ($cell['s'] ?? '');
            }
            $ref = xlsx_col_name($col) . $rowNumber;
            $styleAttr = ' s="' . ($styleIds[$style] ?? 0) . '"';
            if (is_int($value) || is_float($value)) {
                $cells .= '<c r="' . $ref . '"' . $styleAttr . '><v>' . $value . '</v></c>';
            } elseif ($value === null || $value === '') {
                $cells .= '<c r="' . $ref . '"' . $styleAttr . '/>';
            } else {
                $text = (string) $value;
                $cells .= '<c r="' . $ref . '"' . $styleAttr . ' t="inlineStr"><is><t xml:space="preserve">' . xlsx_xml($text) . '</t></is></c>';
            }
            $col++;
        }
        $maxCols = max($maxCols, $col);
        $rowsXml .= '<row r="' . $rowNumber . '">' . $cells . '</row>';
    }
    $lastRef = xlsx_col_name($maxCols - 1) . max(1, $rowNumber);

    $colsXml = '';
    foreach (($sheet['widths'] ?? []) as $i => $width) {
        $n = $i + 1;
        $colsXml .= '<col min="' . $n . '" max="' . $n . '" width="' . (float) $width . '" customWidth="1"/>';
    }
    if ($colsXml !== '') {
        $colsXml = '<cols>' . $colsXml . '</cols>';
    }

    $merges = '';
    $mergeList = array_values(array_filter((array) ($sheet['merges'] ?? []), static fn ($m): bool => is_string($m) && preg_match('/^[A-Z]+\d+:[A-Z]+\d+$/', $m) === 1));
    if ($mergeList !== []) {
        $merges = '<mergeCells count="' . count($mergeList) . '">';
        foreach ($mergeList as $ref) {
            $merges .= '<mergeCell ref="' . $ref . '"/>';
        }
        $merges .= '</mergeCells>';
    }

    return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
        '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">' .
        '<dimension ref="A1:' . $lastRef . '"/>' .
        '<sheetViews><sheetView rightToLeft="1" workbookViewId="0"/></sheetViews>' .
        '<sheetFormatPr defaultRowHeight="15"/>' .
        $colsXml .
        '<sheetData>' . $rowsXml . '</sheetData>' .
        $merges .
        '<pageMargins left="0.5" right="0.5" top="0.6" bottom="0.6" header="0.3" footer="0.3"/>' .
        '</worksheet>';
}

/** ارسال فایل XLSX به مرورگر و پایان اجرا. */
function xlsx_send(string $filename, string $bytes): never
{
    $name = preg_replace('/\.xlsx?$/i', '', $filename) ?? $filename;
    $name = str_replace(['/', '\\', "\r", "\n", '"'], '-', $name);
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Length: ' . strlen($bytes));
    header("Content-Disposition: attachment; filename*=UTF-8''" . rawurlencode($name . '.xlsx'));
    header('X-Content-Type-Options: nosniff');
    echo $bytes;
    exit;
}
