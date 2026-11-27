<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Smart Choice Feedbacks migration sequence bridge.
 * This migration is intentionally empty; it preserves Perfex's required
 * consecutive migration numbering without changing existing database data.
 */
class Migration_Version_182 extends App_module_migration
{
    public function up()
    {
        return true;
    }

    public function down()
    {
        return true;
    }
}
