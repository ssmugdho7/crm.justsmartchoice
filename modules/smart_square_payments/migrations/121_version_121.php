<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Migration_Version_121 extends App_module_migration
{
    public function up() { update_option('smart_square_schema_version', '1.2.1'); }
    public function down() { /* Preserve financial data and credentials. */ }
}
