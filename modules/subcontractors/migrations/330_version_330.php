<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_330 extends App_module_migration
{
    public function up()
    {
        // Presentation and validation upgrade only. No destructive schema changes.
        if (function_exists('smartsource_insert_email_templates')) {
            smartsource_insert_email_templates();
        }
    }
}
