<?php
defined('BASEPATH') or exit('No direct script access allowed');
/** Public instructions only: this endpoint never removes account data. */
class Data_deletion extends ClientsController
{
    public function index()
    {
        if (!$this->app_modules->is_active('sc_social_login')) { show_404(); return; }
        $this->load->view('sc_social_login/data_deletion');
    }
}
