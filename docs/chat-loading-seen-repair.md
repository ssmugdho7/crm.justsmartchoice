# Messaging loading and seen-time repair
Reliable conversation selection and message-specific, timezone-aware read receipts.

## Investigation

Before this repair, dev and main had identical tracked PRChat files; server SHA-256 checks also matched for the full view, controller, model and client include. Dev's extra commits were theme changes. Both authenticated chat pages loaded existing messages during inspection, so a permanent production-only outage was not reproduced. No messages were sent during verification.

The unrepaired code failed isolated behavior checks for (1) numeric sender IDs in seen events (`senderId.startsWith is not a function`), (2) a delayed background group response replacing Staff view, and (3) concurrent readers broadcasting the later attempted seen time instead of the first timestamp already saved in the database.

## Changes

- Initialize presentation and the selected staff conversation from database readiness, independently of realtime presence. Embed the permission-filtered initial contact list with an AJAX fallback; batch its lookups.
- Initialize the authorized client-presence list and use namespaced contact identities, avoiding undefined-list errors during realtime membership updates.
- Guard stale history responses and duplicate pagination; complete empty/error states and retain the selected pane/composer when background groups arrive.
- Scope seen events by sender namespace, conversation and message ID. Preserve first-seen database values and broadcast the saved timestamp, including concurrent reads.
- Serialize dates with the CRM timezone's historical UTC offset. Existing database dates are unchanged; browser presentation converts the supplied instant.
- Use the configured CSRF field for staff/group multipart requests. Keep existing backend access checks and permission registration.
- Add the new load-error language key in English and Spanish. No core files, settings, credentials, dependencies, schema, or existing customer data are changed.

## Complete source locations

- `modules/prchat/controllers/Prchat_Controller.php`
- `modules/prchat/models/Prchat_model.php`
- `modules/prchat/views/chat_full_view.php`
- `modules/prchat/assets/module_includes/groups.php`
- `modules/prchat/assets/module_includes/crm_clients.php`
- `modules/prchat/assets/module_includes/mutual_and_helper_functions.php`
- `modules/prchat/language/english/chat_lang.php`
- `modules/prchat/language/spanish/chat_lang.php`
- `tests/chat_loading_receipts_ui.js`
- `tests/chat_loading_receipts_database.php`

The existing Staff/Groups/Clients navigation and backend permission gates remain in use. This targeted repair does not introduce new capabilities or change activation/installation behavior. Perfex-native controller, model, helper and language conventions are retained; the live installation identifies assets as 4.3.5. No clean Perfex 3.4.1 activation was performed.

## Executed checks before release

- PHP syntax for all eight modified module files: passed.
- `NODE_PATH=/tmp/prchat-audit-tools/node_modules node tests/chat_loading_receipts_ui.js`: 18 behavior checks passed; inline scripts parse. Uses jsdom, jquery and moment from temporary test tooling outside the application.
- `php tests/chat_loading_receipts_database.php`: passed native MySQL temporary-table fixtures for permission-filtered contacts, scoped read updates, first-seen retention, concurrent staff/client receipts, summer/winter offsets and missing timestamps. Contact lookup measured six queries for 40 fixture users; this is query-count evidence, not a production timing claim.
- `php tests/chat_access_regression.php`: existing menu, saved-menu, effective-permission, endpoint-bootstrap and private call-channel access checks passed.
- `git diff --check`: passed.

## Upgrade and rollback

No module reactivation or database migration is required. Pull the release commit on the intended existing branch with `git pull --ff-only origin dev` or `git pull --ff-only origin main`, after backing up current code and confirming no conflicting server edits. Refresh an already-open chat page after deployment. Existing messages and read timestamps are retained. Rollback is a revert of this repair followed by the same guarded pull; do not restore or replace the database.

## Limits

Fixture tests use controlled AJAX/realtime inputs and connection-local TEMPORARY tables. No live test message, customer notification, voice/video call or multipart attachment was sent. Two-device realtime delivery, full provider outages, and clean activation on an independent Perfex installation are not claimed as tested. Live read-only acceptance and deployment results are recorded in the release handover.
