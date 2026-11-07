<?php
// Standalone regression for signature DB backup: php tests/signature_backup_regression.php
define('BASEPATH', __DIR__);
$root = sys_get_temp_dir() . '/sigbackup_' . bin2hex(random_bytes(4)) . '/';
define('UPLOAD_ROOT', $root);

function db_prefix() { return 'tbl'; }
$GLOBALS['logs'] = [];
function log_message($level, $msg) { $GLOBALS['logs'][] = "$level: $msg"; }
function get_upload_path_by_type($type) { return UPLOAD_ROOT . ['contract' => 'contracts', 'estimate' => 'estimates', 'proposal' => 'proposals'][$type] . '/'; }
function unique_filename($dir, $name) { return $name; }
function _l($line, $args = []) { $f = ['signature_image_missing_text' => 'Signature image missing — signed by %s on %s, IP %s', 'signature_on_file_text' => 'Signature on file — signed by %s on %s'][$line]; return vsprintf($f, (array) $args); }
function _dt($d) { return $d; }

class FakeResult { public function __construct(private ?object $r) {} public function row() { return $this->r; } }
class FakeDb
{
    public $db_debug = true; public $fail = false; private array $w = []; private PDO $pdo; private bool $created = false;
    public function __construct() { $this->pdo = new PDO('sqlite::memory:'); $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); }
    public function table_exists($t) { $this->chk(); return $this->created; }
    public function query($sql) { $this->chk(); $this->pdo->exec('CREATE TABLE tblsignature_images (id INTEGER PRIMARY KEY, rel_type TEXT, rel_id INT, file_name TEXT, mime TEXT, data BLOB, created_at TEXT, UNIQUE(rel_type,rel_id,file_name))'); $this->created = true; return true; }
    public function select($s) { return $this; }
    public function where($k, $v = null) { $this->w = array_merge($this->w, is_array($k) ? $k : [$k => $v]); return $this; }
    private function chk() { if ($this->fail) { throw new RuntimeException('db down'); } }
    private function cond() { $c = []; foreach ($this->w as $k => $v) { $c[] = "$k = :$k"; } $w = $this->w; $this->w = []; return [$c ? ' WHERE ' . implode(' AND ', $c) : '', $w]; }
    public function replace($t, $d) { $this->chk(); $st = $this->pdo->prepare("REPLACE INTO $t (" . implode(',', array_keys($d)) . ') VALUES (:' . implode(',:', array_keys($d)) . ')'); return $st->execute($d); }
    public function get($t) { $this->chk(); [$c, $p] = $this->cond(); $st = $this->pdo->prepare("SELECT * FROM $t$c"); $st->execute($p); $r = $st->fetch(PDO::FETCH_OBJ); return new FakeResult($r ?: null); }
    public function delete($t) { $this->chk(); [$c, $p] = $this->cond(); return $this->pdo->prepare("DELETE FROM $t$c")->execute($p); }
    public function count() { return (int) $this->pdo->query('SELECT COUNT(*) FROM tblsignature_images')->fetchColumn(); }
}
$CI = new stdClass(); $CI->db = new FakeDb();
function &get_instance() { global $CI; return $CI; }

require __DIR__ . '/../application/helpers/signature_backup_helper.php';
// Load only the two save functions from misc_helper to avoid the rest of its CI dependencies.
$src = file_get_contents(__DIR__ . '/../application/helpers/misc_helper.php');
foreach (['process_digital_signature_image', 'process_digital_initials_image'] as $fn) {
    preg_match('/function ' . $fn . '\(.*?\n}\n/s', $src, $m) or exit("cannot extract $fn\n");
    eval($m[0]);
}

$fails = 0;
function check($name, $ok) { global $fails; echo ($ok ? 'PASS' : 'FAIL') . " - $name\n"; if (!$ok) { $fails++; } }

$png = base64_encode("\x89PNG\r\n\x1a\nfake-signature");
$dir = UPLOAD_ROOT . 'contracts/42';

// 1. store copy on save
check('signature saved', process_digital_signature_image('data:image/png;base64,' . $png, $dir) === true);
$file = $GLOBALS['processed_digital_signature'];
check('initials saved', process_digital_initials_image($png, $dir) === 'initials.png');
check('DB copies stored (signature + initials)', $CI->db->count() === 2 && signature_backup_exists('contract', 42, $file) && signature_backup_exists('contract', 42, 'initials.png'));
process_digital_signature_image($png, UPLOAD_ROOT . 'estimates/7');
check('estimate rel_type derived from folder', signature_backup_exists('estimate', 7, 'signature.png'));

// 2. restore missing file from DB
unlink("$dir/$file"); @rmdir($dir) ?: array_map('unlink', glob("$dir/*")); @rmdir($dir);
check('file deleted before restore', !is_file("$dir/$file"));
check('ensure_signature_file restores from DB', ensure_signature_file('contract', 42, $file) === true);
check('restored bytes identical', file_get_contents("$dir/$file") === base64_decode($png));
check('restored file mode 0644', (fileperms("$dir/$file") & 0777) === 0644);
check('present file returns true', ensure_signature_file('contract', 42, $file) === true);

// 3. missing + no copy returns false
check('missing file without DB copy returns false', ensure_signature_file('proposal', 99, 'signature.png') === false);
check('empty file name returns false', ensure_signature_file('contract', 42, '') === false);

// 4. clear removes copy
signature_backup_delete('contract', 42);
check('clear removes all contract DB copies', !signature_backup_exists('contract', 42, $file) && !signature_backup_exists('contract', 42, 'initials.png'));
check('clear leaves other documents', signature_backup_exists('estimate', 7, 'signature.png'));
unlink("$dir/$file");
check('cleared signature not restored', ensure_signature_file('contract', 42, $file) === false);

// 5. DB failure never fails signing
$CI->db->fail = true;
check('signing succeeds while DB is down', process_digital_signature_image($png, UPLOAD_ROOT . 'proposals/5') === true && is_file(UPLOAD_ROOT . 'proposals/5/signature.png'));
check('DB failure logged', (bool) preg_grep('/Signature backup failed/', $GLOBALS['logs']));
check('db_debug restored after failure', $CI->db->db_debug === true);
$CI->db->fail = false;

// 6. notice text
$rec = (object) ['acceptance_firstname' => 'Jane', 'acceptance_lastname' => 'Doe', 'acceptance_date' => '2025-01-02 10:00:00', 'acceptance_ip' => '1.2.3.4'];
check('admin missing notice', signature_missing_text($rec) === 'Signature image missing — signed by Jane Doe on 2025-01-02 10:00:00, IP 1.2.3.4');
check('customer neutral notice', signature_missing_text($rec, true) === 'Signature on file — signed by Jane Doe on 2025-01-02 10:00:00');

exec('rm -rf ' . escapeshellarg($root));
echo $fails ? "$fails FAILED\n" : "ALL PASSED\n";
exit($fails ? 1 : 0);
