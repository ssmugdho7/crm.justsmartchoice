Smart Choice CRM Core Repair

Included repairs:
- Sales document views no longer call the unsupported e() helper in the custom payment blocks.
- Estimate, proposal and invoice custom payment fields render safely when the optional metadata table is absent.
- Project PDF preview uses the canonical project-file URL and includes an inline fallback.
- Customer portal logo uses the navigation height and Login is moved to the final menu position for logged-out visitors.
- Standard CRM tables and Projects table are normalized without module-wide DataTable overrides.

Optional database action:
Run add_cash_expense_payment_mode.sql in phpMyAdmin after reviewing the table prefix.
