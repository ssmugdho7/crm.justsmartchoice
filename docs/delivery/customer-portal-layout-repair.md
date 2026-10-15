# Restore the customer portal layout

Keep the existing sidebar and dashboard columns intact when global theme button styling is active.

- Compatibility: existing Smart Choice customer theme for Perfex CRM; target 3.4.1. The observed server asset version is 4.3.5; an independent 3.4.1 installation was not tested.
- Changed source: `assets/themes/smartchoice/css/customer-portal.css`.
- Cause: the Office Theme's emitted `body.customers button { display: inline-flex !important }` overrides hidden portal controls, including the backdrop's HTML `hidden` attribute. That backdrop enters the desktop grid before the sidebar, shifting the sidebar to the wide content column and the content to the narrow next row.
- Repair: scoped visibility rules take precedence over generic button styling. Hide inactive navigation controls and hidden backdrops; explicitly show the existing mobile open/close/backdrop controls only at their existing breakpoint. Preserve desktop collapse and hide the redundant desktop header hamburger.
- Database migrations: none.
- Navigation and permissions: existing links, active indicators, contact permissions and backend authorization are unchanged. No module, route, capability or activation changes.
- Languages: no user-visible strings added or changed.
- Installation: deploy the commit and reload the page. The existing theme asset registration uses file modification time to invalidate the CSS URL. No build or module reactivation is required.
- Preservation/rollback: no settings, customer records, uploads, credentials or dependencies changed. Revert this commit to restore the previous CSS.
- Tests: `php tests/customer_portal_render.php`, with `empty` and `restricted` scenarios, passed; `php tests/customer_portal_usability.php` passed; `git diff --check` passed.
- Browser verification: reproduced the defect with the existing CSS plus the actual conflicting generic button rule and equivalent portal markup. At 1280px, before: visible backdrop, sidebar 945px, content 244px on a later row. After: backdrop hidden, sidebar 244px and content 960px in the intended columns. Desktop collapse produced a 76px sidebar. At 800px and 390px, backdrop remained hidden while closed; mobile open displayed a fixed backdrop and visible drawer, hid desktop collapse, and Escape closed the drawer and hid its backdrop.
- Limitations: synthetic browser fixture uses the actual portal CSS and JavaScript, without customer data. Authentication, uploads, notification delivery, fresh installation and migrations were not exercised because this change only affects navigation control visibility. Production deployment is separate from the default dev release.
