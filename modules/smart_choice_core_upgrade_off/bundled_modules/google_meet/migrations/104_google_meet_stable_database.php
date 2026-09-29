<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Google_meet_stable_database extends App_module_migration
{
    public function up()
    {
        require_once(__DIR__ . '/../install.php');
    }
}
