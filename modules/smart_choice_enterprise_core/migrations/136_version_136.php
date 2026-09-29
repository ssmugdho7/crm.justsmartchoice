<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Migration_Version_136 extends App_module_migration
{
    public function up()
    {
        require_once __DIR__ . '/../libraries/Enterprise_schema.php';
        Enterprise_schema::install();
    }
    public function down() { }
}
