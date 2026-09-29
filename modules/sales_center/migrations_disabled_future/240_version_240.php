<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_240 extends App_module_migration
{
    public function up()
    {
        // Version 2.4.0: portal submission/email stability, editable help guide, restored filters, and action button alignment.
        if (function_exists('update_option')) {
            update_option('sales_center_last_repair', date('Y-m-d H:i:s'));
        }
    }
}
