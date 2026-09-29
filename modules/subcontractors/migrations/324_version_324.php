<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_324 extends App_module_migration
{
    /**
     * Module identity, public digital-link routing, and 120 px contract-logo repair.
     * Existing database tables and records remain unchanged.
     */
    public function up()
    {
        return true;
    }

    public function down()
    {
        return true;
    }
}
