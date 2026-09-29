# StyleFlow 1.1.2 Repair

## Repairs
- Added Perfex-compatible migration 111 for module version 1.1.1.
- Added migration 112 for module version 1.1.2.
- Migration target is now 112.
- Preserved historical migrations 001 and 002.
- Centralized StyleFlow UI labels, table/header style labels, activity text, and system-template display names in the English and Spanish language files.
- Preserved existing database templates, active selections, invoices, estimates, proposals, and settings.

## Deployment
1. Back up the database and modules/styleflow.
2. Upload the complete styleflow folder over the existing module.
3. Open the Perfex modules page and allow upgrade to 1.1.2.
4. Confirm migration 111/112 completes and StyleFlow opens.

## Rollback
Restore the previous module folder and database backup. Do not manually delete StyleFlow tables.
