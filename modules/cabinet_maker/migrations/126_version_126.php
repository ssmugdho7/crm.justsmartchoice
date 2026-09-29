<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_126 extends App_module_migration
{
    public function up()
    {
        // Compatibility bridge for installations that previously stopped at 126.
        // Database repair is performed once in migration 127.
        update_option('cabinet_maker_migration_checkpoint', '126');
    }

    public function down()
    {
        // Intentionally non-destructive.
    }
}
