<?php
defined('BASEPATH') or exit('No direct script access allowed');
/** Compatibility bridge: installations already marked 2.4.0 expect migration 240. */
class Migration_Version_240 extends App_module_migration
{
    public function up() { return true; }
    public function down() { return true; }
}
