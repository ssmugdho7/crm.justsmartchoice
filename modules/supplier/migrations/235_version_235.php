<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_235 extends App_module_migration
{
    public function up()
    {
        // Supplier v1.0.3 UI/service update. No database changes required.
    }


    public function down()
    {
        // Smart Choice safe no-op rollback. Keep CRM data intact.
    }
}
