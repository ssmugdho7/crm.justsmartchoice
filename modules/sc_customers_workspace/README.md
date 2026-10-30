# Smart Choice Customers Workspace

## Executive Emerald Customer Experience

This presentation module applies the supplied emerald and white customer design to the native Perfex CRM customer directory, customer profiles, create/edit form, contacts directory and contact modal. It preserves native records, custom fields, filters, bulk actions, tabs, CSRF handling, uploads and backend endpoints.

The module adds a Customers/Contacts top navigation, styles the six existing live summary cards, keeps row actions visible, improves table hierarchy, styles the native customer profile tabs and form controls, and presents the native contact modal as an accessible focused dialog. Screenshot names, counts and customer values are reference content and are never inserted into the CRM.

## Permissions, licensing and compatibility

The workspace targets Perfex CRM 3.4.1 and reuses the native Customers permissions: View Own, View, Create, Edit and Delete. It adds no controller or public API and does not change authorization. Native controllers continue to enforce record scope and every mutation. New visible strings have English and Spanish translations.

Activation requires the existing owner-controlled Smart Choice Module Licensing system and product ID `sc_customers_workspace`. If the license is missing or expires, this presentation module remains inactive and the native Customers area continues to work unchanged.

## Install, upgrade and rollback

1. Back up source, database and uploads using the normal site process.
2. Place this directory at `modules/sc_customers_workspace/`, or install its complete ZIP through Setup → Modules.
3. Configure `sc_module_licensing`, activate a valid `sc_customers_workspace` company license, then activate this module.
4. Verify `/admin/clients`, `/admin/clients/all_contacts`, a customer profile, customer edit, and add/edit contact with accounts representing assigned-only and global customer access.
5. To roll back, deactivate this workspace. No customer data, permissions, uploads or settings are removed. No database migration is required.

## Source files

- `sc_customers_workspace.php`: module and license activation registration.
- `includes/customers.php`: route scoping, assets and contact row hooks.
- `assets/css/customers.css`: responsive emerald presentation scoped to Customers pages.
- `assets/js/customers.js`: translated top navigation and progressive DOM enhancement.
- `language/{english,spanish}/sc_customers_workspace_lang.php`: paired language keys.
- `smart_choice_module_manifest.json`: compatibility and impact declaration.

## Verification

Run PHP syntax checks for the module, `node --check assets/js/customers.js`, Git whitespace checks, and the repository customer workspace regression scripts. Live activation and write-path checks require an administrator, a valid module license and suitable customer fixtures.
