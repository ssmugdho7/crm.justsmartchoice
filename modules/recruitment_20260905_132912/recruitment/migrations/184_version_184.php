<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Migration_Version_184 extends App_module_migration
{
    public function up()
    {
        if (get_option('recruitment_version') !== '1.8.4') {
            update_option('recruitment_version', '1.8.4');
        }
        if (get_option('recruitment_portal_languages') === '') {
            add_option('recruitment_portal_languages', 'english,spanish');
        }
    }
    public function down() {}
}
