<?php

defined('BASEPATH') or exit('No direct script access allowed');

// Data-preserving uninstall by design. Tables, settings, API keys, documents,
// analyses, and signatures are intentionally retained for safe reactivation.
log_activity('Solar Pro module uninstalled/deactivated; module data preserved');
