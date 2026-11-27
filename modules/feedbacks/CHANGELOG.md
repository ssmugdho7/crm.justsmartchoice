## 2.5.2
- Corrected migration base classes to Perfex App_module_migration for module upgrade compatibility.
- Added migration 252 idempotent schema integrity pass; retained existing migration history and data.
- Added explicit database error logging and failure propagation in schema migrations.

# Changelog

## 2.5.1
- Fixed the audience-members email index for MySQL 5.7 and legacy InnoDB index-length limits.
- Added idempotent migration 251 to repair installations that already recorded version 2.5.0.
- Preserved existing module functionality and data structures; no existing module files or dependencies removed.

# Smart Choice Feedbacks Changelog

## 2.5.0
- Added migration 240 compatibility bridge to resolve missing migration 240.
- Added migration 250 for advanced question metadata, form design, audience segments and import logs.
- Added questionnaire JSON export/import and sample CSV header download.
- Added advanced public response controls: dropdown, yes/no, rating/scale, number, currency, percentage, date, time, email, phone and URL; numeric entry offers an on-screen prompt keypad.
- Added configurable form header/footer and visual settings.
- Added audience segment creation and CSV/manual member management.
- Added date/questionnaire-filtered reporting and response breakdown.
- Preserved original questionnaire tables and legacy response storage.

## 2.4.0
- Added 20 construction-business templates and questionnaire list bulk actions.
