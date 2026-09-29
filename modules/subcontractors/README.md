# Smartsource Subcontractors 3.2.2

Compatibility: Smart Choice CRM 3.8.8 / Perfex CRM 3.4.x, PHP 8.5+, MySQL 5.7+.

## Upgrade
1. Back up the CRM database.
2. Back up `modules/smartsource_subcontractors/` and `uploads/smartsource_subcontractors/`.
3. Replace only `modules/smartsource_subcontractors/` with this package.
4. Open Setup > Modules and run the upgrade to 3.2.2.
5. Clear CRM cache and PHP OPcache.

Do not rename the folder. Do not delete existing tables or uploads.


## Version 3.2.9
- Repaired portal registration and document persistence.
- Added native staff account provisioning and Subcontractors role linkage.
- Added native welcome email template delivery.
- Removed delayed template editor display.
- Added CRM logo branding, module footer, left-aligned company/template names, and expanded bilingual help text.

## Version 3.3.0
- Preserves submitted text values after validation or security failures.
- Adds clear top-level and field-level validation messages.
- Adds responsive iPhone and mobile portal layout.
- Adds the CRM-branded portal footer and employee-login access.
- Adds a lightweight looping logo particle animation with reduced-motion support.
- Keeps all existing registration, staff provisioning, uploads, templates, settings, and data intact.
