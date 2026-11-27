<?php
// CLI-only, real helper + temporary SQLite and files. Never touches CRM data.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
define('BASEPATH', __DIR__);
define('PR_CHAT_VERSION', 'test');
define('TABLE_CHATGROUPMEMBERS', 'fixture_chatgroupmembers');
define('TABLE_CHATGROUPMESSAGES', 'fixture_chatgroupmessages');
define('TABLE_CHATGROUPS', 'fixture_chatgroups');
define('TABLE_CHATGROUPSHAREDFILES', 'fixture_chatgroupsharedfiles');
$directory = sys_get_temp_dir() . '/chat-retention-' . bin2hex(random_bytes(8));
mkdir($directory, 0700);
define('PR_CHAT_MODULE_GROUPS_UPLOAD_FOLDER', $directory);
function get_option($key) { return ''; }
function prchat_staff_can_chat() { return false; }
function staff_can(...$args) { return false; }
function db_prefix() { return 'fixture_'; }
$activity = [];
function log_activity($message) { $GLOBALS['activity'][] = $message; }
class RetentionDatabaseFixture {
    public $pdo;
    private $clauses = [];
    private $values = [];
    public function __construct() { $this->pdo = new PDO('sqlite::memory:'); $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); }
    public function trans_start() { $this->pdo->beginTransaction(); }
    public function trans_complete() { return $this->pdo->commit(); }
    public function where($key, $value) { return $this->condition('AND', $key, $value); }
    public function or_where($key, $value) { return $this->condition('OR', $key, $value); }
    private function condition($operator, $key, $value) {
        $this->clauses[] = (count($this->clauses) && end($this->clauses) !== '(' ? $operator . ' ' : '') . '"' . $key . '" = ?';
        $this->values[] = $value;
        return $this;
    }
    public function group_start() { $this->clauses[] = (count($this->clauses) ? 'AND ' : '') . '('; return $this; }
    public function group_end() { $this->clauses[] = ')'; return $this; }
    private function execute($sql, $values = []) {
        if ($this->clauses) $sql .= ' WHERE ' . implode(' ', $this->clauses);
        $values = array_merge($values, $this->values);
        $this->clauses = $this->values = [];
        $query = $this->pdo->prepare($sql);
        return $query->execute($values);
    }
    public function delete($table) { return $this->execute('DELETE FROM "' . $table . '"'); }
    public function update($table, $data) {
        $assignments = array_map(static fn($key) => '"' . $key . '" = ?', array_keys($data));
        return $this->execute('UPDATE "' . $table . '" SET ' . implode(', ', $assignments), array_values($data));
    }
    public function rows($table) { return $this->pdo->query('SELECT * FROM "' . $table . '" ORDER BY id')->fetchAll(PDO::FETCH_ASSOC); }
}
$ci = (object) ['db' => new RetentionDatabaseFixture()];
function &get_instance() { return $GLOBALS['ci']; }
require dirname(__DIR__) . '/modules/prchat/helpers/prchat_helper.php';
function check($condition, $message) { if (!$condition) throw new RuntimeException($message); echo "PASS $message\n"; }
try {
    $db = $ci->db;
    $db->pdo->exec("CREATE TABLE fixture_chatgroupmembers (id INTEGER PRIMARY KEY, group_id INTEGER, member_id INTEGER);
        CREATE TABLE fixture_chatgroupmessages (id INTEGER PRIMARY KEY, group_id INTEGER, sender_id INTEGER, message TEXT);
        CREATE TABLE fixture_chatgroups (id INTEGER PRIMARY KEY, created_by_id INTEGER);
        CREATE TABLE fixture_chatgroupsharedfiles (id INTEGER PRIMARY KEY, group_id INTEGER, sender_id INTEGER, file_name TEXT);
        CREATE TABLE fixture_chatmessages (id INTEGER PRIMARY KEY, sender_id INTEGER, reciever_id INTEGER, message TEXT);
        CREATE TABLE fixture_chatsettings (id INTEGER PRIMARY KEY, user_id INTEGER);
        INSERT INTO fixture_chatgroupmembers VALUES (1, 10, 7), (2, 10, 9), (3, 20, 7);
        INSERT INTO fixture_chatgroupmessages VALUES (1, 10, 7, 'Company plans'), (2, 10, 9, 'Unrelated reply'), (3, 20, 7, 'voice.ogg');
        INSERT INTO fixture_chatgroups VALUES (10, 7), (20, 9);
        INSERT INTO fixture_chatgroupsharedfiles VALUES (1, 10, 7, 'plans.pdf'), (2, 10, 9, 'other.pdf');
        INSERT INTO fixture_chatmessages VALUES (1, 7, 9, 'direct outgoing'), (2, 9, 7, 'direct incoming'), (3, 9, 12, 'unrelated direct');
        INSERT INTO fixture_chatsettings VALUES (1, 7), (2, 9);");
    file_put_contents($directory . '/plans.pdf', '%PDF-1.7 company document');
    file_put_contents($directory . '/voice.ogg', 'OggS voice recording');
    file_put_contents($directory . '/other.pdf', '%PDF-1.7 unrelated document');
    $fileHashes = [];
    foreach (glob($directory . '/*') as $file) $fileHashes[$file] = hash_file('sha256', $file);
    $before = $db->rows(TABLE_CHATGROUPMESSAGES);
    foreach ([['id' => 7, 'transfer_data_to' => 7], ['id' => 7, 'transfer_data_to' => 0], ['id' => '7invalid', 'transfer_data_to' => 11], ['transfer_data_to' => 11]] as $invalid) {
        check(pr_chat_staff_member_data_transfer($invalid) === false, 'Invalid offboarding transfer rejected');
        check($db->rows(TABLE_CHATGROUPMESSAGES) === $before, 'Rejected transfer leaves company messages unchanged');
    }
    check(pr_chat_staff_member_data_transfer(['id' => 7, 'transfer_data_to' => 11]) === true, 'Native offboarding hook completes');
    $messages = $db->rows(TABLE_CHATGROUPMESSAGES);
    check(count($messages) === 3 && $messages[0]['sender_id'] === 11 && $messages[2]['sender_id'] === 11, 'Departing employee group messages remain with transfer recipient');
    check($messages[0]['message'] === 'Company plans' && $messages[1] === $before[1] && $messages[2]['message'] === 'voice.ogg', 'Message bodies and unrelated author remain unchanged');
    $files = $db->rows(TABLE_CHATGROUPSHAREDFILES);
    check(count($files) === 2 && $files[0]['sender_id'] === 11 && $files[1]['sender_id'] === 9, 'Shared company file metadata retained without changing other uploaders');
    foreach ($fileHashes as $file => $hash) check(is_file($file) && hash_file('sha256', $file) === $hash, 'Original attachment bytes retained: ' . basename($file));
    check($db->rows(TABLE_CHATGROUPMEMBERS) === [['id' => 2, 'group_id' => 10, 'member_id' => 9]], 'Departing employee removed from group memberships');
    check($db->rows(TABLE_CHATGROUPS) === [['id' => 10, 'created_by_id' => 11], ['id' => 20, 'created_by_id' => 9]], 'Group ownership transfers and unrelated owner remains unchanged');
    check($db->rows('fixture_chatmessages') === [['id' => 3, 'sender_id' => 9, 'reciever_id' => 12, 'message' => 'unrelated direct']], 'Existing private-conversation cleanup preserved');
    check($db->rows('fixture_chatsettings') === [['id' => 2, 'user_id' => 9]], 'Departing employee chat settings removed');
    check(count($activity) === 1 && strpos($activity[0], 'Original Staff ID: 7') !== false && strpos($activity[0], 'Transferred To: 11') !== false, 'Activity records original staff and transfer recipient for provenance');
} finally {
    foreach (glob($directory . '/*') as $file) unlink($file);
    rmdir($directory);
}
