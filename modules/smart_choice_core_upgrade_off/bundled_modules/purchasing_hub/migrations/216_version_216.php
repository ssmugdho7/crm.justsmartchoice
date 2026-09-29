<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_216 extends App_module_migration
{
    public function up()
    {
        // Version 2.1.6 is a front-end repair release.
        // It intentionally performs no destructive database changes.
        return true;
    }
}
