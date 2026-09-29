<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_127 extends App_module_migration
{
    public function up()
    {
        update_option('smart_choice_links_version', '1.2.7');
    }
}
