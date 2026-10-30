<?php
// Explicit CLI-only content upgrade. Never run through a web request.
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit;
}

$apply = in_array('--apply', $argv, true);
$backupDirectory = null;
foreach ($argv as $argument) {
    if (strpos($argument, '--backup-dir=') === 0) {
        $backupDirectory = substr($argument, strlen('--backup-dir='));
    }
}
$root = dirname(__DIR__, 2);
define('BASEPATH', $root . '/system/');
require $root . '/application/config/app-config.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$db = new mysqli(APP_DB_HOSTNAME, APP_DB_USERNAME, APP_DB_PASSWORD, APP_DB_NAME);
$db->set_charset('utf8mb4');

$replacements = [
    'https://www.facebook.com/profile.php?id=100079490918155&amp;sk=reviews' => 'https://www.facebook.com/profile.php?id=61594271759671',
    'https://www.facebook.com/profile.php?id=100079490918155&sk=reviews' => 'https://www.facebook.com/profile.php?id=61594271759671',
    'https://www.facebook.com/smartchoicsolar' => 'https://www.facebook.com/profile.php?id=61594271759671',
    'https://www.facebook.com/SmartChoiceUSA' => 'https://www.facebook.com/profile.php?id=61594271759671',
    'https://www.instagram.com/justsmartchoice/' => 'https://www.instagram.com/smartchoicecontra/',
    'https://www.pinterest.com/justsmartchoice' => 'https://www.pinterest.com/haroldcabrerausa/',
    'https://www.pinterest.com/986358893297026176/' => 'https://www.pinterest.com/haroldcabrerausa/',
    'https://www.youtube.com/@Justsmartchoice' => 'https://www.youtube.com/@SmartChoiceContractorUSA',
];
$targets = [
    ['table' => 'tbloptions', 'id' => 'id', 'content' => 'value', 'where' => "name NOT LIKE 'old\\_%'"],
    ['table' => 'tblemailtemplates', 'id' => 'emailtemplateid', 'content' => 'message', 'where' => '1=1'],
    ['table' => 'tblsmart_choice_links', 'id' => 'id', 'content' => 'url', 'where' => '1=1'],
];
$changes = [];
$counts = [];
foreach ($targets as $target) {
    $table = $target['table'];
    $counts[$table] = 0;
    $rows = $db->query("SELECT `{$target['id']}` AS id, `{$target['content']}` AS content FROM `$table` WHERE {$target['where']}");
    foreach ($rows as $row) {
        $new = strtr($row['content'], $replacements);
        if ($new !== $row['content']) {
            $changes[] = $target + ['row_id' => (int) $row['id'], 'before' => $row['content'], 'after' => $new];
            $counts[$table]++;
        }
    }
}
if (!$apply || !$changes) {
    echo json_encode(['mode' => $apply ? 'applied' : 'dry-run', 'changed_rows' => $counts]) . PHP_EOL;
    exit;
}
foreach (array_unique(array_column($changes, 'table')) as $table) {
    $engine = $db->query("SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='$table'")->fetch_assoc();
    if (!$engine || strtoupper($engine['ENGINE']) !== 'INNODB') {
        throw new RuntimeException('Transactional tables are required; no rows changed.');
    }
}
// Preserve only affected content, outside the web root, before any database write.
if (!$backupDirectory || $backupDirectory[0] !== '/' || is_link($backupDirectory)) {
    throw new RuntimeException('An absolute private --backup-dir is required.');
}
umask(0077);
if (!is_dir($backupDirectory) && !mkdir($backupDirectory, 0700, true)) {
    throw new RuntimeException('Cannot create private backup directory.');
}
$resolvedBackup = realpath($backupDirectory);
if (!$resolvedBackup || $resolvedBackup === $root || strpos($resolvedBackup, $root . '/') === 0 ||
    strpos($resolvedBackup, '/public_html/') !== false) {
    throw new RuntimeException('Backups must be outside public_html and the CRM source.');
}
if (!chmod($resolvedBackup, 0700)) {
    throw new RuntimeException('Cannot protect backup directory.');
}
$backupFile = $resolvedBackup . '/social-links-before.json';
$handle = fopen($backupFile, 'x');
if (!$handle) {
    throw new RuntimeException('Cannot create backup; use a new directory.');
}
$payload = json_encode($changes, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
if (fwrite($handle, $payload) !== strlen($payload)) {
    fclose($handle);
    throw new RuntimeException('Backup write failed; no rows changed.');
}
fclose($handle);
if (file_get_contents($backupFile) !== $payload) {
    throw new RuntimeException('Backup verification failed; no rows changed.');
}
$db->begin_transaction();
try {
    foreach ($changes as $change) {
        $statement = $db->prepare("UPDATE `{$change['table']}` SET `{$change['content']}`=? WHERE `{$change['id']}`=? AND BINARY `{$change['content']}`=?");
        $statement->bind_param('sis', $change['after'], $change['row_id'], $change['before']);
        $statement->execute();
        if ($statement->affected_rows !== 1) {
            throw new RuntimeException('Content changed since inspection; transaction canceled.');
        }
    }
    $db->commit();
} catch (Throwable $error) {
    $db->rollback();
    throw $error;
}
echo json_encode(['mode' => 'applied', 'changed_rows' => $counts, 'backup' => $backupFile]) . PHP_EOL;
