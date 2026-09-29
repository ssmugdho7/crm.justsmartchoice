<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Backward-compatible controller for old bookmarks.
 * Customer authentication is mandatory before any meeting data is displayed.
 */
class Google_meet_public extends App_Controller
{
    public function index()
    {
        if (!is_client_logged_in()) {
            redirect(site_url('authentication/login'));
        }

        redirect(site_url('google_meet/meeting_clients/meetings'));
    }
}
