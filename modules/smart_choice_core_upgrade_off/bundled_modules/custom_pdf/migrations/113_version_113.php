<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_113 extends App_module_migration
{
    public function up()
    {
        update_option('custom_pdf_version', '1.1.3');
        update_option('custom_pdf_quality', '★★★★★');
        update_option('custom_pdf_author_uri', 'https://justsmartchoice.com/webdeveloper.php');
    }
}
