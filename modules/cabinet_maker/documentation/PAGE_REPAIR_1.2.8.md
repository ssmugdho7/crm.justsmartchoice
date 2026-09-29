# Cabinet Maker 1.2.8 Page Repair

This release preserves the successful 1.2.7 activation workflow and repairs runtime page failures.

- No schema repair or catalog seeding runs during ordinary page requests.
- Designs, Materials, Vendors, Vendor Catalog, Offcut Inventory, Settings, and Health Check use safe table checks.
- Legacy/incomplete rows receive display defaults instead of causing HTTP 500.
- Unsupported `e()` calls were replaced with Perfex-compatible `html_escape()`.
- Optional catalog data is not required for any page to open.
- Migration 128 is idempotent and non-destructive.
