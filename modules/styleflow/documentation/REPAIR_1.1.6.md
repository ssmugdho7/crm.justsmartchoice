# StyleFlow 1.1.6 Repair

## Scope
Repair only: restore bundled templates when missing, repair StyleFlow Settings navigation, expose settings in a dedicated module page and CRM Settings view, and preserve independent template assignment for invoices, estimates, and proposals.

## Data safety
- Existing template rows are never overwritten.
- Missing bundled template rows are inserted by slug only.
- Existing active template selections are preserved.
- No sales documents, uploads, API keys, or CRM settings are deleted.

## Upgrade
1. Back up database and `modules/styleflow/`.
2. Replace the module files with this package.
3. Run the normal Perfex module upgrade to migration 116.
4. Open StyleFlow > Manage Templates and StyleFlow > Settings.
5. Also verify Setup/Settings > StyleFlow renders the same settings table where supported by the CRM settings shell.

## Rollback
Restore the previous module files and database backup. Do not delete the StyleFlow templates table.
