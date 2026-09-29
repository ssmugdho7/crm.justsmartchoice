<?php defined('BASEPATH') or exit('No direct script access allowed');
if (!function_exists('estimating_hub_ai_clean_money')) {
    function estimating_hub_ai_clean_money($value){
        if (is_numeric($value)) { return (float)$value; }
        $value = preg_replace('/[^0-9.\-]/', '', (string)$value);
        return is_numeric($value) ? (float)$value : 0.0;
    }
}
if (!function_exists('estimating_hub_ai_markup')) {
    function estimating_hub_ai_markup($cost,$markup){ return estimating_hub_ai_clean_money($cost)*(1+(estimating_hub_ai_clean_money($markup)/100)); }
}
if (!function_exists('ehai_limit')) {
    function ehai_limit($text, $limit = 120){
        $text = trim(strip_tags((string)$text));
        if (function_exists('character_limiter')) { return character_limiter($text, $limit); }
        return mb_strlen($text) > $limit ? mb_substr($text, 0, $limit - 3).'...' : $text;
    }
}
if (!function_exists('ehai_money')) {
    function ehai_money($value){ return number_format((float)$value, 2, '.', ','); }
}
if (!function_exists('ehai_safe')) {
    function ehai_safe($value){ return html_escape((string)$value); }
}
