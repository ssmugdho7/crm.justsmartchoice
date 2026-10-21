# Customer chat upload stability during admin preview

The live access log recorded a customer upload returning HTTP 403 at 21 October 2026 00:18:13 server time, immediately after background `getGroupMessages` requests. `AdminController` cleared the native `Login as client` session on every staff-chat poll. A recording or file chosen before the poll then lost its authorized contact context when uploaded. Longer recordings gave the poll more time to interrupt the session. A standalone nine-second recording uploaded successfully before this change; there is no demonstrated seven-second codec or size limit.

The cleanup now preserves an existing preview only for the staff chat's `users`, `getMessages`, `getGroupMessages` AJAX polls and `pusher_auth` realtime authentication (which also uses JSONP). These APIs still require the authenticated staff session and existing action/object permissions. The change creates no contact session and adds no recipient access. Normal admin navigation, other actions and logout retain the native preview cleanup. Upload MIME, size, CSRF, contact-session ownership and staff visibility checks remain active.

Validation:

- `php tests/chat_preview_poll_session.php`: executes the native session cleanup block. Reproduced the reset before the fix; preserves the four background APIs after the fix; foreground navigation, unrelated AJAX, mutation and logout retain cleanup and staff identity.
- `php tests/chat_portal_context_regression.php`: authenticated-contact identity, staff coexistence, forged-context, cross-contact and hidden-staff denials.
- `php tests/chat_access_regression.php`: existing menu, chat bootstrap, call and private-channel access checks.
- `python3 tests/chat_upload_http_regression.py`: 48 real multipart checks with temporary storage, including eight- and thirty-second recordings in normal portal and native-preview context, byte-for-byte storage, ordinary files, invalid/oversize uploads and forbidden recipients.
- Changed PHP syntax and whitespace checks with the existing CRLF convention pass.

This repair addresses the demonstrated admin-preview interruption. Normal customer sessions do not contain a concurrent staff session. A fresh native preview is needed if admin navigation already ended it before deployment. Browser, device and network problems outside this cause require their actual failing HTTP response to diagnose.
