<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Debug_mode_browser_receiver
{
    public static function receive($ci, $portal = false)
    {
        if ($ci->input->method(true) !== 'POST') { show_error('POST required', 405); return; }
        // CI consumes its normal POST token during global CSRF validation. Keep an independent, session-bound token.
        $token = $ci->input->post('debug_capture_token');
        $expectedToken = $ci->session->userdata('debug_mode_capture_token');
        if (!is_string($token) || !is_string($expectedToken) || $expectedToken === '' || !hash_equals($expectedToken, $token)) { show_error('Invalid CSRF token', 403); return; }
        $window = $ci->session->userdata('debug_mode_capture_window');
        if (!is_array($window) || ($window['start'] ?? 0) < time() - 60) { $window = ['start' => time(), 'count' => 0]; }
        if ($window['count'] >= 40) { $ci->output->set_status_header(429); return; }
        $window['count']++;
        $ci->session->set_userdata('debug_mode_capture_window', $window);
        $kind = $ci->input->post('kind');
        if (!in_array($kind, ['javascript', 'promise', 'resource', 'http', 'network'], true)) { show_error('Invalid diagnostic kind', 400); return; }
        require_once dirname(__DIR__) . '/libraries/Debug_mode_log_reader.php';
        $fields = [];
        foreach (['message', 'file', 'route'] as $key) {
            $value = $ci->input->post($key);
            if (!is_string($value)) { show_error('Invalid diagnostic field', 400); return; }
            if ($key !== 'message') { $value = parse_url($value, PHP_URL_PATH) ?: '/'; }
            $fields[$key] = str_replace(["\r", "\n"], ' ', Debug_mode_log_reader::redact(substr($value, 0, 700)));
        }
        if ($portal) {
            $fields['message'] = 'Client portal ' . $kind . ' error';
            $parts = explode('/', trim($fields['route'], '/'));
            $fields['route'] = '/' . preg_replace('/[^a-zA-Z_-]/', '', $parts[0]);
            if (!preg_match('~^/(assets|modules)/.*\.(js|css|png|jpg|jpeg|svg|webp|gif|woff2?)$~i', $fields['file'])) { $fields['file'] = $fields['route']; }
        }
        $module = 'core';
        if (preg_match('~^/modules/([a-zA-Z0-9_-]+)/~', $fields['file'], $m)) { $module = $m[1]; }
        elseif (preg_match('~^/admin/([a-zA-Z0-9_-]+)~', $fields['route'], $m)) { $module = $m[1]; }
        $visit = $ci->input->post('visit');
        $visitContext = !$portal && is_string($visit) && preg_match('/^[a-f0-9]{32}$/D', $visit) ? ' [visit=' . $visit . ']' : '';
        log_message('error', '[browser] ' . ($portal ? '[client-portal] ' : '') . '[' . $kind . '] [module=' . $module . '] ' . $fields['message']
            . ' [route=' . $fields['route'] . '] [file=' . $fields['file'] . ':' . max(0, (int) $ci->input->post('line')) . ']' . $visitContext);
        $ci->output->set_status_header(204);
    }
}
