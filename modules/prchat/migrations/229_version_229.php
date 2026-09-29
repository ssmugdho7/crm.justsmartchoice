<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * PRChat 2.2.9 compatibility migration.
 *
 * IMPORTANT:
 * This migration must remain a true no-op. The 2.2.9 release contains only
 * permission/UI/controller changes and requires no schema or option mutation.
 * Perfex's module migration manager is responsible for advancing the module
 * migration state after this method returns successfully.
 *
 * Keeping this migration side-effect free prevents a partially upgraded CRM
 * from becoming stuck while loading/upgrading PRChat. No messages, groups,
 * uploads, settings, API credentials, provider configuration, or permissions
 * are modified here.
 */
class Migration_Version_229 extends App_module_migration
{
    public function up()
    {
        // Intentionally empty: code-only release.
    }

    public function down()
    {
        // Upgrade-only; no destructive rollback.
    }
}
