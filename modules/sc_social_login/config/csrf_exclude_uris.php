<?php
defined('BASEPATH') or exit('No direct script access allowed');
// Only Apple's cross-site callback. It verifies a single-use state AND an HttpOnly browser cookie.
return ['sc_social_login/oauth/callback/apple'];
