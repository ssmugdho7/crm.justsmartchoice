<?php

defined('BASEPATH') || exit('No direct script access allowed');

class Migration_Version_126 extends App_module_migration
{

    public function up()
    {

        add_option('etm_add_staff_name_to_from' , 1 , 0);

        add_option('etm_staff_see_only_sent_mail' , 0 , 0);

    }
    public function down()
    {
        // Safe rollback placeholder intentionally left non-destructive.
        return true;
    }


}
