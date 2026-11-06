<?php
define('BASEPATH', true);

function db_prefix() { return 'tbl'; }

final class PermitTestDb
{
    public $fields = [];
    public $permits = [];
    public $tableExists = true;
    public function select(...$args) { return $this; }
    public function from(...$args) { return $this; }
    public function join(...$args) { return $this; }
    public function where(...$args) { return $this; }
    public function order_by(...$args) { return $this; }
    public function table_exists($table) { return $this->tableExists; }
    public function get($table = null)
    {
        $rows = $table === null ? $this->fields : ($table === 'tblsce_permits' ? $this->permits : []);
        return new class($rows) {
            public function __construct(private array $rows) {}
            public function result_array() { return $this->rows; }
        };
    }
}

function &get_instance()
{
    return $GLOBALS['ci'];
}

function check($condition, $message)
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

require dirname(__DIR__).'/modules/sc_admin_fixes/includes/permit_controls.php';
$ci = (object) ['db' => new PermitTestDb()];
$ci->db->fields = [['id' => 8, 'name' => 'Permit Needed?', 'value' => 'Yes']];
check(scaf_project_finish_requires_inspection(12), 'Missing permit must block project completion.');
$ci->db->permits = [['inspection_status' => 'final_passed'], ['inspection_status' => 'rough']];
check(scaf_project_finish_requires_inspection(12), 'Every required permit needs a passed final.');
$ci->db->permits[1]['inspection_status'] = 'final_passed';
check(!scaf_project_finish_requires_inspection(12), 'Passed finals permit completion.');
$ci->db->tableExists = false;
check(scaf_project_finish_requires_inspection(12, [8 => 'No']), 'Changing permit requirement and closing in the same request cannot bypass the check.');
$ci->db->fields[0]['value'] = '';
check(scaf_project_finish_requires_inspection(12, [8 => 'Yes']), 'A newly submitted permit requirement is honored.');
check(!scaf_project_finish_requires_inspection(12), 'No explicit requirement preserves the normal workflow.');
check(scaf_permit_contact_link('+1 (813) 555-0101') === '+18135550101', 'Valid phone remains dialable.');
check(scaf_permit_contact_link('javascript:1234567890') === '', 'Unsafe phone scheme is refused.');
echo "PASS permit completion guard and mobile inspector links\n";
