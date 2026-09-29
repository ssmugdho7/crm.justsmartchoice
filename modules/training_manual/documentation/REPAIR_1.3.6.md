# Training Manual 1.3.6 Repair

## Scope
- Fixed public training article URLs so `training_manual/<slug>` resolves to the published article controller instead of returning 404.
- New Training Manual saves now return to the Training Manuals main dashboard.
- New Training Article saves now return to the Training Articles main dashboard.
- Existing Save and Build behavior remains unchanged.
- Existing edit/save behavior remains unchanged.
- No tables, records, settings, uploads, API keys, permissions, manuals, or articles are deleted.

## Migration
- Added sequential migration `136_version_136.php`.
- Migration is upgrade-only and non-destructive.
- Existing migrations 101 through 135 are preserved unchanged.

## Install / Upgrade
1. Back up the CRM database.
2. Back up `modules/training_manual/` and `uploads/training_manual/`.
3. Upload the `training_manual` folder over `modules/training_manual/` without deleting existing uploads.
4. In Perfex CRM, run/allow the normal module upgrade so migration 136 is applied.
5. Clear application cache while preserving `.htaccess` and `index.html`; reset OPcache if available.

## Verify
1. Create a new Training Manual and Save. Confirm return to the Training Manuals dashboard.
2. Create a new Training Article and Save. Confirm return to the Training Articles dashboard.
3. Open the saved article from the dashboard and confirm it renders.
4. Publish an article and open its public link. Confirm the slug URL no longer returns 404.
5. Edit an existing manual/article and confirm existing edit behavior remains intact.
6. Confirm customer training-library routes still work.

## Rollback
1. Restore the backed-up `modules/training_manual/` folder.
2. No database rollback is required because migration 136 does not alter schema or content.
3. If desired, restore the database backup for a full point-in-time rollback.

## Validation
- PHP syntax validation passed for all module PHP files using PHP 8.4 CLI available in the build environment.
- Migration sequence validated: 101-136, no gaps or duplicates.
- English/Spanish language key parity validated: 106 keys in each file, no missing keys.
