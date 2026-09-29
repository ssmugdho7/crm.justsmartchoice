Google Meet 1.2.0

Repairs:
- Removed the invalid custom customer route that caused HTTP 404.
- Uses Perfex native HMVC URL /google_meet/client -> Client::index.
- Corrected duplicated module path in customer view loading.
- Added defensive customer meeting lookup by contact ID and matching contact email.
- Added friendly empty state with Check Again button.
- Assigned meetings display as responsive cards with View and Join actions.
- Preserves all existing meetings, settings, credentials, attendees, and admin features.
- Adds sequential migration 120_version_120.php.
