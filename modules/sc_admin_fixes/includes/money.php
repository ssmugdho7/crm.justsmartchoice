<?php
defined('BASEPATH') or exit('No direct script access allowed');
function scaf_decimal_places($places = null) {
    $places = $places === null ? (int)get_decimal_places() : (int)$places;
    if ($places < 0 || $places > 8) throw new InvalidArgumentException('Invalid decimal scale');
    return $places;
}
function scaf_digits_cmp($a, $b) {
    $a = ltrim($a, '0') ?: '0'; $b = ltrim($b, '0') ?: '0';
    return strlen($a) === strlen($b) ? (strcmp($a, $b) <=> 0) : (strlen($a) <=> strlen($b));
}
function scaf_digits_add($a, $b) {
    $i = strlen($a)-1; $j = strlen($b)-1; $carry = 0; $out = '';
    while ($i >= 0 || $j >= 0 || $carry) {
        $n = ($i >= 0 ? (int)$a[$i--] : 0) + ($j >= 0 ? (int)$b[$j--] : 0) + $carry;
        $out = ($n % 10) . $out; $carry = intdiv($n, 10);
    }
    return ltrim($out, '0') ?: '0';
}
function scaf_digits_sub($a, $b) {
    $i = strlen($a)-1; $j = strlen($b)-1; $borrow = 0; $out = '';
    while ($i >= 0) {
        $n = (int)$a[$i--] - ($j >= 0 ? (int)$b[$j--] : 0) - $borrow;
        $borrow = $n < 0 ? 1 : 0; if ($borrow) $n += 10; $out = $n . $out;
    }
    return ltrim($out, '0') ?: '0';
}
function scaf_decimal_parts($value, $places) {
    if (is_float($value)) throw new InvalidArgumentException('Use decimal strings for money');
    if (!is_string($value) && !is_int($value)) throw new InvalidArgumentException('Invalid decimal');
    if (!preg_match('/^([+-]?)(\d*)(?:\.(\d*))?$/D', trim((string)$value), $m) || (($m[2] ?? '') === '' && ($m[3] ?? '') === '')) throw new InvalidArgumentException('Invalid decimal');
    $fraction = $m[3] ?? ''; $digits = ltrim(($m[2] ?: '0') . substr(str_pad($fraction, $places, '0'), 0, $places), '0') ?: '0';
    if (strlen($fraction) > $places && (int)$fraction[$places] >= 5) $digits = scaf_digits_add($digits, '1');
    return [$m[1] === '-' && $digits !== '0' ? -1 : 1, $digits];
}
function scaf_decimal_format_parts($sign, $digits, $places) {
    $digits = str_pad($digits, $places+1, '0', STR_PAD_LEFT);
    return ($sign < 0 && trim($digits, '0') !== '' ? '-' : '') . ($places ? substr($digits, 0, -$places) . '.' . substr($digits, -$places) : $digits);
}
function scaf_decimal_normalize($value, $places = null) {
    $places = scaf_decimal_places($places); [$sign, $digits] = scaf_decimal_parts($value, $places);
    return scaf_decimal_format_parts($sign, $digits, $places);
}
function scaf_decimal_add($a, $b, $places = null) {
    $places = scaf_decimal_places($places); [$sa,$da] = scaf_decimal_parts($a,$places); [$sb,$db] = scaf_decimal_parts($b,$places);
    if ($sa === $sb) return scaf_decimal_format_parts($sa, scaf_digits_add($da,$db),$places);
    $cmp = scaf_digits_cmp($da,$db);
    return scaf_decimal_format_parts($cmp >= 0 ? $sa : $sb, $cmp >= 0 ? scaf_digits_sub($da,$db) : scaf_digits_sub($db,$da),$places);
}
function scaf_decimal_sub($a, $b, $places = null) {
    if (is_float($b)) throw new InvalidArgumentException('Use decimal strings for money');
    $b = trim((string)$b); return scaf_decimal_add($a, str_starts_with($b,'-') ? substr($b,1) : '-'.ltrim($b,'+'), $places);
}
function scaf_decimal_cmp($a, $b, $places = null) {
    $places = scaf_decimal_places($places); [$sa,$da] = scaf_decimal_parts($a,$places); [$sb,$db] = scaf_decimal_parts($b,$places);
    return $sa !== $sb ? $sa <=> $sb : $sa * scaf_digits_cmp($da,$db);
}
