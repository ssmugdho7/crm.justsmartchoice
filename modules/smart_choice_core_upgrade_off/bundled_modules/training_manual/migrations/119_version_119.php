<?php defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_119 extends App_module_migration
{
    public function up()
    {
        // UI/style-only upgrade. No destructive database changes.
        add_option('training_manual_inline_styles_enabled', '1');
    }
}
