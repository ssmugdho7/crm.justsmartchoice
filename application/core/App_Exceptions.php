<?php

defined('BASEPATH') or exit('No direct script access allowed');

/** Keep diagnostic context private; never include request query strings, bodies or trace arguments. */
class App_Exceptions extends CI_Exceptions
{
    public function log_exception($severity, $message, $filepath, $line)
    {
        static $requestId;
        $requestId = $requestId ?: bin2hex(random_bytes(8));
        $route = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '/cli';
        $route = '/' . implode('/', array_slice(explode('/', trim($route, '/')), 0, 3));
        $route = preg_replace('~[^a-zA-Z0-9/_-]~', '_', $route);
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 12);
        $module = 'core';
        foreach (array_merge([['file' => $filepath]], $trace) as $frame) {
            if (preg_match('~/modules/([a-zA-Z0-9_-]+)/~', $frame['file'] ?? '', $m)) { $module = $m[1]; break; }
        }
        $message .= ' [request=' . $requestId . '] [route=' . $route . '] [module=' . $module . ']';
        if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
            foreach ($trace as $frame) {
                if (isset($frame['file'], $frame['line'])) {
                    $message .= "\n  at " . $frame['file'] . ':' . $frame['line'] . ' ' . ($frame['class'] ?? '') . ($frame['type'] ?? '') . ($frame['function'] ?? '');
                }
            }
        }
        parent::log_exception($severity, $message, $filepath, $line);
    }
}
