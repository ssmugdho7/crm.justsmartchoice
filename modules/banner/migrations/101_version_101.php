<?php

defined('BASEPATH') || exit('No direct script access allowed');

class Migration_Version_101 extends App_module_migration
{
	public function __construct()
	{
		parent::__construct();
	}

	public function up()
	{
	}
    public function down()
    {
        // Safe rollback placeholder intentionally left non-destructive.
        return true;
    }

}
