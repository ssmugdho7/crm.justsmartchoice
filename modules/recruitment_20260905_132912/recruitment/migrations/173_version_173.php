<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_173 extends App_module_migration
{
    public function up()
    {
        // Smart Choice Contractors USA / Harold Cabrera
        // Version 1.7.3 is a visual/compatibility update for the recruitment portal.
        // No destructive database changes are required for this upgrade.
        return true;
    }

    public function down()
    {
        return true;
    }
}
