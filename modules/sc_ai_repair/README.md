# Smart Choice AI Repair 1.0.2

Controlled Perfex CRM diagnostics and repair planning with OpenAI, file backups, PHP syntax validation, reports, asset rename/conversion, reference updates, and rollback.

## Version 1.0.2

- Configurable CRM root folder with Perfex structure validation.
- Separate CRM website URL and AI provider API base URL.
- OpenAI provider explanation and protected API-key view/copy controls.
- Safe 2 MB maximum per-file AI context limit.
- Vendor-code scanning explanation and optional scan behavior.
- Wider responsive rename/convert controls.
- Repair-history report modal and permission-controlled delete action.
- Existing repair backups are preserved when a history row is deleted.

## Installation

Upload the `sc_ai_repair` folder to `modules/`, activate the module, then open **AI CRM Repair → Settings**. Enter the absolute CRM root folder containing `index.php`, `application/`, `system/`, `modules/`, `assets/`, and `uploads/`.

## 1.0.4
- Repairs now apply with one confirmation click; typing APPLY is no longer required.
- Successful repairs display every changed file and the generated migration/version.
- Module repairs automatically increment the module version and create the matching sequential migration.
- Core repairs automatically create the next sequential CRM migration.
- Added Clear Cache with protected `.htaccess` and `index.html` preservation plus OPcache reset.
- Applied repairs immediately appear in Repair History with Report and Rollback actions.
