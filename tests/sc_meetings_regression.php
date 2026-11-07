<?php
define('BASEPATH', __DIR__); define('FCPATH', '/nonexistent/public_html/crm.justsmartchoice/');
$GLOBALS['opt'] = ''; $GLOBALS['logs'] = []; $GLOBALS['staff'] = true;
class H { public $f = [], $a = []; function add_filter($n, $c) { $this->f[$n][] = $c; } function add_action($n, $c) { $this->a[$n][] = $c; } }
$H = new H; function hooks() { global $H; return $H; }
$lang = []; require __DIR__ . '/../modules/sc_admin_fixes/language/english/sc_admin_fixes_lang.php';
function _l($k, $label = '') { global $lang; $line = $lang[$k] ?? $k; try { return sprintf($line, $label); } catch (Throwable $e) { return $line; } }
function e($s) { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); }
function get_option($k) { return $GLOBALS['opt']; } function log_message($l, $m) { $GLOBALS['logs'][] = $m; } function is_staff_logged_in() { return $GLOBALS['staff']; }
require __DIR__ . '/../modules/sc_admin_fixes/includes/meetings.php';
$fails = 0; function ok($c, $m) { global $fails; echo ($c ? 'PASS ' : 'FAIL ') . $m . "\n"; if (!$c) { $fails++; } }
function render() { ob_start(); scaf_render_live_meetings(); return ob_get_clean(); }
// Quick links
// Scratch DB
preg_match("/define\('APP_DB_PASSWORD',\s*'((?:[^'\\\\]|\\\\.)*)'/", file_get_contents('/Users/mugdho/smart_projects/crm.justsmartchoice/application/config/app-config.php'), $m); $pw = stripcslashes($m[1] ?? '');
$pdo = new PDO('mysql:host=127.0.0.1', 'root', $pw, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$pdo->exec('DROP DATABASE IF EXISTS sc_meet_scratch_test; CREATE DATABASE sc_meet_scratch_test; USE sc_meet_scratch_test;
CREATE TABLE users (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, name VARCHAR(80));
CREATE TABLE meet_rooms (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, code CHAR(12), title VARCHAR(120), host_user_id INT UNSIGNED, knock TINYINT(1) DEFAULT 0, ended_at DATETIME NULL, deleted_at DATETIME NULL, created_at DATETIME);
CREATE TABLE meet_participants (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, room_id INT UNSIGNED, user_id INT UNSIGNED NULL, name VARCHAR(80), role ENUM("host","guest"), status ENUM("waiting","joined","denied","left","removed"), joined_at DATETIME, admitted_at DATETIME, last_seen DATETIME, left_at DATETIME NULL);
INSERT INTO meet_rooms (id,code,title,host_user_id,ended_at,created_at) VALUES (1,"abc-defg-hij","Sales <sync>",1,NULL,UTC_TIMESTAMP()),(2,"bcd-efgh-ijk","Ended",1,UTC_TIMESTAMP(),UTC_TIMESTAMP()),(3,"cde-fghi-jkl","Stale",1,NULL,UTC_TIMESTAMP()),(4,"def-ghij-klm","Big",1,NULL,UTC_TIMESTAMP() - INTERVAL 1 HOUR);
INSERT INTO meet_participants (room_id,name,role,status,joined_at,last_seen) VALUES
(1,"Alice","host","joined",UTC_TIMESTAMP(),UTC_TIMESTAMP()),(1,"<b>Bob</b>","guest","joined",UTC_TIMESTAMP(),UTC_TIMESTAMP() - INTERVAL 5 SECOND),
(1,"StaleCarl","guest","joined",UTC_TIMESTAMP(),UTC_TIMESTAMP() - INTERVAL 60 SECOND),(1,"LeftDana","guest","left",UTC_TIMESTAMP(),UTC_TIMESTAMP()),
(2,"EndedEve","host","joined",UTC_TIMESTAMP(),UTC_TIMESTAMP()),(3,"StaleFred","host","joined",UTC_TIMESTAMP(),UTC_TIMESTAMP() - INTERVAL 30 SECOND),
(4,"P1","guest","joined",UTC_TIMESTAMP(),UTC_TIMESTAMP()),(4,"P2","guest","joined",UTC_TIMESTAMP(),UTC_TIMESTAMP()),(4,"P3","guest","joined",UTC_TIMESTAMP(),UTC_TIMESTAMP()),(4,"P4","guest","joined",UTC_TIMESTAMP(),UTC_TIMESTAMP()),(4,"P5","guest","joined",UTC_TIMESTAMP(),UTC_TIMESTAMP()),(4,"P6","guest","joined",UTC_TIMESTAMP(),UTC_TIMESTAMP()),(4,"P7","guest","joined",UTC_TIMESTAMP(),UTC_TIMESTAMP()),(4,"P8","guest","joined",UTC_TIMESTAMP(),UTC_TIMESTAMP()),(4,"P9","guest","joined",UTC_TIMESTAMP(),UTC_TIMESTAMP()),(4,"P10","guest","joined",UTC_TIMESTAMP(),UTC_TIMESTAMP());');
$cfg = '/tmp/scmeet/config.php';
$write = function ($p) use ($cfg) { file_put_contents($cfg, '<?php return ' . var_export(['db_host' => '127.0.0.1', 'db_name' => 'sc_meet_scratch_test', 'db_user' => 'root', 'db_pass' => $p, 'base_url' => 'https://meet.justsmartchoice.com'], true) . ';'); };
try {
    $write($pw); $GLOBALS['opt'] = $cfg; $html = render();
    ok(substr_count($html, 'alert alert-info') === 2, 'only the two live rooms rendered');
    ok(strpos($html, 'P8 +2 more') !== false && strpos($html, 'P9') === false, 'large room shows 8 names then +2 more');
    ok(strpos($html, 'href="https://meet.justsmartchoice.com/abc-defg-hij"') !== false && strpos($html, 'target="_blank" rel="noopener noreferrer"') !== false, 'join URL correct');
    ok(strpos($html, 'Alice, &lt;b&gt;Bob&lt;/b&gt;</p>') !== false && strpos($html, '<b>Bob') === false, 'two active names, HTML escaped');
    ok(strpos($html, 'Sales &lt;sync&gt;') !== false, 'title escaped');
    foreach (['StaleCarl', 'LeftDana', 'EndedEve', 'StaleFred', 'bcd-efgh-ijk', 'cde-fghi-jkl'] as $x) { ok(strpos($html, $x) === false, "excluded $x"); }
    $GLOBALS['staff'] = false; ok(render() === '', 'not rendered for logged-out'); $GLOBALS['staff'] = true;
    $GLOBALS['opt'] = '/tmp/scmeet/missing.php'; $GLOBALS['logs'] = []; ok(render() === '' && count($GLOBALS['logs']) === 1, 'missing config => empty, logged');
    $write('definitely-wrong-' . bin2hex(random_bytes(4))); $GLOBALS['opt'] = $cfg; $GLOBALS['logs'] = []; ok(render() === '' && count($GLOBALS['logs']) === 1, 'bad credentials => empty, logged');
    $write($pw); echo "\nHTML:\n" . render() . "\n";
} finally { $pdo->exec('DROP DATABASE IF EXISTS sc_meet_scratch_test'); @unlink($cfg); }
echo $fails ? "$fails FAILED\n" : "ALL PASSED\n"; exit($fails ? 1 : 0);
