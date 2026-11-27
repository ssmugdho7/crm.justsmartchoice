<?php
// Explicit, additive migration. Run from CLI after taking a private leads backup.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
define('BASEPATH', __DIR__ . '/../system/');
require __DIR__ . '/../application/config/app-config.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$db = new mysqli(APP_DB_HOSTNAME, APP_DB_USERNAME, APP_DB_PASSWORD, APP_DB_NAME);
$table = (defined('APP_DB_PREFIX') ? APP_DB_PREFIX : 'tbl') . 'leads';
if (!preg_match('/^[a-zA-Z0-9_]+$/', $table)) throw new RuntimeException('Invalid table prefix');
$column = $db->query("SHOW COLUMNS FROM `$table` LIKE 'additional_assigned_staff'");
if (!$column->num_rows) $db->query("ALTER TABLE `$table` ADD COLUMN additional_assigned_staff TEXT NULL");
echo "Lead assignee schema ready\n";
