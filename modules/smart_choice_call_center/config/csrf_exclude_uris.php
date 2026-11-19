<?php
defined('BASEPATH') or exit('No direct script access allowed');
// These callbacks are authenticated by Twilio's signature in Webhook::__construct().
return ['smart_choice_call_center/webhook/(incoming|ivr_route|outgoing_twiml|browser_outgoing|browser_complete|ai_turn|status|recording)'];
