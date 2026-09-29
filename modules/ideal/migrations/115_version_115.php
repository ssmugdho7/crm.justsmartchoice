<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_115 extends App_module_migration
{
    public function up()
    {
        update_option('ideal_module_version', '1.1.5');
    }

    public function down()
    {
        // Preserve payment configuration and transaction records.
    }
}
