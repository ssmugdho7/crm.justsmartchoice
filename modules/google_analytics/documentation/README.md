# Smart Choice Google Analytics 2.0.0

Install the `google_analytics` folder directly under `modules/`.

The module does not modify Perfex core tables, customer tables, estimate tables, DataTables, or global CSS. Tracking is injected only through supported Perfex hooks and only when enabled with a valid GA4 Measurement ID.

For API reports:
1. Enable Google Analytics Data API in Google Cloud.
2. Create a service account and download its JSON key.
3. Add the service-account email as Viewer on the GA4 property.
4. Paste the JSON in module settings.
5. Add the numeric GA4 Property ID to each website profile.


## Version 2.0.1
- Fixed MySQL 5.7 HTTP 500 during database upgrade.
- Removed unsupported defaults from TEXT columns.
- Added recovery migration 201 for failed migration 200 installations.

## Version 2.0.2 migration recovery
This package preserves the original migration 100 and includes every sequential
migration through 201. This is required for existing installations that recorded
migration 100; Perfex executes each missing integer migration in order.
