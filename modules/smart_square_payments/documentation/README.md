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
