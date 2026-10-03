# Development performance audit — 2026-10-03

Scope: customer portal and shared staff administration work. No schema migrations,
new configuration, authentication changes, or production promotion.

## Changes and measured before/after behavior

| Area | Before | After |
|---|---|---|
| Catalog initial HTML | Unused full catalog/options load plus one count per category: 230 reads on dev data | Two lightweight category reads; catalog remains loaded through its existing AJAX endpoint |
| Catalog products/options | One product read plus one variation read per product: 191 reads for 190 products | Two reads total, preserving product fields, option associations, category filters, array/object return types and variation-group ordering |
| Help Library | Books read plus articles/staff join per book: 18 reads for 17 visible books; full article and mind-map bodies materialized | Two reads; card metadata only, with published/customer-visible constraints and article-detail route unchanged |
| Customer permissions | Every repeated check issues a count | One request-local read per contact, including cached denials; core and active supplier contact mutation paths invalidate that contact's entry |
| Customer dashboard totals | Separate invoice/project totals and per-status counts | One grouped read per entity, reused by dashboard widgets; standalone summary widgets also use grouped reads |
| Staff project chart | A UNION branch rescans projects and membership for each status | One grouped scan using the same global-versus-member scope; absent statuses still show zero |
| Staff debug settings | Eleven option-existence counts on every admin request | One read, no writes for existing settings; missing defaults still self-heal and saved/empty values remain unchanged |
| Catalog rendering | HTML escaping creates a temporary DOM element for every field and option | Equivalent string escaping without per-field DOM allocation; existing card, dropdown and cart behavior retained |

Read-only development database measurements (five iterations, median; not end-to-end page times):

- Catalog data: 15.670 ms → 3.898 ms.
- Initial catalog HTML data reads: 15.988 ms → 0.075 ms.
- Help Library reads: 1.625 ms → 0.464 ms.
- Materialized Help Library result: 219,908 → 14,345 bytes. This is model data,
  not an HTTP transfer-size measurement.
- Forty permission checks measured 2.453 ms versus 0.059 ms for one permission read;
  this is a small shared improvement, not an explanation for seconds of delay.

The original variation query only sorted by `variation_id`; ordering tied values
was not guaranteed. Real-data comparison normalizes ties by ID to verify every
product/option field and association. Variation groups retain their original sort.

## Verification

`tests/performance_database_regression.php` runs native CodeIgniter/MySQL queries
against connection-local `audit_*` TEMPORARY tables only. It checks bounded query
counts, customer isolation, private/unpublished/empty Help Library cases, plugin
permission definitions, grant/revoke/delete/fresh-request behavior, invoice
cancelled/draft denominators, staff global/member scopes, duplicate memberships,
empty statuses, and debug default preservation.

Existing catalog permissions, checkout ownership, cart/order validation, Meetings
permissions/ownership, chat access, role-loader races, staff saves, reCAPTCHA,
proposal PDF, Help Library rendering, customer forms and dashboard populated,
empty and restricted render checks passed. Changed PHP files are syntax checked.
Catalog browser verification includes all 190 cards and existing search/options.

Protected customer and staff page timing needs an authenticated browser session;
this session opens the login page. No customer account/password/permission was
changed to obtain access. The improvements above remove demonstrated work, but
asset/network/plugin costs may still affect overall page times. Do not promote
these changes to production based solely on these database benchmarks.

## Development rollback

Before pulling, back up the changed tracked source files and record the previous
checkout SHA in the private Bluehost `codex-backups` directory. Preserve ignored
configuration/uploads and unrelated untracked files. Roll back the development
checkout to that recorded SHA if verification fails; no database rollback is
required for this change because it has no migration.
