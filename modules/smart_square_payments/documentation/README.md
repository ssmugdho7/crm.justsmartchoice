# Smart Choice Square Payments 1.2.2

Square invoice checkout for Perfex CRM 3.4.1. The module registers Square as an online payment gateway, supports USD invoices, stores credentials through Perfex encrypted gateway settings, and records completed provider payments in the CRM.

## Upgrade

Back up the CRM source and database before upgrading. Version 1.2.2 adds the missing `USD` currency setting without changing saved credentials, existing invoice choices, or payment history.

The customer checkout endpoint is:

`https://crm.justsmartchoice.com/smart_square_payments/charge`

## Configuration

In **Setup > Payment Gateways > Square**, configure the matching Application ID, Access Token, Location ID, and environment. Sandbox credentials must only be used with Sandbox Mode. Production credentials must only be used with Sandbox Mode disabled.

Square must also be selected under **Allowed Payment Modes** for an invoice. “Selected by default” applies to new invoices. Existing invoice choices are preserved during upgrades.

Never place access tokens or webhook signature keys in source control, logs, or public JavaScript. The Application ID and Location ID are intentionally passed to Square's browser SDK; the Access Token remains server-side.

## Verification

Use Square Sandbox test cards for a complete payment test. Do not use production cards for development verification. Confirm that a successful provider payment creates exactly one CRM payment record and one completed `smart_square_transactions` row.

On 26 October 2026, the actual payment controller was exercised in an isolated local fixture using authenticated Square Sandbox credentials: a USD 3.00 payment completed, Square's GetPayment endpoint confirmed `COMPLETED` and 300 minor units, a declined-card token returned failure, and repeated successful requests returned the same provider payment ID. The fixture replaced CRM persistence with a local test ledger; this does not certify production database posting or customer email delivery. The production token/location were checked read-only with Square's Locations API. No live charge was made.

The 1.2.2 follow-up limits idempotency keys to 40 characters (Square allows at most 45), retains decimal balances without a floating-point conversion, and renders checkout as a standalone responsive document. Gateway settings pass language keys to Perfex's native renderers.

## Branded checkout and receipts

Checkout shows the configured company logo, a centered heading, a light purple card, and the invoice billing snapshot with active primary-contact fallbacks. Customer values are escaped before display. English and Spanish keys cover all labels and explanatory text.

Customer receipts continue through Perfex's `invoice-payment-recorded` template and the customer's active invoice-email contacts. The Telegram module's existing `after_payment_added` hook remains responsible for Telegram delivery. Square adds a deduplicated administration receipt through `invoice-payment-recorded-to-staff` to the gateway's Administration payment receipt email (default `admin@justsmartchoice.com`). It reuses the payment PDF, skips a duplicate native owner notification, and isolates receipt exceptions from checkout. Review failed or pending `square_admin_email` notification ledger records if delivery requires investigation.

No migration is required for this follow-up. Deploy the complete changed files and retain existing encrypted gateway settings. Run `php tests/smart_square_payments_regression.php`, `php tests/smart_square_admin_receipt.php`, and `node tests/smart_square_checkout_flow.js`. These verify billing fallbacks, settings/language consistency, receipt recipient/PDF/deduplication, and success/error navigation. Provider settlement and inbox delivery require live provider evidence.

## Prime Shield checkout presentation

The shared checkout now follows the supplied Prime Shield reference: a local vector brand asset adapted from the website's existing contract logo, a payment-pattern background, blue payment button, customer summary, card-brand indicators, and responsive billing form. All invoices using this Square gateway receive the same presentation. The invoice document and actual customer records remain unchanged.

The billing-address Edit action focuses the payment-only address form. “Same as customer address above” copies the invoice's structured billing address; it does not substitute an address from a reference image. The customer must complete the billing fields and check the terms checkbox (unchecked by default). Square receives the validated optional `billing_address` object through CreatePayment; the server still derives the amount from the outstanding invoice balance. Old API callers without a billing address remain supported. After the first request, retries retain the same card token and complete payload, including the billing address.

Upgrade by deploying the module source and all three files in `assets/`. There are no database migrations or credential changes. Additional verification: `php tests/smart_square_billing_controller.php` exercises the real controller with an isolated Square transport fixture, covering address forwarding, amount integrity, invalid-input rejection, legacy callers, and invoice authorization. Browser checks cover local desktop/mobile layout, SDK attachment, address copying and edit focus; automated JavaScript checks cover form validation and unchanged retry behavior. These checks do not perform a production charge or prove inbox delivery.
