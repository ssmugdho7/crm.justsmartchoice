<?php
defined('BASEPATH') or exit('No direct script access allowed');

// Activation is idempotent. Perfex still owns and records module migration versions.
foreach ([100, 101, 102, 103, 104] as $version) {
    $file = __DIR__ . '/migrations/' . $version . '_version_' . $version . '.php';
    require_once $file;
    $class = 'Migration_Version_' . $version;
    (new $class())->up();
}
