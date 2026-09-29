<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_112 extends App_module_migration
{
    public function up()
    {
        // Navigation and portal-validation release only. No destructive schema changes.
        if (get_option('solar_pro_version') === false) { add_option('solar_pro_version', '1.1.2'); } else { update_option('solar_pro_version', '1.1.2'); }
    }
}
