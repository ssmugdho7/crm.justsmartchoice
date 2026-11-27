# Smart Choice Feedbacks 2.5.1 — Build Validation

- Module version: 2.5.1.
- Migration compatibility bridge: `240_version_240.php` / `Migration_Version_240`.
- Latest migration: `251_version_251.php` / `Migration_Version_251`; migration 250 uses a MySQL-compatible email collation.
- Existing source archive files retained: yes; all original file entries preserved.
- Built-in construction templates: 20.
- Built-in template questions: 186.
- Module-owned PHP files: syntax linted successfully using PHP 8.4.24.
- English/Spanish language keys: all English keys have matching Spanish keys.
- ZIP integrity: checked after packaging. All original 2.5.0 archive entries retained; only module version, migration files, and release documentation changed.

## Environment limitations
The build runtime provides PHP 8.4.24, not PHP 8.5. Therefore this build has not been executed under PHP 8.5. No Smart Choice CRM 4.3.3 staging database or live web server was connected in this session, so migration execution, email delivery, DataTables behavior, role permission behavior, and public-form submission have not been end-to-end exercised against the production CRM.

## Data compatibility
The upgrade is additive. It does not drop or rename existing questionnaire, mail-list, form-question, or form-result tables. The migration down methods intentionally do not remove the new tables or stored design/audience data.
