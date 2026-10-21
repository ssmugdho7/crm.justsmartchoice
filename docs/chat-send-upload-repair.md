# Messaging send, upload and group-photo repair
Restored client endpoints, reliable send acknowledgements, circular saved photos, and adjacent messaging fixes.

## Evidence and changes

The deployed client controller contained only Pusher authentication. Its interface referenced absent send, history, read, pagination, search, file-upload, reaction and edit actions, which produced 404 responses. The compatibility unread route also dispatched to a different response shape.

This repair supplies those actions with session-derived senders and conversation/customer authorization. It preserves the existing View Own / View / Create / Edit / Delete capability registration, active-contact checks and client visibility rules. It rejects spoofed senders, other customers, empty sends and unsupported reactions. History pagination and rapid switching retain the selected conversation. Typing requests do not require a message body and never insert a message.

Multipart staff/client/group, picker and drag/drop paths use the configured CSRF name and current value. Client uploads include their selected recipient and use client storage. File-picker and drag/drop completion persists to the captured recipient directly; it never overwrites or submits a current composer draft after a conversation switch. Native filename sanitization replaces an unnecessary browser filename blacklist. Voice capture pins its original conversation through asynchronous upload, releases microphone tracks on stop/cancel, and rejects malformed or failed responses. A message is marked delivered only after the server returns a persisted ID. Customer-portal acknowledgements update their particular optimistic row, including out-of-order responses.

Group-photo requests include CSRF and HTTP failure handling. Native image-byte and MIME checks permit JPG/PNG/GIF/WebP/BMP; active SVG and unsupported HEIC are rejected. Photos have unique URLs to avoid stale replacement caching. Database failure preserves the old photo and cleans the unsuccessful new file. Removal only deletes a managed avatar after the database update succeeds. Reloading the sidebar restores the saved photo. A square frame, circular clipping, cover fitting and no flex shrink keep the image round after remove/re-add. Changed shared CSS/JS URLs include their file modification time.

Adjacent findings from the wider review:

- Group member search sent POST while its controller read GET; inactive staff could appear in results. The picker now respects its query and filters active staff.
- Group creation could report failure after a successful save when realtime was unavailable. Group/membership creation is transactional, deduplicates members, validates names, and returns saved state independently of realtime. The create button recovers after an error.
- Group editing queried `sender_type`, a column absent from both the module schema and production. It now uses the actual group ID and own sender. Production schema inspection confirmed `group_id` and `edited_at` exist.
- Removing the last client reaction saved NULL but reported failure. NULL is now a valid cleared reaction; real database failures remain errors.
- Call signaling could accept HTTP-200 failure payloads, retain failed peer connections, or mistake equal numeric staff/contact IDs for self-calls. Namespace checks, accepted-response validation and cleanup are repaired.

New visible strings are in both `modules/prchat/language/english/chat_lang.php` and `modules/prchat/language/spanish/chat_lang.php`. Existing Staff / Groups / Clients navigation and permission gates remain. No core files, dependencies or existing messages are replaced. No schema migration is required: `group_image` is already supplied by native installation and migrations 207/210; the endpoints now request the native upgrade instead of altering schema during an upload.

## Source delivery

All complete modified files are in the Git commit. The exported handover includes `source/`, `repair.patch` and a SHA-256 manifest. Exact paths are listed in its README. This is an upgrade for the existing complete CRM checkout, not a standalone module ZIP.

## Executed validation

| Area | Actual checks |
| --- | --- |
| Native database/controllers | `php tests/chat_message_actions_regression.php` includes temporary-table database/client/call/group suites: send/history, typing, paging, scoped reads, edit/reaction success and failure, customer isolation, POST member search, inactive members, atomic groups, duplicate submissions, pin/mute reload, search history, text deletion and backend permission denials. |
| Seen-time regression | `php tests/chat_loading_receipts_database.php`: first-seen retention, concurrent receipts and historical timezone offsets. |
| Access regression | `php tests/chat_access_regression.php`: 22 menu cases, 11 call-access cases, bootstrap/token gates and private client-call channel ownership. |
| Multipart HTTP | `python3 tests/chat_upload_http_regression.py`: 34 checks through native CI Upload and controllers, real loopback HTTP, PDF and voice bytes across staff/client/group paths, invalid files, oversize voice, denied recipients, wide photo upload/replace/remove/re-add and failed DB save cleanup. Only ephemeral storage and connection-local MySQL TEMPORARY tables are used. |
| UI regression | `node tests/chat_ui_regression.js` and `node tests/chat_loading_receipts_ui.js`: 18 checks each for loading, tab isolation, empty/error states, races, dates and receipts; inline scripts parse. |
| Voice/avatar UI | `node tests/chat_voice_avatar_ui.js`: 20 checks for recording, duplicate click, late microphone cancellation, recipient pinning, fresh CSRF, invalid/404/HTML responses, persisted-ID acknowledgements and circular frame computed styles. |
| Attachment target UI | `node tests/chat_attachment_target_ui.js`: 21 checks exercising all four actual file-picker callbacks, recipient pinning, draft retention, saved-ID validation and the actual admin voice initialization. |
| Call behavior | `node tests/chat_calls_regression.js`: 15 controlled WebRTC/signaling checks, including voice/video, answer, ICE buffering, mute, hangup and setup failures. |
| Syntax/source | Changed PHP syntax, both edited JS files, bilingual key parity, inline-script parsing and `git diff --check`. |

Node suites use jsdom/jquery/moment from temporary tooling outside the application: `NODE_PATH=/tmp/prchat-audit-tools/node_modules`. Native database suites use existing private configuration but write exclusively to connection-local TEMPORARY fixture tables. The loopback router refuses normal web/FPM requests and requires its ephemeral marker and environment path.

## Upgrade and rollback

Back up tracked server changes and affected code first. Fast-forward dev with `git pull --ff-only origin dev`, validate it, then fast-forward production with `git pull --ff-only origin main`. Preserve private configuration, uploaded customer files, local server changes and vendor repositories. Refresh open messaging pages to reload inline code and versioned assets. Do not deactivate/reactivate the module, replace its database or clear chat history.

Rollback uses a revert of this repair followed by the same guarded branch pull. Original code archives and Git heads are saved outside public hosting for each deployment.

## Verification limits

Browser control was disabled during this repair; the new release has not had a live signed-in visual walkthrough. Computed DOM/CSS and controlled JavaScript checks do not prove browser rendering on every device. No live staff/customer message, notification, call or attachment was sent for testing. Two-device Pusher delivery, real microphone/camera hardware, TURN traversal, real network reconnection, Twilio SMS/phone calls and other external integrations still require operational acceptance. Clean activation on a separate Perfex 3.4.1 installation was not performed; the repair uses existing Perfex/CodeIgniter module APIs and was checked against the live PHP 8.3 schema. No claim that every provider/device feature is fully verified is made.
