<?php defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_199 extends App_module_migration
{
    public function up()
    {
        // Compatibility migration shim for safe upgrade sequence.
    }
    public function down()
    {
        // Safe rollback placeholder intentionally left non-destructive.
        return true;
    }

}
