<?php

defined('BASEPATH') || exit('No direct script access allowed');

class Env_ver extends AdminController
{
    public function index()
    {
        redirect(admin_url('customtables'));
    }

    public function activate()
    {
        set_alert('success', 'Custom Data Tables is ready. Activation validation is not required for this internal Smart Choice module.');
        redirect(admin_url('modules'));
    }
}
