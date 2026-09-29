SmartSource Subcontractors Module v6
Install folder: public_html/crm.justsmartchoice/modules/smartsource_subcontractors/

Upgrade Fixes:
- Includes migration file 120_version_120.php and 130_version_130.php to fix the Perfex error: Migration can be found with the version 1.20.
- Adds Subcontractor Portal links so subcontractors can update their own profile and upload documents.
- Adds profile picture upload for subcontractors.
- Adds portal token and portal enabled fields.
- Adds staff integration columns for is_subcontractor and smartsource_subcontractor_id.
- Adds custom field dropdown support for Subcontractors and Subcontractor Contracts.
- Adds data table page length options for 10, 25, 50, 100, and All records.
- Keeps clean front-end labels without underscores.

After Upload:
1. Replace the existing smartsource_subcontractors folder.
2. Go to Setup > Modules.
3. Click Upgrade if shown, or Deactivate and Activate.
4. Open Subcontractors > Settings > Repair Database Tables.
5. Open Subcontractors > Subcontractor Portal to copy portal links.

Important:
This module does not bypass Perfex licensing or core validation.


Version 1.3.0 Notes:
- Template editor moved to full-width top layout.
- Template preview added.
- Portal links always generate secure tokens.
- Portal language selector added for English and Spanish.
- DBPR and county verification fields accept URLs with or without https://.
- Stats cards are clickable filters.
- Contract notification checkboxes added.


VERSION 2.1.0 NOTES
- Portal links now use the direct module controller URL to avoid 404 routes on shared hosting.
- Module navigation added to all admin pages.
- DataTables initialization made safe to prevent menu/page freeze.
- Contract action buttons reduced in size.
- Print button label corrected.
- Portal registration and profile notifications now use the CRM email library fallback.
- Placeholder templates are replaced during upgrade migration 2.1.0.


VERSION 2.6.0 FINAL SUCCESS PAGE FIX
- Portal submissions now redirect to profile/{token}?saved=1 to avoid 404 on direct success URL routes.
- Existing success URL method remains available as fallback.
