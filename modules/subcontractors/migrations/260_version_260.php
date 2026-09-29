<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_260 extends App_module_migration
{
    public function up()
    {
        // Version 2.6.0: portal thank-you redirect hardening.
        // Uses profile/{token}?saved=1 so public profile routing handles the success page reliably.
        return true;
    }

    public function down()
    {
        return true;
    }
}
