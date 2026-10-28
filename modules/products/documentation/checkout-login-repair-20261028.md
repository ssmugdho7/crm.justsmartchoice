# Guest checkout returns after customer login
Preserve the session cart and return the authenticated customer to checkout.

## Repair
Guest checkout previously redirected to the site root without saving its destination. It now stores the fixed same-site `products/client/place_order` URL in Perfex's native `red_url` session key and opens customer login directly. Native authentication preserves the existing session data while rotating the session ID and consumes the return URL after successful login. No user-supplied redirect is accepted.

The existing server-side customer ownership, pricing, stock, tax, payment, CSRF and login validation remain in place. The cart is still cleared only by the existing successful-order path (or disabled-store behavior). No CRM core files, settings, permission registration, language keys or schema changes are needed. Existing bilingual login messages are reused.

## Complete source / upgrade
Deploy the updated `modules/products/controllers/Client.php` with the normal module source update. No migration or activation step is required. The new `tests/products_checkout_login_regression.php` executes the actual checkout controller, authentication model, login controller and native redirect helper against isolated fixtures, without creating accounts, invoices, orders or emails.

## Validation
- The new regression reproduced the old guest redirect failure before the repair.
- All 13 guest/login checkout checks pass: trusted destination, cart/variation quantities, invalid-password preservation, successful login redirect, session rotation, one-time redirect consumption, signed-in checkout and empty-cart handling.
- All 122 existing product permission, cart, checkout ownership and order-validation checks pass.
- Live authenticated browser verification requires a customer sign-in; no order submission or payment is used as a test.
