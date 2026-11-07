<?php
declare(strict_types=1);

define('APP_ROOT', dirname(__DIR__, 2));
require APP_ROOT . '/lib/tickets-sla.php';

$raw = stream_get_contents(STDIN);
$dates = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
if (!is_array($dates)) {
    throw new RuntimeException('Expected a JSON array of Jalali dates');
}
$result = [];
foreach ($dates as $date) {
    if (!is_array($date) || count($date) !== 3) {
        throw new RuntimeException('Invalid Jalali date tuple');
    }
    [$jy, $jm, $jd] = array_map('intval', array_values($date));
    [$gy, $gm, $gd] = jalali_to_gregorian($jy, $jm, $jd);
    $back = gregorian_to_jalali($gy, $gm, $gd);
    $input = jalali_input_to_gregorian(sprintf('%04d/%02d/%02d', $jy, $jm, $jd));
    $end = jalali_input_to_gregorian(sprintf('%04d/%02d/%02d', $jy, $jm, $jd), true);
    $result[] = [
        'gregorian' => [$gy, $gm, $gd],
        'round_trip' => $back,
        'input' => $input,
        'end_of_day' => $end,
    ];
}
echo json_encode($result, JSON_THROW_ON_ERROR);
