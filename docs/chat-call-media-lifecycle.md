# Admin audio/video media lifecycle

Call-code verification found reproducible media lifecycle failures:

- Selecting a microphone with an empty negotiated audio sender added another track/transceiver instead of filling the existing sender.
- Permission granted after hangup could leave a microphone active, or attach an old call's microphone/camera to a newer call.
- Repeated camera prompt clicks queued multiple device requests.
- Muting before permission resolved did not mute the newly acquired audio track.
- Opt-in debug traces logged the raw TURN credential response.

The manager now reuses the matching sender, releases failed/stale streams and preserves mute when changing microphones. Call UI media requests belong to the manager and peer that requested them; stale or timed-out streams are stopped, duplicate prompt requests are guarded and newly acquired audio respects mute. Diagnostics keep credentials out of console output. The admin call UI URL now changes with its file modification time so a normal page reload retrieves the repair.

Validation: `chat_call_media_lifecycle_ui.js` reproduces the sender, stale-stream and pending-mute defects before repairs, and checks sender reuse, failed replacement cleanup, late grants, new-call isolation, duplicate requests and camera timeouts afterward. The 16 controlled call/signaling cases, six native signaling endpoints, access/private-channel regressions, JS syntax and PHP syntax pass. Chrome's real WebRTC engine also negotiates two local peers with generated audio/video: both sides connect, receive audio/video packets, mute and release media on hangup. Signaling in this local harness is isolated; no CRM recipient is rung by it.

The live admin token endpoint returns STUN only, with no TURN server. Cross-network/TURN traversal and actual hardware require separate operational checks. The private verification record tracks the signed-in two-account browser test and deployment state.
