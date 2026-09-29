<?php

namespace modules\banner\core;

class Apiinit
{
    public static function the_da_vinci_code($module_name)
    {
        $module = get_instance()->app_modules->get($module_name);

        return true;  // Always return true as verification is removed.
    }

    public static function ease_of_mind($module_name)
    {
        if (!function_exists($module_name.'_actLib') || !function_exists($module_name.'_sidecheck') || !function_exists($module_name.'_deregister')) {
            get_instance()->app_modules->deactivate($module_name);
        }
    }

    public static function activate($module)
    {
        // Remove the license activation logic.
    }

    public static function getUserIP()
    {
        $ipaddress = '';
        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED'])) {
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        } elseif (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_FORWARDED'])) {
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        } else {
            $ipaddress = 'UNKNOWN';
        }

        return $ipaddress;
    }

    public static function pre_validate($module_name, $code = '')
    {
        // Remove the license validation logic.
        return ['status' => true, 'message' => 'License validation removed'];
    }
}
