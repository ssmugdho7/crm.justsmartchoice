<?php
defined('BASEPATH') or exit('No direct script access allowed');
// Machine-to-machine endpoint uses a high-entropy activation key, never a session cookie.
return ['sc_module_licensing/license_api/check'];
