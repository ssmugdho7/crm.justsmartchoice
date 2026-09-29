SMART CHOICE GOOGLE MEET — VERSION 1.1.5

REPAIR
- Fixes authenticated customer portal 404 errors.
- Corrects Perfex/CodeIgniter module route targets so they point directly to Google_meet_client.
- Preserves legacy customer meeting URLs for existing bookmarks and emails.
- Verifies meeting assignment before viewing or joining.
- Keeps the Google Meet join link private to the assigned logged-in contact.
- Adds GROUP BY protection so duplicate attendee rows do not duplicate meetings.

MIGRATION
- Adds migrations/115_version_115.php.
- Updates stored module version to 1.1.5.
- Preserves all meetings, attendees, links, settings, API credentials, templates, and customer records.

UPGRADE
1. Back up the database and modules/google_meet.
2. Replace modules/google_meet with this package.
3. Open Setup > Modules and run the upgrade to 1.1.5.
4. Clear Perfex cache and PHP OPcache.
5. Log in as an assigned customer contact and open Meetings.
