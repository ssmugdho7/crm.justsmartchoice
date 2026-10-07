<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_128 extends App_module_migration
{
    public function up()
    {
        // Perfex requires sequential numbers when upgrading older 1.2.x installs to 1.3.0.
    }

    public function down()
    {
        // No schema or data changes in this compatibility step.
    }
}
