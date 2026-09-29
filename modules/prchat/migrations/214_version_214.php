<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Migration_Version_214 extends App_module_migration
{
    public function up()
    {
        if (get_option('prchat_module_version') === false) { add_option('prchat_module_version', '2.1.4'); } else { update_option('prchat_module_version', '2.1.4'); }
    }
    public function down() {}
}
