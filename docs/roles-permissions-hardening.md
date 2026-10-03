# Roles and permissions hardening — October 3, 2026

## Confirmed defects and fixes

- Staff permissions were replaced without a transaction and remained stale in the request cache after a grant or revocation. Replacements now lock the staff row, commit atomically, and refresh both cached permissions and the current user.
- Profile updates without permission fields could erase rights and alter administrator/non-staff status. Missing fields now preserve rights; the staff form includes an explicit marker so clearing every checkbox still revokes every right.
- A role assigned through an import/API path without explicit permission fields could produce a staff account with no rights. The model now applies the valid role template when explicit rights are absent. Explicit staff overrides, including an empty set, remain authoritative.
- Applying role permissions to existing staff now saves the role and staff replacements in one transaction, with rollback on failure. Renaming a role preserves its rights. Merely editing a role still preserves existing staff overrides unless “update staff permissions” is selected.
- Invalid role IDs and malformed permission payloads fail safely. Role cache entries are invalidated after updates/deletions.
- Overlapping role dropdown requests could display the wrong role's rights. Both served JavaScript assets now ignore outdated replies and prevent submission while loading or after a failed load.
- Chat advertised View own but checked only View in its menu, assets, directory, and controllers. Both read capabilities now open Chat. Own scope permits participant conversations, created/joined groups, and assigned customer contacts.
- Chat action endpoints now enforce Create, Edit, Delete, Delete groups, and AI Assistant independently of read access. Group/message ownership is checked on the server. The native group loader now returns only joined groups, rather than relying on the browser to filter a list of all groups. Group senders, uploads, leaving groups, and mention identity derive from the authenticated account.
- Staff and customer channel authentication now rejects unrelated private channels. Direct message payloads and read receipts use private participant channels; group administration events go only to members. Presence remains responsible for online status.
- Calls check the recipient's Chat access or the staff member's customer access. Staff callers cannot impersonate a client. Call signaling channels are private; customer calls require an authenticated contact, enabled customer calls, and an authorized staff recipient.

## Preserved behavior

Authentication, CSRF, native staff/role management guards, administrator bypass, customer assignment checks, per-staff overrides, and deliberate menu hiding remain in place. No application schema migration or real account permission changes are part of this release. Basic direct messaging retains the existing read-access requirement; Create applies to group creation, announcements, and employee SMS.

This audit covers the shared permission persistence path, native staff/role guards, and staff Messaging Chat authorization. It does not certify every third-party module's independent authorization implementation. The older audit's missing customer-chat controller methods remain a separate integration defect; this release does not invent customer messaging handlers.

## Verification

- `php tests/chat_access_regression.php`: 22 menu cases, 11 call constructor cases, 11 backend chat constructor cases, and 11 call-token cases, plus customer-call login/settings and private-channel checks, using actual module/menu/controller code.
- `CRM_SITE_ROOT=<private site checkout> php tests/roles_permissions_database_regression.php`: 81 checks using actual models and CodeIgniter's MySQL driver. All writes use connection-local TEMPORARY `audit_*` tables. Checks include cache refresh, malformed input, explicit clearing, role application/overrides, partial updates, failed-save rollback, scoped history, groups, contacts, private channels, recipients, inactive recipients, and revocation.
- `NODE_PATH=<jsdom runtime> node tests/role_permission_loader.js`: response races, blocked submission, failed requests, empty/cleared roles, and administrator changes for both served assets.
- Staff-save, proposal-PDF, customer-portal, and CAPTCHA regressions; PHP syntax for changed files and JavaScript syntax for 16 embedded chat scripts.

Interactive staff UI and real call delivery require an authenticated staff session. The isolated tests neither send real messages/calls nor modify real accounts.

## Rollback

Before deployment, preserve the previous production commit and archive the tracked checkout, site configuration, and staff/role/permission database tables in `/home2/scusawco/codex-backups/roles-permissions-20261003`. Roll back code with a reviewed revert of the release commits and a fast-forward pull. Restore the private SQL backup only if data restoration is specifically required; normal code rollback does not require restoring accounts.
