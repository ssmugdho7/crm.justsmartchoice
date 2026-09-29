# Solar Pro 1.0.6 Upgrade

1. Back up the database, `modules/solar_pro/`, and `uploads/solar_pro/`.
2. Replace the complete `modules/solar_pro/` directory with this package.
3. Open Setup > Modules and run the Solar Pro module upgrade if prompted.
4. Confirm Solar Pro migration 106 completes. It only adds missing Solar Pro columns/options; it does not change the CRM core migration version.
5. Clear application cache while preserving `.htaccess` and `index.html`, then reset OPcache.
6. Verify Dashboard, Analyses, New Analysis, Utilities, Equipment, Contracts, Contract Templates and Settings.

Rollback: restore the previous module folder and database backup. Do not delete Solar Pro tables or uploads.
