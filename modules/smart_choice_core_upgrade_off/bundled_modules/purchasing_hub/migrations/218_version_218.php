<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_218 extends App_module_migration
{
    public function up()
    {
        // Version 2.1.8 is a UI-only table action placement repair release.
        // Import, Refresh, and Massive Delete are moved next to the native DataTables Export controls.
        // It intentionally performs no destructive database changes.
        return true;
    }
}
