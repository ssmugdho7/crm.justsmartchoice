# Per-document sales prefixes

Existing invoices, estimates, credit notes, proposals and contracts expose **Edit prefix** on their edit forms. This action saves only the prefix. It preserves unrelated unsaved fields, numeric sequences, dates, record IDs, signatures and URLs. The caller must be a logged-in staff member with the module's edit permission.

The shared POST endpoint validates plain-text UTF-8 prefixes up to 50 characters. Empty prefixes and `0` are supported. Invoice, estimate and credit-note prefix/formatted-number updates commit together in a database transaction. The original invoice/estimate/credit-note endpoints delegate to the same validation and retain their legacy response type.

Proposals and contracts gain nullable `prefix` columns through the explicit additive migration. NULL proposals keep using the existing global proposal prefix; NULL contracts keep displaying their original numeric ID. Editing either stores an override for that record. Changing one record never changes a module default. Existing defaults remain configurable in Setup → Settings → Finance.

The saved number is used in form headings, table output, number searches, customer pages and PDF/email formatters. Contracts add a `{contract_number}` merge field while `{contract_id}` remains the raw ID. Proposal and contract table rows prime the request cache so numbering does not add one database read per row.

## Dev deployment

Back up the old revision and affected table schemas before pulling. Pull `origin/dev`, then run:

```sh
php scripts/migrations/sales_document_prefixes.php --apply
php tests/sales_prefix_database.php
php -l application/helpers/smart_choice_numbering_helper.php
```

The migration is CLI-only, idempotent, and restricted to localhost/dev.justsmartchoice.com. It adds two nullable columns without rewriting existing rows. For a code rollback, leave these columns in place; dropping them would discard saved overrides. Production was outside this task's deployment scope.

## Verification

- `php tests/sales_prefix_database.php`: real MySQL/CodeIgniter queries in temporary InnoDB tables; migration twice, persistence, four number formats, draft compatibility, permission/input failures, empty/zero/Unicode values, SQL/PHP formatting agreement, atomic rollback, actual editor rendering, both contract PDF templates and shared/legacy controller methods.
- `NODE_PATH=/tmp/prchat-audit-tools/node_modules node tests/sales_prefix_ui.js`: async save, CSRF token, text-only DOM updates, heading/breadcrumb/title sync, errors, Enter key and unsaved form preservation.
- Existing sales-heading, breadcrumb and theme-hook regressions and PHP/JS syntax checks.

Live browser verification requires local browser use to be enabled in Work settings. Do not infer browser coverage from these automated checks.
