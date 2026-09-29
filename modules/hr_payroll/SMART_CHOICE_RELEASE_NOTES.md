# Payroll Hub 1.2.6

- Rebuilds Settings integration using a first-level native Settings view (`hr_payroll/payroll_hub_settings`) for Smart Choice CRM right-panel rendering.
- Adds a direct full-page fallback at Payroll Hub > Payroll Hub Settings.
- Adds configurable Payroll Hub colors, table appearance, density, row hover/borders, rows per page, checkbox/actions visibility, and sidebar section visibility.
- Preserves existing payroll data and advanced configuration screens.
- Migration 126 adds only option defaults; no destructive schema changes.

# Payroll Hub 1.2.5

- Hard-fixes the existing `Payroll Hub` label in Setup > Settings so it is clickable even when the custom CRM renders it as plain text or wraps it in spans/icons/list items.
- Uses an inline, dependency-free Settings bridge loaded directly through `app_admin_head`, avoiding failures caused by deferred or blocked external module scripts.
- Click loads the Payroll Hub settings fragment into the normal right-side Settings content panel; direct `?group=payroll_hub` remains supported.
- Adds a real `Settings` child under the Payroll Hub sidebar as a second access path.
- Preserves dark-green Smart Choice module menu branding and all existing payroll data/configuration.
- Migration 125 is non-destructive.

# Payroll Hub 1.2.4

- Native Smart Choice CRM Settings navigation repair.
- `Payroll Hub` now registers an explicit `href` / `url` to `admin/settings?group=payroll_hub`, matching the working right-panel Settings pattern used by Smart Choice module settings.
- The registered view remains `hr_payroll/settings/payroll_hub_settings`, so the CRM Settings controller renders Payroll Hub in the same right-side content area as General and Company Info.
- Browser bridge retained only as a secondary fallback for dynamically rebuilt custom Settings navigation.
- Dark-green Smart Choice Payroll Hub menu branding preserved.
- Migration 124 is non-destructive.
