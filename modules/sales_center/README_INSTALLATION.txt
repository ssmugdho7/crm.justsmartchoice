SMART CHOICE SALES CENTER 1.3.5
Perfex CRM 3.4.1 / PHP 8.5 compatible module package

FOLDER TO UPLOAD:
public_html/crm.justsmartchoice/modules/sales_center/

IMPORTANT:
Do not upload this module as sales_center_v134. The folder must be exactly sales_center.
This build removes the mixed future migration jump files and keeps the module aligned as version 1.3.5.

WHAT THIS BUILD DOES:
1. Repairs the database through install.php and migration 135.
2. Preserves existing Sales Center records.
3. Uses native Perfex CRM records for proposals, estimates, invoices, payments, and credit notes.
4. Does not create duplicate proposal, estimate, invoice, payment, or credit note documents.
5. Adds the Sales Documents area to link native CRM sales records to sales representatives and commission tracking.
6. PDF and Download buttons redirect to native Perfex PDF endpoints.
7. Send Email buttons use the native Perfex send email modal when JavaScript is available and fall back to the native send_to_email URL.
8. Includes Health Check and Repair Database actions under Sales Center Settings.

INSTALL / REPAIR STEPS:
1. Back up the CRM files and database.
2. Delete or rename the broken folder public_html/crm.justsmartchoice/modules/sales_center_v134/ if it exists.
3. Upload the sales_center folder from this ZIP into public_html/crm.justsmartchoice/modules/.
4. Go to Setup > Modules and activate or upgrade Smart Choice Sales Center.
5. Go to Sales Center > Settings and click Repair Database Tables.
6. Open Sales Center > Sales Documents.
7. Test Open, PDF, Download, and Send Email using an existing proposal, estimate, invoice, and credit note.

IF UPGRADE IS STILL STUCK AT 130:
1. Run SQL_REPAIR_135.sql in phpMyAdmin after confirming your table prefix is tbl.
2. Return to Sales Center > Settings and click Repair Database Tables.
3. Clear CRM cache and browser cache.

EMAIL NOTE:
This module uses the CRM email configuration. If the native modal opens but messages do not arrive, check Setup > Settings > Email, SMTP credentials, SPF/DKIM/DMARC, and Perfex application logs.


Version 1.3.6 Notes:
- Error 130 migration rescue added with continuous migrations 100 through 136.
- Quality ★★★★★ retained in module metadata.
- Compact Import, Sample Header, Export, Refresh, and Massive Delete controls added to table pages.
- Sales Documents, Sales Reports, Salespersons, Contracts, and Templates support safe CSV import/export workflow.
- Native CRM Proposals and Estimates pages receive compact toolbar injection when the module assets are loaded.
- Use CSV sample headers before importing. Excel files should be saved as CSV before import.
