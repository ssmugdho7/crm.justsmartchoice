# Solar Pro 1.0.5 Upgrade

1. Back up the database and the complete `modules/solar_pro/` directory.
2. Replace the complete Solar Pro module directory with this release. Do not merge only selected files.
3. Confirm `modules/solar_pro/config/migration.php` exists. It is intentionally a bridge to `application/config/migration.php`; it contains no hard-coded CRM migration version.
4. Open Setup → Modules and run the Solar Pro module upgrade if Perfex requests it.
5. Open Solar Pro Dashboard, Analyses, New Analysis, Utilities, Equipment, and Contracts. None of these routes should invoke the CRM core database-upgrade screen when the CRM core database is already current.
6. Open Setup → Settings → Solar Pro and verify the settings panel renders on the right side.

Migration 105 is module-only and non-destructive. Roll back by restoring the backed-up Solar Pro directory and database backup.
