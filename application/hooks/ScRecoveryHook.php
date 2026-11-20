<?php
defined('BASEPATH') or exit('No direct script access allowed');
final class ScRecoveryHook
{
    public function bootstrap(): void
    {
        require_once APPPATH . 'helpers/recovery_helper.php';
    }
    public function request(): void
    {
        if (!sc_recovery_enabled()) return;
        $ci = &get_instance();
        if (function_exists('is_admin') && is_admin() && isset($ci->app_menu)) {
            $ci->app_menu->add_setup_menu_item('recovery', ['name'=>'Trash & Recovery','href'=>admin_url('recovery'),'position'=>95,'icon'=>'fa fa-history']);
            $ci->load->vars(['setup_menu'=>$ci->app_menu->get_setup_menu_items()]);
        }
        if (isset($ci->db)) $ci->db->query('SET @sc_recovery_batch = ?, @sc_recovery_actor = ?', [sc_recovery_batch(), sc_recovery_actor()]);
        if (sc_recovery_actor() === 'request') return;
        // Also retain authenticated uploads handled by third-party modules.
        foreach ($_FILES as $upload) {
            $this->incoming($upload['tmp_name'] ?? [], $upload['name'] ?? []);
        }
    }
    private function incoming($temp, $name): void
    {
        if (is_array($temp)) {
            foreach ($temp as $key => $value) $this->incoming($value, is_array($name) ? ($name[$key] ?? '') : '');
        } elseif (is_string($temp) && is_uploaded_file($temp)) {
            $safe = preg_replace('/[^a-zA-Z0-9._ -]/', '_', basename((string)$name));
            sc_recovery_capture($temp, 'incoming', FCPATH . 'uploads/incoming-' . substr($safe ?: 'upload', 0, 180));
        }
    }
}
