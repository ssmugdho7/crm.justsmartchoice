Google Meet 1.2.6

- Uses Google Meet 1.2.5 as the exact baseline.
- Fixes customer portal 404 for /google_meet/meeting_clients/meetings with explicit module routes.
- Keeps the native Perfex customer portal navigation, header, content area, and footer.
- Keeps the customer Meetings page available even when the logged-in customer has no meetings.
- Preserves customer meeting detail and Join Meeting routes.
- Restores robust rendering/initialization for Responsible Employee, Project, Staff Attendees, and Customer Contacts selectors.
- Adds a native-select visibility fallback if another CRM JavaScript asset prevents bootstrap-select initialization.
- Adds sequential non-destructive module migration 126.
- Preserves all meetings, attendees, settings, credentials, notifications, reports, and existing admin functionality.
