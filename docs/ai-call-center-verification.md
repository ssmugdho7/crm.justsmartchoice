# Live AI call center repair - 19 November 2026

## Current outcome

- Verified call-center implementation on production and GitHub main: `9caa9d900f675f61d8c75b279ddae19595511c5e` (prior `645ad6cfe810927beb7e020c84fd50ec9978a72f`).
- The user explicitly authorized preserving the 467 unrelated tracked production changes while deploying only the call-center repair. Their binary patch was compared byte-for-byte after each fast-forward. They were preserved.
- Private recovery point: `/home2/scusawco/.codex-backups/call-center-e7e62679-6ad57B`. Contains committed source, tracked/index patches, runtime configuration, call-center source snapshots, consistent database dump, and private provider setup records. Database dump passed gzip verification and SHA-256 verification.
- Twilio account active; owned voice number `+18559066053`. Fixed outbound caller ID incorrectly set to an email address. Main number and outbound caller ID now use the owned phone number.
- Configured a dedicated Call Center TwiML application and browser signing credentials. Provider authentication verified HTTP 200. Browser dialer visibly reached READY before browser control was disabled in Work settings.
- Existing inbound Studio forwarding to the business phone and separate Lead Manager TwiML application preserved.
- Public callback routing repaired against the actual MX module router: valid signed callback reaches the controller; invalid signature is rejected HTTP 403. A valid callback for a nonexistent call returns HTTP 404 as expected. Public JavaScript HTTP 200; `/.git/HEAD` HTTP 403.
- First approved phone test to `+17277553786` completed in 11 seconds. Twilio and CRM status/duration agree; answer/end timestamps persisted.
- Existing main OpenAI credential authenticates but generation returns HTTP 429 / `credit_balance_exhausted`. A separately configured CRM chatbot credential generated successfully. Reused that existing credential privately as the dedicated Call Center AI key; did not change other modules' credentials or create a new AI-provider account.
- Added CRM OpenAI + Twilio speech provider. Active agent: `Smart Choice CRM AI Assistant` (ID 1). Uses existing configured `gpt-4o` model and Twilio speech recognition/text-to-speech. No external voice server required.
- Signed live HTTP verification: greeting HTTP 200 with speech collection; synthetic utterance produced an AI reply, next turn persisted, no provider error.
- Second phone test was separately approved for actual AI conversation. CRM call ID 3 completed in 111 seconds. Twilio and CRM agree on completion and duration; speech turns and AI replies were processed without provider error. The bounded history retains the most recent eight caller utterances and eight assistant replies. The handler processed nine callbacks including its final turn.

## Verification

Final recheck: all 47 PHP behavior checks, eight actual MX route checks, and five browser-device lifecycle checks passed. The PHP behavior and route suites also passed on Bluehost. Production had no tracked edits in the call-center module. The approved live AI call was rechecked through the Twilio API and CRM database; both retain completed status and matching duration.

```
php tests/call_center_regression.php
php tests/call_center_routes_regression.php
node tests/call_center_softphone_regression.js
node --check modules/smart_choice_call_center/assets/js/call-center.js
git diff --check
```

Changed PHP files passed syntax checks locally and on production. Regression tests exercise AI streams/webhooks/CRM speech turns, configuration validation, authorization, signed callbacks, real MX routing, repeated Twilio form fields, failure handling, campaign concurrency/retry limits, duplicate callbacks, customer-leg outcomes, stop requests and browser lifecycle. External integrations were verified as described above, not inferred from fixture tests.

## Material limits

The implemented AI provider speaks English in turns rather than streaming full-duplex audio. It identifies itself as AI, uses brief replies, bounds conversation context and turn count, handles stop requests and failures, and has no tools to book appointments, quote prices, or mutate business records. Bounded conversation context is retained in the call record. Provider response storage is disabled. Both approved test calls have audio recording disabled.

Existing inbound routing still forwards to a human business phone; it has not been silently replaced by AI. Scheduled callback processing remains an existing no-op; extension fallback/after-hours routing and disposition-to-lead workflows are not implemented end to end. These limits prevent a blanket claim that every advertised call-center feature is complete.

## Provider references

- https://www.twilio.com/docs/voice/twiml/gather
- https://www.twilio.com/docs/voice/twiml/stream
- https://www.twilio.com/docs/usage/security#validating-requests
- https://developers.openai.com/api/reference/resources/chat/subresources/completions/methods/create
