<?php
namespace App\UnitConverter;

use Illuminate\Support\HtmlString;

/**
 * @param int $byte
 * @param int $precision
 * @return string
 */
function byteToHumanReadable(int $byte, int $precision = 2): string {
    $conversions = DigitalStorageConverter::convert($byte, $precision)->from('B')->all();
    arsort($conversions, SORT_NUMERIC);
    $last = [array_last(array_keys($conversions)) => array_pop($conversions)];

    foreach (array_reverse($conversions) as $unit => $value) {
        if ($value > 1000.0) {
            return new HtmlString(sprintf('%s&nbsp;%s', array_first($last), array_first(array_keys($last))));
        }

        $last = [$unit => $value];
    }

    return new HtmlString(sprintf('%s&nbsp;%s', array_first($last), array_first(array_keys($last))));
}
