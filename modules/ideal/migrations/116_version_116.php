<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_116 extends App_module_migration
{
    public function up()
    {
        // Code compatibility release only. Preserve all existing Stripe credentials,
        // gateway settings, payment records, webhook identifiers, and subscription data.
        update_option('ideal_module_version', '1.1.6');
        return true;
    }

    public function down()
    {
        return true;
    }
}
