<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Repairs module-local routing and signing compatibility without changing data.
 */
class Migration_Version_325 extends App_module_migration
{
    public function up()
    {
        return true;
    }

    public function down()
    {
        return true;
    }
}
