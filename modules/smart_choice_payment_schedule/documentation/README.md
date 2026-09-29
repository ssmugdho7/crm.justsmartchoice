# Smart Choice Sales & Payment Schedule

This module restores the missing payment-schedule dependency referenced by the CRM sales forms. It stores schedules in module-owned tables and keeps native Perfex invoices and payments authoritative.

## Accounting behavior
- One invoice remains the source of truth.
- Payments reduce the invoice balance normally.
- Installment rows track the planned schedule only.
- No duplicate revenue invoices are created.
