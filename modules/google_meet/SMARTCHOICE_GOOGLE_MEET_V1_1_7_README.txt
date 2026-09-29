Google Meet 1.1.7

Customer portal HTTP 500 repair:
- Uses the native Perfex ClientsController data/view/layout rendering pipeline.
- Removes admin init_head/init_tail calls from customer views.
- Repairs and validates the attendee schema through migration 117.
- Preserves meetings, attendees, links, settings, API credentials, templates, and notifications.
- Keeps authenticated customer-only access and per-contact ownership checks.
