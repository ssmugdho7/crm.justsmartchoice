Google Meet 1.2.4

Customer portal rebuilt to match the working Appointly module pattern:
- Native Perfex/MX URL: /google_meet/meeting_clients/meetings
- Meeting_clients extends ClientsController
- Uses get_client_user_id() for the customer company
- Finds meetings assigned to any contact under that customer
- Supports contact_id and attendee-email fallback
- Uses $this->data(), $this->view(), and $this->layout()
- No custom client route aliases
- Preserves standard customer header/footer
- Friendly empty state and meeting cards
- Protected View and Join actions
- Existing meetings, attendees, settings, credentials, and links preserved
