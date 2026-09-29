# Solar Pro 1.1.0 Upgrade

## Purpose
This release corrects the Perfex module migration version mapping while preserving all Solar Pro 1.0.10 features and data.

## Why 1.1.0
The CRM core removes dots from the module Version header and supports exactly three-digit migration filenames. `1.1.0` therefore maps to migration `110`, which exists as `migrations/110_version_110.php`. A header of `1.0.10` incorrectly maps to `1010`, which the CRM migration loader cannot recognize.

## Upgrade
1. Back up the CRM database.
2. Back up `modules/solar_pro/` and `uploads/solar_pro/`.
3. Replace the complete `modules/solar_pro/` directory with this package.
4. Open Setup → Modules and run the Solar Pro database upgrade if prompted.
5. Confirm the installed version becomes 1.1.0.
6. Open Dashboard, Analyses, New Analysis, Utilities, Equipment, Contracts, Templates, Proposals, and Solar Settings.

## Data safety
Migration 110 is non-destructive. Existing analyses, proposals, contracts, templates, equipment, API credentials, uploads, settings, signatures, and customer data are preserved.

## Rollback
Restore the pre-upgrade module files and database backup. Do not uninstall the module to roll back.
