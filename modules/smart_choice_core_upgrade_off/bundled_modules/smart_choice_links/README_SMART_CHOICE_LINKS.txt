Smart Choice Links v1.0.3

Fresh standalone Perfex CRM module.
Folder: smart_choice_links
Table: tblsmart_choice_links
Migration: 101_version_101.php

Install:
1. Upload folder to modules/smart_choice_links
2. Activate in Perfex Modules
3. Run Upgrade Database
4. Clear application/cache except index.html
5. Ctrl+F5

This module does not use favorite_links tables, classes, routes, or migrations.


Version 1.0.3:
- Fixed Add New Link modal freeze/backdrop issue.
- Moved modal container to body safely.
- Changed primary accent from yellow to Smart Choice orange.
- Improved topbar Links button vertical alignment.


Version 1.0.3: improved bad URL cleanup, updated instructions, and reduced modal size by about 20%.


Version 1.0.4:
- Edit modal now shows internal CRM links as clean paths like admin/modules.
- URL cleaner fixes moduless/modeless and stacked repeated CRM URLs.
- URL field disables autocomplete and cleans on blur/submit.

Version 1.0.5: fixed admin/modules URL persistence, removed unsafe module-word auto-rewrite behavior, updated instructions, and added migration repair for existing bad moduless/modeless records.
