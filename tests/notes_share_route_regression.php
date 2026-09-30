<?php

// Run with: php tests/notes_share_route_regression.php
define('BASEPATH', __DIR__);

$route = [];
require dirname(__DIR__) . '/modules/notes/config/routes.php';

$pattern = 'notes/share/(:any)';
$target = $route[$pattern] ?? null;

if ($target !== 'share/index/$1') {
    fwrite(STDERR, "FAIL: Notes share route must resolve inside the already-selected Notes module.\n");
    exit(1);
}

$token = str_repeat('a', 48);
$regex = '#^' . str_replace([':any', ':num'], ['.+', '[0-9]+'], $pattern) . '$#';
$resolved = preg_replace($regex, $target, 'notes/share/' . $token);

if ($resolved !== 'share/index/' . $token) {
    fwrite(STDERR, "FAIL: Notes share URL did not resolve to the public Share controller.\n");
    exit(1);
}

echo "PASS: Notes share URL resolves to notes/share::index.\n";
