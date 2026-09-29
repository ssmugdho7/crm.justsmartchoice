<?php

defined('BASEPATH') or exit('No direct script access allowed');
class Migration_Version_100 extends App_module_migration
{
    public function up()
    {
        require module_dir_path('smart_choice_payment_schedule', 'install.php');
    }
    public function down()
    {
        // Accounting history is preserved.
    }
}
