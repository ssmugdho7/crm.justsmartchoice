# Smart Choice Feedbacks 2.5.1

Target: Smart Choice CRM 4.3.3 / Perfex Classic 3.x. Module folder remains `modules/feedbacks`.

## Migration repair
This release includes both `240_version_240.php` (no-op compatibility bridge) and `250_version_250.php` (additive schema migration) and `251_version_251.php` (MySQL index compatibility repair). This directly addresses installations that report: `No migration could be found with the version number: 240`. Do not delete earlier migration files.

## Included
- Preserves the original questionnaire module, mail lists, templates, and 20 construction templates.
- Bulk questionnaire deletion and View/Delete table actions from 2.4.0.
- Advanced question metadata and public field rendering for dropdown, yes/no, rating, linear scale, number, currency, percentage, date, time, email, phone and URL.
- Numeric keypad prompt for numeric responses.
- JSON and CSV questionnaire import, JSON export and sample CSV header download.
- Form design settings with color, corner radius, custom HTML header/footer.
- Audience segments with manual member addition and CSV import.
- Date, questionnaire, question/topic and authenticated staff filters in reports; response charts and answer distributions.
- English and Spanish labels for new controls.

## Upgrade procedure
1. Back up database and `modules/feedbacks`.
2. Replace the complete existing `modules/feedbacks` folder with this ZIP's `feedbacks` folder. Do not uninstall.
3. In Setup > Modules, run the module upgrade if prompted. Migration 240 is intentionally present as a bridge; migration 250 creates the new additive tables and migration 251 repairs the audience email index for older MySQL/InnoDB configurations.
4. Clear application and browser cache.
5. Test a questionnaire, public response, import/export, audience CSV, reports and custom branding on staging.

## Data safety
No existing Perfex form tables are dropped or renamed. The migration `down()` intentionally retains new tables to avoid destructive rollback. Questionnaire import creates a new questionnaire; it does not overwrite an existing one.

## Compatibility validation
PHP lint and package integrity checks are included in the release report. Live CRM 4.3.3 database execution and PHP 8.5 runtime validation require the target server; do not treat static lint as proof of live runtime compatibility.
