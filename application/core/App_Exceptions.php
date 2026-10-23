<?php

defined('BASEPATH') or exit('No direct script access allowed');

require_once __DIR__ . '/App_Debug_context.php';

/** Keep diagnostic context private; never include request query strings, bodies or trace arguments. */
class App_Exceptions extends CI_Exceptions
{
    public function log_exception($severity, $message, $filepath, $line)
    {
        static $requestId;
        $requestId = $requestId ?: bin2hex(random_bytes(8));
        $route = App_Debug_context::route();
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 12);
        $module = 'core';
        foreach (array_merge([['file' => $filepath]], $trace) as $frame) {
            if (preg_match('~/modules/([a-zA-Z0-9_-]+)/~', $frame['file'] ?? '', $m)) { $module = $m[1]; break; }
        }
        $message .= ' [request=' . $requestId . '] [visit=' . App_Debug_context::visit_id() . '] [route=' . $route . '] [module=' . $module . ']';
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
