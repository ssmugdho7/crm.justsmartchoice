# Login persistence

Staff and customer login, registration and password reset/set screens now have accessible password visibility buttons. Existing profile/contact controls remain in place. Buttons never submit forms and remask fields on submit/reset.

Default authenticated sessions last one 8-hour workday, with server-enforced absolute expiry and session-ID renewal every 5 minutes. Existing installation constants continue to override session settings. HTTPS installations use Secure session cookies and SameSite=Lax by default; session cookies remain HttpOnly.

“Remember me” restores a new session on the same browser/WebView for **7 days** after a successful login (and after successful staff two-factor authentication where enabled). The token uses 256 random bits; only its bound hash is stored in the existing database table. Expiry and staff/contact identity cannot be edited to extend or change access. Secure, HttpOnly, SameSite=Lax cookies use the installation's existing host/path/prefix. Logout or logging in without Remember me revokes the device token. Password changes and inactive/deleted contacts, customers or staff prevent restoration. There is no database migration.

Pre-upgrade remembered cookies lacked server-bound expiry. They require one fresh login to establish the new seven-day token; existing active sessions receive a workday timestamp when first encountered. No passwords are saved in JavaScript, local storage or Flutter preferences.

The Android WebView uses its normal persistent cookie store. It loads the same authoritative CRM and receives these controls/policies from the server. Emulator tests must use dummy cookie values or a development account; do not copy production cookies into test artifacts.

Verification: `php tests/remember_login_regression.php`, `node tests/auth_password_visibility.js`, and existing profile password and CAPTCHA regression tests. The token regression runs real authentication methods against disposable in-memory session/account/token adapters, including session loss, workday expiry, logout, tampering, password change and account deactivation.

There is no universal “industry standard” session duration. OWASP recommends balancing application risk and usability, using server-side expiration and session renewal: https://cheatsheetseries.owasp.org/cheatsheets/Session_Management_Cheat_Sheet.html
