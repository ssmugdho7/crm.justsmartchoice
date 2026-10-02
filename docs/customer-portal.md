# Customer Portal

The authenticated Smart Choice theme uses the customer demo's grouped navigation,
compact metrics, project progress, billing snapshot, and resource shortcuts. All
information comes from the CRM. Demo identities, balances, supervisor details,
and simulated payment/signature dialogs are not shipped.

The existing head, navigation templates, and footer are unchanged. Portal CSS is
scoped to the authenticated content wrapper. Guests and public invoice, estimate,
contract, and proposal views with navigation disabled keep their original layout.
The authenticated Solar Pro report list now renders through the customer theme;
its existing lookup and public report routes are unchanged.

## Behavior

- Sidebar links use the existing permission-filtered menu and enabled modules.
  Header-hook links are copied only when already rendered and same-origin.
- Desktop navigation can collapse. Mobile navigation supports Escape, focus
  return, keyboard focus containment, and an inactive closed drawer. Without
  JavaScript, the navigation remains visible.
- Project queries are scoped to the current customer and limited to three rows.
  Invoice draft visibility, hidden contracts, and contact-specific ticket access
  follow the existing customer routes.
- Payments, signatures, uploads, bookings, table sorting/search, and forms use the
  existing CRM pages and handlers. There are no authentication or database changes.
- CSS and JavaScript are registered directly with file modification versions, so
  production cannot select a stale minified portal copy.

## Verification

Run the isolated rendering checks without an application session or database:

```sh
php tests/customer_portal_render.php full
php tests/customer_portal_render.php restricted
php tests/customer_portal_render.php empty
node --check assets/themes/smartchoice/js/customer-portal.js
```

Before promoting staging to main, sign in with a test contact and verify the
dashboard, projects, invoices, estimates, contracts, proposals, tickets, files,
calendar, knowledge base, appointments, meetings, help library, and Solar Pro.
Repeat with a restricted contact and a customer with no records. Check desktop,
tablet, and phone widths, invoice search/sorting, mobile drawer keyboard handling,
and the unchanged header/footer. Transactional payment/signature submissions
should use the site's test payment environment.

Development changes belong on `staging`; promotion to `main` requires an explicit
request. A staging push does not update the live deployment that follows main.
