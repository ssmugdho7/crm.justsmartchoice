# Smart Choice Goals Module Standard

- Direct folder: `/modules/goals/`
- Main file: `goals.php`
- Flat language files: `language/english/goals_lang.php` and `language/spanish/goals_lang.php`
- Continuous migrations through 250
- Mandatory permissions: View (Own), View (Global), Create, Edit, Delete
- PHP 8.5 and Perfex CRM 3.4.x compatible
- Scoped CSS only; no global DataTables or CRM table overrides
- Existing `tblgoals` records are preserved during activation and upgrade
- Uninstall preserves native goal records
