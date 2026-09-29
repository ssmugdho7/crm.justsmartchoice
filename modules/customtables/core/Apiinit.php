<?php

namespace modules\customtables\core;

defined('BASEPATH') || exit('No direct script access allowed');

class Apiinit
{
    public static function ease_of_mind($module_name) { return true; }
    public static function the_da_vinci_code($module_name) { return true; }
    public static function activate($module) { return true; }
    public static function pre_validate($module_name, $purchase_key = '') { return ['status' => true, 'message' => 'Module ready']; }
    public static function getUserIP() { return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'; }
}
