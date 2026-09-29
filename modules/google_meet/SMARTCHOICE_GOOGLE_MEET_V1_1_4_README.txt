SMART CHOICE GOOGLE MEET — VERSION 1.1.4

UPGRADE MIGRATION
- Adds sequential migration 114_version_114.php.
- Updates the stored module version to 1.1.4.
- Updates the customer portal navigation label to Meetings.
- Adds the Google Meet subtitle and customer-login-required options.
- Preserves all meetings, attendees, notifications, settings, credentials, API keys, and links.

CUSTOMER PORTAL REPAIR
- Removes the separate My Video Meetings button previously injected beneath the customer login form.
- Hides meeting navigation from logged-out visitors.
- Requires an authenticated customer contact for every meeting list, view, and join route.
- Moves Meetings before the logout/profile area where supported by the customer navigation markup.
- Uses the existing camera icon, a compact Meetings title, and a smaller Google Meet subtitle.
- Shows a clear no-meeting-scheduled message for authenticated contacts without assigned meetings.

PERMISSIONS
1. View Own
2. View(Permission Global)
3. Create
4. Edit
5. Delete
6. View All Templates

INSTALLATION
1. Back up the database and modules/google_meet directory.
2. Upload this google_meet folder over modules/google_meet.
3. Open Setup > Modules and run the available upgrade to version 1.1.4.
4. Clear Perfex cache and PHP OPcache.
5. Verify staff permissions and customer portal behavior.

ROLLBACK
Restore the backed-up module folder and database. Migration 114 intentionally does not delete production data.
