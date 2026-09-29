# Solar Pro – Design, Savings & Proposal System

**Version:** 1.1.0  
**Author:** Smart Choice Contractors USA / Harold Cabrera  
**Compatibility target:** Perfex CRM 3.4.x, PHP 8.5+, MySQL 5.7+

Solar Pro adds a solar opportunity workflow to Perfex CRM: property analysis, Google Solar Building Insights integration, fallback panel-production sizing, Florida utility/rate administration, equipment records, 12-month production and savings modeling, CRM lead creation, customer-facing reports, contracts, initials, and electronic signatures.

## Installation

1. Back up the complete CRM database.
2. Back up the complete CRM file tree, including `modules/`, `uploads/`, and `application/config/`.
3. Extract the ZIP so the module is located at `modules/solar_pro/`.
4. In Perfex CRM, open **Setup → Modules**.
5. Activate **Solar Pro – Design, Savings & Proposal System**.
6. Open **Setup → Settings → Solar Pro** and configure Google/Enphase credentials and pricing assumptions as required.
7. Open **Solar Pro → Utilities** and create effective-dated utility rate plans before using utility-specific financial proposals.
8. Open **Solar Pro → Equipment** and confirm the panels/microinverters sold by the company.
9. Assign Solar Pro permissions to staff roles.
10. Test the public calculator at `/solar_pro/estimate` if enabled.

## Database and upgrade safety

- Initial activation creates only missing `solar_*` module tables.
- Existing CRM data is not deleted or rewritten.
- Existing Solar Pro options are preserved because defaults are created with `add_option()`.
- Uninstall/deactivation preserves Solar Pro tables, reports, API configuration, signatures, and documents.
- Migration sequence starts at `001_version_001.php` and future migrations must be append-only with no gaps or duplicate numbers.
- This CRM core accepts exactly three-digit module migration numbers. Solar Pro versions must therefore map to a three-digit dotless value (for example `1.1.0` → `110`). Do not use a release such as `1.0.10`, which would incorrectly map to unsupported migration `1010`.

## Google Solar API

Version 1.0.5 uses server-side Google Geocoding and the Solar API `buildingInsights:findClosest` endpoint when API credentials and coverage are available. When Google data is unavailable, the module automatically uses the configured net daily-production assumption for 400 W or 440 W panels.

Google Maps Platform billing and the Solar API must be enabled on the Google Cloud project. Follow Google attribution and data-display policies when expanding imagery/data-layer displays.

## Enphase

Version 1.0.5 includes Enphase equipment records and protected configuration fields for Enphase API v4 credentials. Production monitoring synchronization is intentionally staged for a later migration because Enphase API v4 authorization requires OAuth 2.0 and application/system-owner authorization.

## Utility rates

The module seeds utility company names only. It does not hard-code current Florida tariffs. Add effective-dated rate plans for customer charges, energy/fuel rates, taxes, minimum bills, export credits, and net-metering assumptions. This prevents a future rate change from silently altering historical proposals.

## Contract signing

Contracts receive unique public tokens. Required initial fields use `{solar_initial:key}`. The default contract contains `{solar_initial:scope}` and `{solar_signature}`. The signature record stores signer name/email, signature data, IP address, user agent, timestamp, and a SHA-256 document hash. Signed contracts are not edited by the signing endpoint.

## Rollback

1. Deactivate Solar Pro in **Setup → Modules**.
2. Restore the pre-install file backup if necessary.
3. Restore the database backup only if a full rollback is required.
4. Do not manually drop Solar Pro tables if signed documents or reports must be preserved.

## Verification checklist

- Module activates without migration errors.
- Solar Pro appears in the admin menu.
- Solar Pro appears as a clickable section under Setup → Settings.
- English and Spanish language files load with identical keys.
- Staff permissions can be assigned.
- New analysis saves and opens a report.
- Missing Google key falls back to configured panel-production assumptions.
- Google Solar API analysis works when valid credentials and building coverage exist.
- Utility rate plans save with effective dates.
- Customer report public token opens.
- Contract link opens, initials can be drawn, and signature can be submitted.
- Existing CRM customers, leads, uploads, settings, API keys, and modules remain unchanged.

## Changelog

### 1.0.3

- Initial production package.
- Solar analysis dashboard and calculator.
- Google Geocoding/Solar Building Insights integration with logged API responses.
- 400 W/440 W fallback sizing.
- 12-month energy and bill-savings model.
- System price per panel or per watt.
- 25-year configurable escalation/degradation model.
- Florida utility directory and effective-dated rate plans.
- Panel, inverter, and battery equipment administration.
- Perfex lead creation from solar inquiries.
- Public solar calculator and customer report.
- Customer portal report list.
- Solar contract creation, required initials, signature, audit data, and document hash.
- Smart Choice orange/blue/green branded responsive UI.
- English and Spanish language files.


## v1.0.5 CRM compatibility repair
Solar Pro does not define its own CRM core `migration_version`; its HMVC migration bridge dynamically imports the authoritative CRM core migration configuration. Module database upgrades are handled by Perfex `App_module_migration` and run only against the Solar Pro module version. Opening Solar Pro therefore loads the module dashboard instead of invoking the CRM core database-upgrade screen. Solar Pro Settings are registered inside the native CRM Settings page under General and use the `solar_pro` settings group.
