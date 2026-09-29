<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Google_meet_auto_reports_notes_fix extends App_module_migration
{
    public function up()
    {
        require_once(__DIR__ . '/../install.php');
        return true;
    }

    public function down()
    {
        return true;
    }
}
