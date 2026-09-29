# StyleFlow 1.1.4

## Critical repair
- Removed `config/migration.php`. That file belongs to CodeIgniter/Perfex core migrations and was overriding the CRM global migration target with StyleFlow's module version.
- This caused the false CRM message comparing StyleFlow files version 1.1.3 with CRM database version 4.0.0.
- StyleFlow now uses only native `App_module_migration` files under `modules/styleflow/migrations/`.
- Added module migration `114_version_114.php` for version 1.1.4.
- Added a page-scoped, idempotent database readiness check. It only repairs StyleFlow's own table/options when a StyleFlow page is opened and the table is missing; it does not alter Perfex core migration configuration.

## Data safety
No CRM core tables, invoices, estimates, proposals, customers, uploads, or existing StyleFlow records are removed.

## Deployment
1. Back up the database and `modules/styleflow/`.
2. Replace the existing StyleFlow module folder with this package.
3. Clear `application/cache/` generated files but keep `.htaccess` and `index.html`.
4. Open Modules and run the StyleFlow module upgrade if Perfex offers it.
5. Open StyleFlow. The CRM core database-upgrade screen must not appear.
