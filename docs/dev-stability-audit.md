# Development stability and permission audit

These changes target reproduced defects in Online Shopping and staff Meetings. They require no API credentials, service setup, schema migration, or new database. They do not grant permissions automatically or change customer authentication, core CSRF handling, payment processing, customer meeting ownership, or production configuration.

| Area | Before | After |
| --- | --- | --- |
| Product roles | Edit/Delete could not be assigned; several write endpoints checked View or Create instead. | Standard View/Create/Edit/Delete capabilities are registered and enforced on catalog, categories, variations, coupons, notifications, popups, reviews, upsell rules and gift-card templates. |
| Product controls and navigation | Edit depended on Delete; read-only users saw write buttons and POS links they could not use. | Edit and Delete controls are independent; POS/menu links and write controls follow the corresponding grants. Form option lookup still works for creators and editors. |
| Product reports | Order reports/history and staff-order lookups lacked consistent checks; custom report names/dates were concatenated into SQL, breaking quoted names and allowing crafted filters. | Reports require View; staff POS helpers require Create. Custom report filters use escaped query-builder values, validated dates in the CRM date format and clear JSON errors. |
| Cart shortcuts | A new product was never inserted; scalar shortcut IDs and legacy carts could produce warnings. | Shortcuts accept one or multiple product IDs, insert/increment the base item, and keep variation quantities separate. Legacy variation keys and sparse cart indexes are handled. |
| Customer checkout ownership | A submitted customer ID selected the account to invoice. | Customer checkout derives its invoice owner from the logged-in session. Staff POS retains its authorized customer selection. |
| Product options | An option belonging to another product could supply the price/stock; deleted products/options caused null dereferences. | Options must belong to the selected product. Unavailable lines fail before invoice creation. Valid prices still come from the database. |
| Order validation | Missing/malformed lines, nonpositive quantities and missing customers could crash or reach invoice/stock operations. | Invalid lines/customers are rejected before persistence. Fractional quantities are retained when valid and checked against stock without integer truncation. Recurring invoice behavior is retained. |
| CSV imports | Rows wider than the header could cause `array_combine` errors. | Invalid headers are rejected; oversized rows are skipped with a warning while valid rows import. CSV parsing explicitly retains the existing escape character. |
| Coupon/category stability | Coupon deletion redirected to the wrong route; editing a deleted category dereferenced null. | Deletion returns to the module's coupon list; stale categories return an unsuccessful JSON response. |
| Stock test URL | An old controller test action updated stock for hard-coded invoice 45. | The action returns 404 without touching stock. |
| Meeting role registration | Capabilities used an incompatible role-editor structure, with an unrelated templates capability. | Native meeting View Own/View/Create/Edit/Delete capabilities are registered; the unused templates entry is removed. |
| Meeting access | Staff endpoints, reports, exports, counters and bulk deletion lacked consistent capability/ownership checks. | Backend checks distinguish read/create/edit/delete. View Own includes meetings created by, assigned to, or attended by that staff member; direct IDs, filters and deletion batches cannot broaden access. Global View retains global access. |
| Meeting settings | Ordinary meeting users could access integration settings and test-notification actions. | The existing native Settings View/Edit grants control settings, health and notification-test actions. Menu links match those checks. |
| Meeting save feedback | Failed updates/status changes and empty or failed comments could report success and write logs. | Failure returns false with an error alert; success is reported only after a successful write. Missing meetings cannot accept a shared-link update. |

## Verification

- `php tests/products_stability_regression.php`: 122 checks using real controller/model code and inert persistence fixtures; includes valid base/variation invoices, staff-selected customers, recurring invoices, malformed checkout input, CSV import, stale categories, and permissions.
- `php tests/meetings_permissions_regression.php`: 77 checks for registration, allowed/denied routes, owner/assignee/attendee scope, settings, filtering, batch deletion and successful/failed saves.
- `php tests/catalog_permission_controls.php`: 40 checks rendering native catalog controls with separate View/Edit/Delete grants.
- `CRM_SITE_ROOT=/path/to/private/checkout php tests/catalog_meetings_database_regression.php`: 28 checks running the native CodeIgniter/MySQL queries on Bluehost against connection-local `audit_` TEMPORARY tables only. Fixture table metadata and identical copies account for MySQL's temporary-table listing and repeated-join limitations. Real customer, staff and application tables are not modified.
- Product permission and meeting registration checks fail against the previous source, confirming the original defects.
- Existing chat/calls, role-loader, staff saves, CAPTCHA, notes route, proposal PDF, customer profile/account/help-library/meeting and catalog/language interaction regressions pass.
- Initial syntax sweep: 798 first-party PHP files passed. Changed PHP files were checked again after edits.

## Rollout and limits

Push to the original `origin/dev` only. Main, the alternate remote and website checkouts remain unchanged. No deployment is part of this change.

This is a targeted audit, not a claim that every endpoint in every installed third-party module is defect-free. Authenticated browser workflows using real staff/customer accounts were not exercised in this run. Existing permissions determine access; administrators can assign the corrected capabilities through the existing role editor. Integration-dependent messaging/notification delivery and concurrent stock reservations are outside this patch.
