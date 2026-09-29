SMART CHOICE GOOGLE MEET 1.2.1

FIXED
- Corrected config/routes.php so it defines a valid CodeIgniter $route array.
- Restored native Perfex HMVC resolution for /google_meet/client.
- Preserved the native customer portal header and footer.
- Preserved the friendly no-meeting state and refresh action.
- Preserved assigned customer meeting cards and protected View/Join actions.
- Added sequential migration 121_version_121.php.

INSTALL
1. Back up the database.
2. Back up modules/google_meet.
3. Upload and overwrite modules/google_meet with this package.
4. Run the module upgrade to version 1.2.1.
5. Clear Perfex cache and PHP OPcache.
6. Log in as a customer and open /google_meet/client.

ROLLBACK
Restore the previous modules/google_meet backup and database backup.
