# Jitsi video meetings (module 1.3.0)

New automatic meetings use a persisted Jitsi URL with 128 bits of randomness. Edits and retries reuse that URL. Existing complete Google Meet URLs remain usable. The module name, permission feature and routes remain `google_meet` for compatibility.

## Provider requirements

The CRM generates rooms without Google OAuth or API keys. Public `meet.jit.si` requires the host to sign in to Jitsi before guests can join. Hosting policies and recording availability belong to the provider; the CRM does not impose a 60-minute timer or guarantee the public service's availability or duration. A different HTTPS Jitsi hostname can be set in module settings.

A saved six-digit PIN is optional. The embedded CRM host applies it on becoming a Jitsi moderator; embedded guests submit it when prompted. Jitsi removes the room password when everyone leaves. Hosts using the external URL must apply the saved PIN in Jitsi themselves. CRM edit permission/ownership and Jitsi moderation are separate checks.

Staff with view access can keep private notes. Only a meeting owner/assigned host with edit permission (or an administrator with edit permission) can complete a room through lifecycle requests. Guest exits never complete meetings. Completing a CRM meeting and hanging up leaves the local participant's video session; it does not forcibly eject other Jitsi participants. Closing the browser is not a reliable conference-left event; existing manual status controls remain available.

Share actions compose a WhatsApp link or an email draft; they do not silently send messages. Calendar and room endpoints retain customer ownership/contact permission checks. Customers never receive the private notes endpoint or timeline payload.

Appointly creates a video URL before its staff and external booking inserts/notifications. Explicit Google Calendar integrations remain available but cannot replace the saved Jitsi URL. Appointment calendar files and invitation templates include the saved link. Email transport, approval, booking and billing policies remain unchanged.

## Development release

1. Back up the development checkout and its meeting tables/options before upgrading. Preserve installation configuration, uploads and dependencies.
2. Check tracked server changes before pulling: `git status --short --untracked-files=no`. Stop if there are conflicting server edits.
3. Deploy `/home2/scusawco/public_html/dev` with `git pull --ff-only origin dev`.
4. Use the existing Perfex Modules upgrade action for Google Meet 1.3.0 (migration `130_version_130.php`). It adds fields and a nullable composite unique live-note identity; existing Google links and credentials are preserved. Running installation twice is covered by the native SQL regression test.
5. Confirm the module settings/health page shows a valid Jitsi server and upgraded schema. Create one development fixture without notifications, check admin/customer scoped joins, notes, sharing and deletion, then verify a two-party call using a host signed into Jitsi.
6. Keep production unchanged. Roll back application files using the saved revision if verification fails. The additive columns can remain; migration `down()` deliberately preserves links/notes/credentials. Restore the private database backup only if data rollback is necessary.

## Verification

Run with PHP 8.x and Node with `jsdom`/`jquery` available:

- `php tests/jitsi_helpers_regression.php`
- `CRM_SITE_ROOT=/path/to/private/local/site php tests/jitsi_database_regression.php`
- `php tests/jitsi_appointments_regression.php`
- `php tests/jitsi_views_regression.php`
- `node tests/jitsi_room_ui.js`
- `php tests/meetings_permissions_regression.php`
- `php tests/customer_meeting_query.php`
- `php tests/customer_meeting_render.php`

Database tests mutate only connection-local temporary tables, including rollback failure tests. Appointly notification tests intercept insertion before sending any messages. Browser QA can render the real views with `JITSI_QA_DIR` set; that fixture verifies layout/provider loading separately from native SQL and permission tests. It is not evidence of a completed two-party audio/video call.
