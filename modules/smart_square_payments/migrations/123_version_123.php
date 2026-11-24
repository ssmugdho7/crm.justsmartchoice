<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_123 extends App_module_migration
{
    public function up()
    {
        update_option('smart_square_schema_version', '1.2.3');
    }

    public function down()
    {
        // Financial records and subscription mappings are intentionally preserved.
    }
}
