<?php
defined('BASEPATH') or exit('No direct script access allowed');
/** Collection only: never exposes staff reports or accepts anonymous telemetry. */
class Client_diagnostics extends App_Controller
{
    public function collect()
    {
        if (!is_client_logged_in() || !get_contact_user_id() || !debug_mode_is_enabled() || get_option('debug_mode_client_capture') !== '1') {
            show_error('Forbidden', 403); return;
        }
        require_once dirname(__DIR__) . '/libraries/Debug_mode_browser_receiver.php';
        Debug_mode_browser_receiver::receive($this, true);
    }
}
