<?php
defined('BASEPATH') or exit('No direct script access allowed');
/** Solar Pro 1.1.1 - performance and settings navigation repair. */
class Migration_Version_111 extends App_module_migration
{
    public function up()
    {
        // No schema mutation is required. This migration only records the
        // module upgrade after controller/settings behavior was repaired.
    }
}
