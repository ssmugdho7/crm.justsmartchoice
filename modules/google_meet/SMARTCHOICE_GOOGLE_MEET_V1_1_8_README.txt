SMART CHOICE GOOGLE MEET — VERSION 1.1.8

CUSTOMER PORTAL HTTP 500 REPAIR
- Routes customer meeting pages to the module-specific Google_meet_client controller.
- Removes the generic Client controller dependency that could collide with Perfex core routing.
- Corrects the native customer module view path from google_meet/client/... to client/....
- Uses the normal Perfex customer header, navigation, content area, and footer.
- Shows the assigned-meetings table when meetings exist.
- Shows a friendly no-meeting message when no meeting is assigned.
- Protects every list, view, and join action by authenticated contact assignment.
- Logs query errors and safely displays the empty state instead of returning HTTP 500.
- Loads the module customer styles in the client portal.

HELP
- Adds exact instructions for assigning a new meeting to a customer contact.
- Explains that the customer must be selected in Customer Contacts for the meeting to appear.

MIGRATION
- Adds migrations/118_version_118.php.
- Updates the module version to 1.1.8.
- Preserves all meetings, attendees, links, settings, API credentials, templates, notifications, and customer records.

INSTALLATION
1. Back up the database and modules/google_meet directory.
2. Upload the google_meet folder over modules/google_meet.
3. Open Setup > Modules and run the available upgrade to version 1.1.8.
4. Clear Perfex cache and reset PHP OPcache.
5. Assign a meeting to a test customer contact and verify Meetings in the customer portal.

ROLLBACK
Restore the backed-up module folder and database. Migration 118 intentionally deletes no production data.
