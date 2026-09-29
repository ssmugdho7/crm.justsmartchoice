<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Ultimatepos extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('ultimatepos_model');
    }

    public function sync_customers()
    {
        $this->ultimatepos_model->sync_customers();
        set_alert('success', 'Customers synchronized successfully');
        redirect(admin_url('settings?group=ultimatepos'));
    }

  
}
