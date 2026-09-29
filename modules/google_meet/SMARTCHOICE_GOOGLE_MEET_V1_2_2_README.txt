Smart Choice Google Meet 1.2.2

Customer portal repair:
- Fixes the fatal undefined google_meet_lang() function used by the customer controller and views.
- Uses valid MX HMVC module routes with controller targets relative to the google_meet module.
- Preserves the native Perfex ClientsController header/footer layout.
- Shows assigned meetings as cards.
- Shows a friendly empty state when no meeting is assigned.
- Preserves customer meeting assignment by contact_id.
- Adds migration 122 to repair the attendee contact_id/email schema without deleting data.
