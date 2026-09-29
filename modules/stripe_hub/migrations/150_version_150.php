<?php

defined('BASEPATH') or exit('No direct script access allowed');
class Migration_Version_150 extends App_module_migration
{
    public function up()
    {
        require module_dir_path('stripe_hub', 'install.php');
        update_option('stripe_hub_version', '1.5.0');
    }
    public function down()
    {
        update_option('stripe_hub_version', '1.5.0');
    }
}
