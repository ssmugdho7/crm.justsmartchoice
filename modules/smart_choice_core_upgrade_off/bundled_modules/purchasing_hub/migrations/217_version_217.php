<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_217 extends App_module_migration
{
    public function up()
    {
        // Version 2.1.7 is a UI compactness and Purchase Order search/action bar repair release.
        // It intentionally performs no destructive database changes.
        return true;
    }
}
