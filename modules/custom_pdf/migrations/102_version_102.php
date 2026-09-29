<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_102 extends App_module_migration
{
    public function up()
    {
        update_option('custom_pdf_version_checkpoint_102', '1');
    }
}
