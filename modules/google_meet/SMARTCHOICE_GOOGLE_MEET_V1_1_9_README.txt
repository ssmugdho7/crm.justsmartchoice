SMART CHOICE GOOGLE MEET 1.1.9

FIXED
- Corrected /google_meet/client to route explicitly through the google_meet module controller.
- Prevented database query failures from producing an HTTP 500 on the customer portal.
- Preserved the native Perfex customer header, navigation, and footer.
- Added a friendly empty state when no meeting is assigned.
- Preserved assigned-meeting View and Join security.
- Added migration 119_version_119.php.

INSTALL
1. Back up the database and modules/google_meet folder.
2. Upload the google_meet folder over modules/google_meet.
3. Run the module upgrade to 1.1.9.
4. Clear CRM cache and PHP OPcache.
5. Log in as a customer and open /google_meet/client.
