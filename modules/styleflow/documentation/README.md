# StyleFlow 1.1.0

StyleFlow manages PDF appearance for Perfex CRM invoices, estimates, and proposals.

## Upgrade
1. Back up the database and `modules/styleflow/`.
2. Upload the complete `styleflow` folder over `modules/styleflow/`.
3. In Perfex, open Modules and allow migration 001 to run.
4. Open StyleFlow and activate a template independently for Proposal, Invoice, and Estimate.
5. Open Setup > Settings > StyleFlow to edit template colors, fonts, styles, and document availability.

## Migration 001
Creates `tblstyleflow_templates` (using the active Perfex database prefix), seeds existing StyleFlow designs plus eight SC designs, and adds separate active-template options for invoices, estimates, and proposals. It does not delete or alter sales records.

## Rollback
Restore the backed-up `modules/styleflow/` folder and database backup. Do not manually delete the StyleFlow table if custom templates have been created.
