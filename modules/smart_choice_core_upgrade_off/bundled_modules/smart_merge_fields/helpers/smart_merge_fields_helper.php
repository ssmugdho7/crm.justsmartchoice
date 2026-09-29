<?php

defined('BASEPATH') or exit('No direct script access allowed');

function smart_merge_fields_human_name($text)
{
    $text = preg_replace('/^' . preg_quote(db_prefix(), '/') . '/', '', (string) $text);
    $text = str_replace(['_', '-'], ' ', $text);
    return ucwords(trim($text));
}
