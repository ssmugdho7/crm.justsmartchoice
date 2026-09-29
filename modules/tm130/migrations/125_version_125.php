<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_125 extends App_module_migration
{
    public function up()
    {
        // Layout-only release: restores Articles and Bookmark-filtered Articles to a fixed 3-across card grid.
    }

    public function down()
    {
        // No database changes to roll back.
    }
}
