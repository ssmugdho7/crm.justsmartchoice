Purchasing Hub v2.1.0 Clean Build

This package replaces the broken route/table structure with a cleaner Purchasing Hub module.

Main fixes:
- Uses its own tables: tblpurchasing_hub_items, vendors, orders, bills, quotes, contracts.
- Does not use old CRM inventory/automobile item groups.
- Front-end labels are human-readable and capitalized.
- No visible raw underscores for Purchasing Hub labels.
- Vendors, Items, Reports, Purchase Orders, Accounts Payable, Vendor Quotes, and Contracts route to real pages.
- Item image upload uses module uploads/item_images folder with 5 MB max.
- Settings, Health Check, and Help Guide are added inside CRM Settings.
- Reports are moved to Purchasing Reports menu and use QuickBooks-style purchasing terminology.

Install:
1. Backup database and modules/purchasing_hub first.
2. Replace modules/purchasing_hub with this folder.
3. Activate or run Upgrade Database.
4. Clear application/cache except index.html.
5. Press Ctrl + F5.
