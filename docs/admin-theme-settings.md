# Admin theme settings

Open **Setup → Theme Style → Admin theme**, choose **Emerald (current)** or
**Classic (previous)**, and save. The choice applies to all staff pages after
navigation/reload. Only administrators can change it. New installations and
unknown/removed theme keys fall back to Emerald.

The existing Theme Style page is reused. Its color editor remains available when
that module is active; the base theme selector works independently. Saved color
overrides are preserved. Customer pages, document styling, record permissions,
and module licensing are unchanged.

Shared repairs stay in `assets/css/custom.css`. Emerald's shared admin component
rules live in `assets/css/admin-themes/emerald.css`; Classic omits them and uses
the previous admin styling. Sales and licensed Customers/Projects/Expenses
presentation overrides follow the selected theme. Expense authorization remains
active for both themes.

Trusted installed modules can extend the `smartchoice_admin_themes` filter with
a key and `label` (translation key), `stylesheet`, and `modern` flag. Stylesheets
must be existing files matching `assets/css/admin-themes/[a-z0-9_-]+.css`.
Settings store a registered key, never a user-supplied CSS URL.

## Verification on 2026-10-31

- Read-only desktop shell audit attempted 235 admin navigation pages. 233 loaded
  with the shared header/sidebar/content wrapper and no document-level horizontal
  overflow. This covers navigation pages, not every record, modal, CRUD operation,
  or mobile state in every module.
- Two existing load failures were identified: Theme Style's missing helper and
  Staff Image Health's missing summary method. Both have local repairs. Staff
  Image Health also uses the shared responsive table wrapper and wraps long paths.
- Local browser previews verified both theme choices, a 390px mobile settings
  layout, and a centered dropdown caret inset 13px from the button's right edge.
- Theme settings tests cover persistence, admin/POST restrictions, invalid keys,
  future registration, local stylesheet restriction, and preserved expense access
  controls. Existing sidebar, Emerald, Sales, Customers, Projects and Expenses
  regression suites pass, including temporary database fixtures.

No deployment or production setting changes were performed for this task.
Changes are isolated in the `codex/admin-theme-settings-20261031` worktree.
