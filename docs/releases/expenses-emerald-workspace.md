# Emerald Expenses workspace

Expense actions, live cost summaries, receipts and recording forms for Perfex CRM 3.4.1.

## Delivered behavior

- Expense directory: five live cards, tax-inclusive outlay and billable share, searchable category and billing filters; View/Edit pills beneath Expense Name. Existing Delete, bulk actions, saved filters, all native columns and custom fields remain.
- Voucher: accounting information alongside authenticated image/PDF receipt previews. Missing files receive an honest storage warning. Other file types retain download access. Native invoice conversion, tasks, reminders, child expenses and recurring information remain.
- Recording form: Emerald styling and visible native optional fields, including recurring frequency, tax, payment mode, customer and project controls. Multipart uploads, CSRF and native validation remain.
- Categories: admin-only YTD expense count/outlay grouped by category and currency above the existing category CRUD table. Existing category records are not renamed or seeded.
- Permission-filtered top navigation and paired English/Spanish strings.

Amounts come from CRM data. The supplied ZIP's sample amounts, company details, Florida tax rate, itemized receipt and verification stamp are not inserted. Billed means a billable expense linked to an invoice, independently of payment; native paid/unpaid invoice badges remain. Summary currency/year controls are independent of the directory's filters, as explained in the UI.

## Source locations

All application changes are within the existing active `smart_choice_core_tools` module; no CRM core replacement or new activation is needed.

- `modules/smart_choice_core_tools/smart_choice_core_tools.php`: load the Expenses extension.
- `modules/smart_choice_core_tools/includes/expenses.php`: native view/row/SQL hooks, scoped assets, exact decimal aggregates, record and receipt authorization.
- `modules/smart_choice_core_tools/assets/css/expenses-custom.css`: ZIP palette/components plus scoped Perfex integration and responsive styles.
- `modules/smart_choice_core_tools/assets/js/expenses.js`: status-card actions, AJAX refresh state and workspace-only summary marker.
- `modules/smart_choice_core_tools/views/expenses/`: full module-owned native views (`manage.php`, `expense.php`, `expense_preview_template.php`, `manage_categories.php`, `expenses_total_template.php`) and `navigation.php`, `receipt_canvas.php`, `category_spending.php`.
- `modules/smart_choice_core_tools/language/{english,spanish}/smart_choice_core_tools_lang.php`: complete paired visible strings.
- `tests/expenses_workspace_regression.php`, `tests/expenses_workspace_ui.js`: temporary SQL fixtures and DOM interaction/control-preservation checks.

## Permissions and data

Uses the existing native Expenses View Own, View, Create, Edit and Delete registrations; it does not introduce duplicate capabilities or modify assigned permissions. Categories remain admin-only. View Own restricts summaries and record actions to the creator. Direct upload, download, copy, edit, delete and conversion routes validate record access; mutating receipt actions additionally require Edit. Native invoice Create is still required for conversion. Bulk access is checked before any mutation. Existing native file-owner/admin restrictions on receipt removal also remain.

No schema migration, data rewrite, settings change, dependency install or upload move is required.

## Upgrade and recovery

Deploy the listed files together using the normal dev Git fast-forward workflow. Keep Smart Choice Core Tools active and retain the native Expenses controller/views; they remain the fallback if this presentation extension is removed. After deployment, reload Expenses to pick up filemtime-versioned assets. No reinstall, module reactivation or permission reset is necessary.

The deployment helper creates a private tracked-source recovery archive outside the web root and verifies the server commit and public asset permissions. This is a source backup, not a database/upload backup. Do not reset over subsequent server changes; remove/revert this release through a reviewed follow-up commit if needed.

## Verification

Executed locally:

```sh
CRM_SITE_ROOT=/private/site/checkout php tests/expenses_workspace_regression.php
NODE_PATH=/tmp/prchat-audit-tools/node_modules node tests/expenses_workspace_ui.js
node --check modules/smart_choice_core_tools/assets/js/expenses.js
```

The SQL tests use connection-local TEMPORARY tables only: exact decimal/tax totals, tax 2 without tax 1, currency/year/customer/project/own scopes, invoice states, injection handling, record and bulk permissions, missing receipts, native view resolution and bilingual keys. DOM checks exercise status actions, refreshed KPI state, CSRF/filter payload preservation, controller isolation and preservation of native form controls. PHP syntax and Git whitespace checks are also required before release.

Live browser verification and deployment evidence are recorded in the delivery report. Customer expenses, receipts, invoices and categories are not created or deleted as a deployment smoke test. Test coverage does not assert that every external file or all accounting workflows have been exercised.
