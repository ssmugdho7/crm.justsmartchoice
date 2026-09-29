SMART CHOICE GOOGLE MEET v1.1.0 FIXED

IMPORTANT INSTALLATION:
1. In File Manager, go to public_html/crm.justsmartchoice/modules/google_meet/migrations/
2. Delete these old legacy files if they exist:
   - 104_google_meet_stable_database.php
   - 105_google_meet_view_path_fix.php
   - 106_google_meet_auto_reports_notes_fix.php
3. Upload this google_meet folder directly into public_html/crm.justsmartchoice/modules/
4. Run Upgrade Database for Google Meet.

This package is flat and must NOT be uploaded as modules/google_meet/google_meet.

Fixed:
- Version 1.1.0
- Standard migrations 101-110 only
- Every migration has up() and down()
- Legacy migration cleanup in module bootstrap
- Language folders remain flat: language/english/google_meet_lang.php and language/spanish/google_meet_lang.php
