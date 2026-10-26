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
