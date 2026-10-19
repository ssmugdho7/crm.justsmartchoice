# Daily report history repair
## Show previously submitted reports on My Day Report

The default page silently filtered records to today's date. Production contained a manual report dated October 16, which was therefore hidden on October 19. The default list now shows up to the latest 500 entries for the current staff member, with an explicit date filter and All dates action. Global viewers retain the existing ability to select staff; View Own users cannot override their staff ID. Save failures no longer produce a success alert. Dates use Perfex's configured date conversion.

Changed source is in modules/smart_daily_productivity/{controllers/Smart_daily_productivity.php,models/Smart_daily_productivity_model.php,views/my_day.php,language/english/smart_daily_productivity_lang.php,language/spanish/smart_daily_productivity_lang.php}. Regression coverage is tests/daily_productivity_history_regression.php.

Upgrade: deploy these source files to the existing installed module. No activation, database migration, permissions or existing record changes are required. Roll back using the saved source or the previous Git revision.

Validation: PHP lint passed on all five changed module PHP files. The CLI regression passed 10 checks covering historical/default filters, date selection, own/global access, save alerts, create access and localized dates. No live employee report was created or edited for testing.
