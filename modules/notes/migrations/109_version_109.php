<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_109 extends App_module_migration
{
    public function up()
    {
        // UI-only update: compact notes table layout and remove Assigned To column from the list view.
        return true;
    }
}
