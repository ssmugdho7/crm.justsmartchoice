<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_113 extends App_module_migration
{
    public function up()
    {
        update_option('google_meet_version', '1.1.3');
    }

    public function down()
    {
        // Preserve production meeting data.
    }
}
