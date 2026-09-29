# StyleFlow 1.1.5

## Repair
- Removes/neutralizes the obsolete `modules/styleflow/config/migration.php` left behind by affected 1.1.1-1.1.3 packages.
- Cleanup runs from the StyleFlow module bootstrap before the StyleFlow controller loads.
- Prevents StyleFlow from overriding CodeIgniter/Perfex core migration configuration.
- Adds module migration `115_version_115.php`; it is a module-only version marker and does not invoke the CRM migration library.
- Preserves all StyleFlow templates, settings, activation selections, invoices, estimates, proposals, and CRM data.

## Deployment
1. Back up database and `modules/styleflow/`.
2. Upload the complete StyleFlow 1.1.5 folder over the current module.
3. Load any normal CRM admin page once. The bootstrap removes the stale StyleFlow migration config if it remains on disk.
4. Open StyleFlow. It must open the template manager, not the Perfex core database-upgrade screen.
5. If Perfex offers a StyleFlow module upgrade, run it; migration 115 only records the module version.

## Rollback
Restore the previous module folder and database backup. Do not delete StyleFlow tables manually.
