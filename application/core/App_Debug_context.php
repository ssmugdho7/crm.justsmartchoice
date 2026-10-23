<?php

defined('BASEPATH') or exit('No direct script access allowed');

/** Correlate a document and its same-origin background requests without collecting request data. */
class App_Debug_context
{
    public static function visit_id()
    {
        static $id;
        if ($id === null) {
            $header = $_SERVER['HTTP_X_SC_DEBUG_VISIT'] ?? '';
            $id = is_string($header) && preg_match('/^[a-f0-9]{32}$/D', $header) ? $header : bin2hex(random_bytes(16));
        }
        return $id;
    }

    public static function route()
    {
        $route = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '/cli';
        $route = '/' . implode('/', array_slice(explode('/', trim($route, '/')), 0, 3));
        return preg_replace('~[^a-zA-Z0-9/_-]~', '_', $route);
    }
}
