# Smart Choice Enterprise Core — Phase 2

Version 1.1.0 upgrades the existing Phase 1 module in place.

## Installation

1. Back up the CRM database and module folder.
2. Extract the ZIP.
3. Upload the `smart_choice_enterprise_core` folder to `public_html/crm.justsmartchoice/modules/` and merge/replace the existing module files.
4. Open Setup → Modules.
5. Use the standard Perfex upgrade action for Smart Choice Enterprise Core.
6. Confirm version 1.1.0 and open Smart Choice Enterprise Core → System Health.
7. Assign the new role permissions.

## New extension tables

- `tblsce_events`
- `tblsce_queue_jobs`
- `tblsce_feature_flags`
- `tblsce_audit_log`
- `tblsce_field_permissions`
- `tblsce_api_clients`

The actual prefix follows the CRM `db_prefix()` configuration. No existing operational table is modified.

## Queue

The queue is processed through the existing Perfex cron action. The included test job is a safe no-operation job used to verify insertion and cron processing.

## API security

The API foundation table is installed, but no API client, secret, route, or public access is enabled automatically.

## Rollback

Migration 110 includes `down()`. Rolling it back removes only Phase 2 extension tables. Do not use rollback after later phases depend on these services.
