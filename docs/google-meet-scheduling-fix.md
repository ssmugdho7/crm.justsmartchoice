# Google Meet scheduling and notification fixes

- Parse SQL, HTML datetime-local and configured CRM date formats strictly. Reject invalid times and end-before-start before database writes. Epoch/zero dates show “Time needs correction”; existing dates are not guessed or rewritten.
- Use the datetime-local `T` separator in edit forms; safe date display in admin, customer and report views.
- Deploy the existing module language-registration fix and add English fallbacks to meeting-list labels.
- After saving a staff inbox notification, trigger Perfex's real-time notification refresh. Preserve the saved notification if the push transport fails.
- Mark an attendee notified only when at least one configured channel accepts the notification; report failure if no channel succeeds. SMTP queue acceptance is not proof of recipient delivery.
- Honor “Send invitations now” on meeting updates as well as creation.
- Explain manual links without Google event IDs as not synced to Google Calendar.

Validation: `php tests/google_meet_regression.php` covers valid/localized dates, invalid/epoch inputs, end-before-start, preventing invalid database writes, failed invitation flags, and real-time trigger recipient. PHP lint passes on all changed files. Tests mock database and Pusher: no invitations, emails, SMS, or Google events are sent.

Live audit: module and global real-time notifications are enabled; Pusher settings are present. The screenshot's epoch-dated meeting was not present in the audited live meeting records. No existing meeting date was modified. Real recipient popup behavior remains to be checked with an approved invitation; SMTP delivery still depends on fixing the previously identified authentication failure.
