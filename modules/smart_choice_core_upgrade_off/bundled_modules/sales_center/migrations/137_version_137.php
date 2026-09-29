<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_137 extends App_module_migration
{
    public function up()
    {
        // Version 1.3.7: scoped table toolbar and PHP 8.5 compatibility release.
        return true;
    }
}
