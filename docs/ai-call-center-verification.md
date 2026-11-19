# AI call center verification - 19 November 2026

## Live findings

- Production source HEAD: `645ad6cfe810927beb7e020c84fd50ec9978a72f`.
- Production contains 467 tracked modifications/deletions. No call-center tracked changes were present at inspection. Those unrelated changes have been preserved; no production source deployment has occurred.
- The call-center Twilio account matches the account open in the browser. Stored credentials successfully authenticated read-only account, incoming-number and application requests (HTTP 200). No secrets were printed.
- The account is active and owns voice-enabled number `+18559066053`.
- The number uses a published Studio flow, “Smart Choice CRM inbound forwarding,” forwarding to the existing business phone. It does not route through the call-center module or an AI agent. Its routing has not been changed.
- Production has an outbound caller ID and AI enabled. Its call-center main number, TwiML App SID, API Key SID and API Key Secret are empty; there are zero AI agents, campaigns and call records.
- An existing TwiML application belongs to the separate Lead Manager feature. Do not replace its URL to configure this module.
- The Twilio console also shows a rejected toll-free messaging registration; voice is enabled. This is not evidence of a voice block.

## Repairs prepared

- Correct outbound TwiML URL and repeated status-event form encoding.
- Validate destinations and provider endpoints before paid calls; fail closed on disabled/missing AI.
- Connect WSS agents with escaped instructions/voice custom parameters; support TwiML webhook redirects with call/agent IDs.
- Ring the staff browser first for REST power calls, instead of dialing the customer and then dialing the customer again.
- Authenticate Twilio webhooks with the installed SDK, checking configured public origin, exact request URI and payload; narrowly exclude only those callbacks from browser CSRF tokens.
- Validate agent/campaign saves and require POST for campaign state changes. Read-only staff access tokens cannot originate calls. Browser webhook validates staff identity and permission.
- Serialize campaign workers, honor business hours/daily limits, release failed reservations, apply retry intervals/attempt limits, and ignore duplicate/late terminal callbacks.
- Report incomplete setup; refresh browser tokens; show initialization failures; restore call controls after rejection/error; add hangup control and refresh script cache version.

## Checks

```
php tests/call_center_regression.php
node tests/call_center_softphone_regression.js
node --check modules/smart_choice_call_center/assets/js/call-center.js
git diff --check
```

The PHP regressions reproduce the original nonexistent outbound action before repair and exercise model/controller behavior with isolated database/network fixtures. They do not call customers. Softphone regressions use a simulated Twilio device. All changed PHP files must pass `php -l`.

## Remaining activation work

1. Reconcile production tracked changes or explicitly authorize a narrowly scoped release method that preserves them. The normal production deployment helper rejects unknown tracked modifications.
2. After the source repair is deployed, create a dedicated TwiML app for this module with POST voice URL `https://crm.justsmartchoice.com/smart_choice_call_center/webhook/browser_outgoing`; configure its SID and an API key/secret privately in Call Center settings. Keep the existing Lead Manager app intact.
3. Set the owned number as main number if incoming CRM routing is desired. Keep Studio business forwarding until an approved replacement has been configured and tested.
4. Supply an existing external AI voice provider endpoint. Twilio provides telephony, not a hosted conversational AI service in this module. WSS providers must implement Twilio bidirectional Media Streams and consume custom parameters. Generic HTTPS webhook providers must return TwiML; their prompts/voices are configured at the provider.
5. Use an explicitly approved test number to verify outbound audio in both directions, hangup, logging, terminal statuses and recording. Verify inbound routing separately if it is changed.

No real calls, provider resource creation, production configuration/database writes, or live source changes were performed during this verification.

## Limits

The callback queue exists but automatic scheduled callback processing is an existing no-op. Extension fallback/after-hours behavior and disposition/lead-conversion workflows are also not implemented end to end in this module. These are not covered by a claim of full functionality. No AI conversation or production call completion has been verified.

## Provider references

- https://www.twilio.com/docs/voice/twiml/stream
- https://www.twilio.com/docs/usage/security#validating-requests
- https://www.twilio.com/docs/voice/api/call-resource#create-a-call-resource
