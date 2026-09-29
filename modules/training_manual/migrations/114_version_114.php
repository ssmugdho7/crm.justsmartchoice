<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_114 extends CI_Migration
{
    public function up()
    {
        $CI = &get_instance();
        require_once(__DIR__ . '/../install.php');
    }

    public function down()
    {
        // Safe migration: no destructive rollback.
    }
}
