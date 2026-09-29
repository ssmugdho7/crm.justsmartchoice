<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Purchase extends AdminController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index(){ redirect(admin_url('purchasing_hub')); }
    public function vendors(){ redirect(admin_url('purchasing_hub/vendors')); }
    public function items(){ redirect(admin_url('purchasing_hub/items')); }
    public function item_list(){ redirect(admin_url('purchasing_hub/items')); }
    public function purchase_order(){ redirect(admin_url('purchasing_hub/purchase_orders')); }
    public function purchase_orders(){ redirect(admin_url('purchasing_hub/purchase_orders')); }
    public function pur_invoice(){ redirect(admin_url('purchasing_hub/accounts_payable')); }
    public function reports(){ redirect(admin_url('purchasing_hub/reports')); }
    public function report(){ redirect(admin_url('purchasing_hub/reports')); }
    public function setting(){ redirect(admin_url('settings?group=purchasing_hub_settings')); }
    public function help_training(){ redirect(admin_url('settings?group=purchasing_hub_help')); }
}
