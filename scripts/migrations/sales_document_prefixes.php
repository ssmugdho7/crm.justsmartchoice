<?php
// Additive migration. Existing rows retain NULL and their current displayed numbers.
function sc_migrate_document_prefixes(mysqli $db, string $tablePrefix): void
{
    if (!preg_match('/^[A-Za-z0-9_]*$/D', $tablePrefix)) throw new RuntimeException('Invalid table prefix');
    foreach (['proposals', 'contracts'] as $name) {
        $table = $tablePrefix . $name;
        $columns = $db->query("SHOW COLUMNS FROM `{$table}` LIKE 'prefix'");
        if (!$columns) throw new RuntimeException('Could not inspect ' . $name);
        if ($columns->num_rows === 0 && !$db->query("ALTER TABLE `{$table}` ADD COLUMN `prefix` VARCHAR(50) NULL DEFAULT NULL")) {
            throw new RuntimeException('Could not add prefix to ' . $name);
        }
    }
}
if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
    define('BASEPATH', dirname(__DIR__, 2) . '/system/');
    require dirname(__DIR__, 2) . '/application/config/app-config.php';
    if (($argv[1] ?? '') !== '--apply' || !in_array(parse_url(APP_BASE_URL, PHP_URL_HOST), ['localhost', '127.0.0.1', 'dev.justsmartchoice.com'], true)) {
        fwrite(STDERR, "Usage: php scripts/migrations/sales_document_prefixes.php --apply (dev only)\n"); exit(1);
    }
    $db = new mysqli(APP_DB_HOSTNAME, APP_DB_USERNAME, APP_DB_PASSWORD, APP_DB_NAME);
    $db->set_charset('utf8mb4');
    sc_migrate_document_prefixes($db, defined('APP_DB_PREFIX') ? APP_DB_PREFIX : 'tbl');
    echo "PASS: nullable proposal/contract prefixes ready; existing numbers preserved\n";
}
