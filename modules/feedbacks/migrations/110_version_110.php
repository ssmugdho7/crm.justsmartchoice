<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Version 2.4.0: introduces the built-in template library and management UI.
 * The library is shipped as module data; no existing tables or records are altered.
 */
class Migration_Version_110 extends App_module_migration
{
    public function up()
    {
        return true;
    }

    public function down()
    {
        // No schema changes in this release; existing questionnaires remain untouched.
        return true;
    }
}
