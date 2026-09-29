<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_416 extends CI_Migration
{
    public function up()
    {
        update_option('sc_crm_build_version', '4.1.6');
        update_option('smart_choice_crm_build', '4.1.6');
        update_option('smart_choice_crm_current_version', '4.1.6');
    }
}
