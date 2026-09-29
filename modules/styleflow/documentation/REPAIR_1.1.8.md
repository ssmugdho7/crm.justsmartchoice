# StyleFlow 1.1.8 Repair

## Scope
- Preserve all existing StyleFlow templates, assignments, staff-photo settings, and sales data.
- Fix raw sales-table language keys/underscores in styled proposal/estimate/invoice output.
- Fix CRM Settings right-panel registration at `admin/settings?group=styleflow` using a direct module view.
- Keep the module Settings button as a compatibility redirect into CRM Settings.
- Add full-page preview images for bundled Smart Choice designs so template cards use the same preview format as legacy templates.

## Database
Migration 118 is code-only. It updates the StyleFlow version option only and does not modify template rows or sales records.

## Rollback
Restore the previous `modules/styleflow/` backup. No database rollback is required because migration 118 does not alter schema or business records.
