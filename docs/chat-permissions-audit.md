# Development chat access audit — October 3, 2026

## Staff access requirements

Messaging Chat requires the active `prchat` module, `pusher_chat_enabled=1`, and effective `prchat:view` permission (administrators pass the native staff permission check). Staff permissions come from `staff_permissions`; roles are templates, not runtime inheritance. Updating an existing role applies to existing staff only when the native **Update staff permissions** option is selected. Per-staff overrides must not be silently replaced or supplemented with role rights.

Client messaging additionally uses `chat_client_enabled`, `chat_staff_can_access_clients`, customer ownership/assignment, and customer-side staff visibility. With no global `customers:view`, the staff customer list is limited to `customer_admins` assignments. Customer contact permissions and staff module permissions are separate systems.

Chatbot Support/Manage grant separate chatbot screens. Create/Edit/Delete/AI Assist alone do not grant access to Messaging Chat. Delete Groups is a dedicated capability, and configuration switches can further restrict actions.

## Reproduced fixes

- The native sidebar substitutes `#` for a parent URL when it has children. Messaging Chat had SMS/settings children but no Conversations child; its intended destination was unreachable from that submenu. Add an explicit Conversations child and `collapse=true` so native Menu Setup preserves the new child even when the saved parent contains no children.
- Register the menu on `admin_init` after the native controller initializes the current staff account.
- Chatbot-only staff were authorized by `Chatbot_Admin` but could not see the menu because it was nested under `prchat:view` and the staff-chat switch. Register that menu independently, matching its controller and preserving the Manage-only settings/analytics checks.
- `Calls_Controller` previously required staff login but no Chat capability. Require effective `prchat:view` before Pusher initialization, matching Messaging Chat access.
- Explain actual requirements beside Chat permission controls without modifying stored permissions.

## Development data findings

Read-only audit: chat module and staff/customer chat enabled; real-time credentials populated; permission-only staff filtering enabled; client staff visibility `assigned_and_responded`; Menu Setup active and the Chat/Chatbot parents not disabled. No options, role rows, permissions, passwords, or messages were changed.

Role 34 stores `view_own/create/edit/chatbot_support/ai_assist`; its staff member 42 stores `view/create/edit/chatbot_support/ai_assist`. This demonstrates that role selections and effective staff rights can differ. That account already has effective Messaging Chat View; an administrator should review its intended scope rather than assume the role is inherited automatically. No active staff account in this development audit has effective Chat `view_own` without `view`.

## Further defects requiring scoped work

`view_own` is advertised by this module but is not implemented in its access checks. Do not alias it to global View: several group endpoints lack membership checks and shared presence events broadcast message payloads. Isolating own-conversation access requires ownership checks and recipient-scoped real-time delivery, with tests for both HTTP and Pusher subscriptions. The help text now makes this limitation explicit. Existing saved rights are retained.

The development client controller contains only `pusherCustomersAuth`, while customer/staff templates still reference absent methods including `getMutualMessages`, `initClientChat`, `getStaffUnreadMessages`, `updateClientUnreadMessages`, `searchClients`, `loadMoreClients`, `uploadMethod`, and `addReaction`. These produce missing endpoints independently of role access. Restoring them requires customer/staff identity, ownership and upload checks; simply routing customer requests to the admin controller would be unsafe. This release does not claim to repair those workflows.

## Verification and rollout

`php tests/chat_access_regression.php` executes the actual module registration, native App_menu/Menu Setup code, 22 permission/enabled combinations, chatbot-only cases, preservation of deliberate menu hiding, and an unauthorized call constructor. PHP lint covers changed source. Production stays unchanged; deploy only the development commit with a private backup of changed files and previous commit recorded for rollback. No database migration or blanket permission grant.
