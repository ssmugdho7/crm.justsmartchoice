# CRM testing report review — 2026-10-02

## Changes

- Dashboard calculator now parses arithmetic without dynamic JavaScript evaluation, accepts formatted numbers and percentages, rejects invalid/non-finite results, and supports Enter. Existing calculator failure was reported; no authenticated browser reproduction was available.
- Message history uses a JavaScript-safe JSON literal. The former JSON.parse string consumed JSON escapes before parsing, breaking messages with newlines/backslashes. Search decodes text entities, uses the local staff ID, and removes stale delegated handlers when reopening history.
- Chat audio input/output preferences persist across reloads, restore into the picker, support resetting to the default, and apply to call media and voice recordings. Device selection failures no longer show success. Microphone permission is requested by the device-picker gesture if enumeration hides device labels. Recording setup failures stop acquired microphone tracks.
- New project/expense uploads create missing nested directories. The live server lacked the recursive directory fix already committed in Git. Expense uploads sanitize/check filenames and return failure if saving fails; the controller no longer returns a successful redirect after failure.
- Expense images/PDFs request the authenticated inline preview; missing attachments show an explicit missing-storage error rather than an invalid download.
- Candidate email now uses the CRM transport with its standard username fallback/OAuth support, verifies immediate transport acceptance, and records Sent only after acceptance. Acceptance does not prove recipient delivery. Candidate care actions validate inputs, report request failures, refresh saved activity, and scope their dialog title correctly.
- Sales Hub duplicates appear only when the administrator explicitly hides the corresponding core Sales entries; unique Sales Hub features remain.
- Enterprise calling checks setup before constructing the Twilio SDK client.

## Live findings requiring account/storage work

- SMTP password decrypts, server connection succeeds, but authentication returns **535**. The queue contains **54 pending emails**. Correct mailbox credentials in Setup → Settings → Email, then verify delivery and review queued recipients before retrying. No emails were sent by this audit.
- `sccc_*` Call Center account/token/API key/API secret/TwiML App SID and caller-number settings are empty. Enterprise 360's three Twilio settings are also absent. PRChat-specific Twilio settings are empty, although other modules contain Twilio configuration. Credentials cannot be assumed interchangeable; configure each intended service and verify its webhooks/voice permissions.
- All **31 expense attachment records** checked point to files absent from `uploads/expenses`. Fixing upload code cannot reconstruct missing receipts. Recover from a storage backup or upload receipts again; no records were deleted.
- Reported PayPal `PAYEE_ACCOUNT_RESTRICTED` indicates a restriction on the receiving merchant account. The owner must resolve it with PayPal. No payment was attempted. Reference: https://developer.paypal.com/api/errors/overview/
- Call, recording, and browser microphone permission behavior still require an authorized real-device call test after configuration. Candidate Call/Test/Interview care actions log activity; they do not themselves place telephone calls, administer assessments, or schedule interviews.

## Validation

- `node tests/crm_testing_regression.js`: arithmetic, invalid inputs, Enter, disabled dynamic evaluation, audio persistence/default reset and output failures.
- `php tests/crm_php_regression.php`: failed single/bulk candidate mail never records Sent; accepted mail does; history JSON preserves quotes, newlines and backslashes.
- Rendered candidate JavaScript using English translations passes `node --check`.
- Real loopback HTTP multipart uploads against production helper functions, using isolated storage and a mock database: project and expense uploads into missing directories succeed; rejected file extension fails. No production records or notifications.
- PHP syntax lint and JavaScript syntax checks on modified files.

Deployment uses a private timestamped backup and SHA-256 baseline comparisons to avoid overwriting concurrent live edits. No changes to working signature/application-submission code or proposal PDF files.
