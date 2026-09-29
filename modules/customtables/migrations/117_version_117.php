<?php

defined('BASEPATH') || exit('No direct script access allowed');

class Migration_Version_117 extends App_module_migration
{
    public function up()
    {
        // Smart Choice safe migration: removed license checks and core-file modification.
        return true;
    }

    public function down() {}
}
