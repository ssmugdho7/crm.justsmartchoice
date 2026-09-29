<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Migration_Version_213 extends App_module_migration
{
    public function up()
    {
        if (get_option('prchat_module_version') === '') {
            add_option('prchat_module_version', '2.1.3');
        } else {
            update_option('prchat_module_version', '2.1.3');
        }
    }
    public function down() {}
}
