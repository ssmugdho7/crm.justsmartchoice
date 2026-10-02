<?php
defined('BASEPATH') or exit('No direct script access allowed');

function google_meet_parse_datetime($value)
{
    $value = trim((string)$value);
    if ($value === '') { return null; }
    $formats = ['Y-m-d H:i:s', 'Y-m-d H:i', 'Y-m-d\TH:i:s', 'Y-m-d\TH:i'];
    if (function_exists('get_current_date_format')) {
        $format = get_current_date_format(true);
        foreach ([' H:i:s', ' H:i', ' g:i A', ' h:i A'] as $time) { $formats[] = $format . $time; }
    }
    foreach (array_unique($formats) as $format) {
        $date = DateTimeImmutable::createFromFormat('!' . $format, $value);
        $errors = DateTimeImmutable::getLastErrors();
        if ($date && (!$errors || (!$errors['warning_count'] && !$errors['error_count']))
            && $date->format('Y') > 1970) {
            return $date->format('Y-m-d H:i:s');
        }
    }
    return null;
}

function google_meet_display_datetime($value)
{
    $date = google_meet_parse_datetime($value);
    return $date ? _dt($date) : 'Time needs correction';
}

function google_meet_form_datetime($value)
{
    $date = google_meet_parse_datetime($value);
    return $date ? str_replace(' ', 'T', substr($date, 0, 16)) : '';
}
