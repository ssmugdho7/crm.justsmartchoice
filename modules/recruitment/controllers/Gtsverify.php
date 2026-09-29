<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Gtsverify extends AdminController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function activate()
    {
        set_alert('success', 'Recruitment module activation is not required in the Smart Choice Contractors build.');
        redirect(admin_url('modules'));
    }
}
