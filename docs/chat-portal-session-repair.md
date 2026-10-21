# Customer chat during native admin preview

The live “Login as client” view for Harold Cabrera reproduced HTTP 403 on a customer reply. Perfex retains both the staff session and the authenticated contact session during this preview. Client chat previously always selected the staff session, so a legitimate `client_<id>` sender was rejected. Uploads, edits, reactions and unread receipts could use the wrong identity for the same reason.

Customer-widget endpoints now explicitly select portal context. The server honors that context during a dual session only when an authenticated contact and Perfex's server-side `logged_in_as_client` flag are present. The contact ID still comes from the session; staff visibility, active-account checks, sender ownership and customer isolation still apply. Admin messaging URLs keep staff context. A query flag alone grants no contact identity. No authentication, verification or permission settings are changed.

The voice HTTP-error translation also lost its status placeholder because Perfex formats language strings before JavaScript uses them. Passing the placeholder through `_l` preserves the actual HTTP status in failures.

The final browser check also found that the customer widget sends a numeric staff ID when marking messages read. The receipt endpoint now normalizes that numeric peer before applying the existing conversation authorization, matching uploads. Hidden staff and malformed IDs remain rejected.

## Validation

- `php tests/chat_portal_context_regression.php`: native dual-session send, stored contact identity, edit ownership, reaction identity, unread receipts, forbidden contacts/staff, customer-enumeration denial, staff coexistence and forged-context denials. The initial test reproduced the failure before the repair.
- `python3 tests/chat_upload_http_regression.py`: 40 real loopback multipart checks, including PDF/voice uploads from dual-session preview, stored bytes and hidden-staff rejection; temporary storage and connection-local database tables only.
- `node tests/chat_portal_context_ui.js`: actual PHP-rendered customer endpoint URLs and localized HTTP-status placeholder.
- Existing message actions, access, first-seen/DST, UI loading, voice/avatar, attachment target and controlled call suites pass. PHP syntax and whitespace checks pass.

The separate private verification report records live browser results and deployment heads. Two-device network behavior, real microphone hardware and external providers require their own operational acceptance. Native admin preview does not establish that an unverified account has completed email verification.

## Release

Promote the same commit to dev and main. Back up server source and local changes outside the webroot, then fast-forward each matching branch without changing private configuration or uploads. Refresh open chat pages to reload inline endpoint settings. Rollback is a revert followed by the same guarded deployment; no data migration is involved.
