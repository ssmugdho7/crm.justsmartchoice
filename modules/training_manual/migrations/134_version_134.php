<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_134 extends App_module_migration
{
    public function up()
    {
        // Presentation-only upgrade. Existing books, articles, images and assignments are preserved.
    }

    public function down()
    {
        // No destructive rollback.
    }
}
