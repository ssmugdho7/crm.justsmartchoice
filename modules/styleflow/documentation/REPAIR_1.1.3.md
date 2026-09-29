# StyleFlow 1.1.3

## Repair
- Fixes HTTP 500 when Perfex runs the module database upgrade.
- Migration 113 performs version bookkeeping only.
- Historical migration files remain present but no longer execute schema repair or template seeding during database upgrade.
- Schema creation/template seeding remains in install.php for activation/fresh installation.
- No invoice, estimate, proposal, template, setting, or CRM record is removed.

## Upgrade
1. Back up the database and `modules/styleflow/`.
2. Upload the complete `styleflow` folder over the existing module.
3. Open Modules and run the database upgrade.
4. Confirm StyleFlow reports version 1.1.3 and opens normally.

## Rollback
Restore the database and previous `modules/styleflow/` backup.
