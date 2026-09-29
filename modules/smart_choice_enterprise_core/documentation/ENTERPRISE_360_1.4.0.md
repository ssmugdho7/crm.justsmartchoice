# Enterprise 360 v1.4.0

Portal-installable replacement upgrade for the existing `smart_choice_enterprise_core` module.

## Installation
Upload the ZIP through Setup > Modules > Install Module. Approve replacement of the existing module files, then run Upgrade Database if shown. Do not uninstall the existing module.

## Repairs
- Enterprise Dashboard and Enterprise Foundation use safe table checks and no longer fail when a table is missing.
- Schema verification runs during activation, migrations, and once when the registered option does not match v1.4.0.
- Sequential migrations 100 through 140 are included.
- System Health lists all Phase 1-7 enterprise tables and provides Repair Enterprise Schema.
- Staff Image Health uses native Perfex paths and provides Repair Image Folders.
- Main menu and module display name changed to Enterprise 360.
- Enterprise Data Manager provides Reload, Sample Header, Import, Export, and permission-controlled Mass Delete for every enterprise extension table.

## Data safety
No existing Perfex core table is dropped or recreated. Uninstall preserves enterprise operational and audit data.
