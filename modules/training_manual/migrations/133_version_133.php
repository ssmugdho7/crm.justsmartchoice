<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_133 extends App_module_migration
{
    public function up()
    {
        // Routing, menu icon, and upload guidance upgrade. No destructive schema changes.
        add_option('training_manual_cover_recommended_dimensions', '1200x675');
        add_option('training_manual_cover_max_size_mb', '2');
    }

    public function down()
    {
        // Upgrade-only migration. Existing settings and data are intentionally preserved.
    }
}
