<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Smart_merge_fields_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function scan_database()
    {
        $this->db->empty_table(db_prefix() . 'smart_merge_scan_cache');
        $tables = $this->db->list_tables();
        $count = 0;
        foreach ($tables as $table) {
            if (!$this->is_allowed_table($table)) {
                continue;
            }
            $fields = $this->db->field_data($table);
            foreach ($fields as $field) {
                if (!$this->is_allowed_field($field->name)) {
                    continue;
                }
                $fieldName = $field->name;
                $mergeTag = $this->build_merge_tag($table, $fieldName);
                $this->db->replace(db_prefix() . 'smart_merge_scan_cache', [
                    'table_name' => $table,
                    'field_name' => $fieldName,
                    'field_type' => isset($field->type) ? $field->type : '',
                    'is_primary' => (int) ($fieldName === 'id' || substr($fieldName, -3) === '_id'),
                    'is_email' => (int) (stripos($fieldName, 'email') !== false),
                    'is_phone' => (int) (stripos($fieldName, 'phone') !== false || stripos($fieldName, 'mobile') !== false),
                    'is_name' => (int) (stripos($fieldName, 'name') !== false || stripos($fieldName, 'company') !== false),
                    'merge_tag' => $mergeTag,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
                $count++;
            }
        }
        $this->log_action(null, 'Database Scan', 'Database fields scanned successfully.', $count);
        return $count;
    }

    public function get_fields($keyword = '')
    {
        if ($keyword !== '') {
            $this->db->group_start();
            $this->db->like('table_name', $keyword);
            $this->db->or_like('field_name', $keyword);
            $this->db->or_like('merge_tag', $keyword);
            $this->db->group_end();
        }
        return $this->db->order_by('table_name', 'asc')->order_by('field_name', 'asc')->get(db_prefix() . 'smart_merge_scan_cache')->result_array();
    }

    public function get_tables_summary()
    {
        return $this->db->select('table_name, COUNT(id) as total_fields')
            ->group_by('table_name')
            ->order_by('table_name', 'asc')
            ->get(db_prefix() . 'smart_merge_scan_cache')
            ->result_array();
    }

    public function get_mappings()
    {
        return $this->db->order_by('id', 'desc')->get(db_prefix() . 'smart_merge_mappings')->result_array();
    }

    public function get_mapping($id)
    {
        return $this->db->where('id', (int) $id)->get(db_prefix() . 'smart_merge_mappings')->row_array();
    }

    public function save_mapping($data)
    {
        $payload = [
            'name' => trim($data['name'] ?? ''),
            'source_table' => trim($data['source_table'] ?? ''),
            'source_field' => trim($data['source_field'] ?? ''),
            'target_table' => trim($data['target_table'] ?? ''),
            'target_field' => trim($data['target_field'] ?? ''),
            'relation_source_field' => trim($data['relation_source_field'] ?? ''),
            'relation_target_field' => trim($data['relation_target_field'] ?? ''),
            'status' => trim($data['status'] ?? 'active'),
            'direction' => trim($data['direction'] ?? 'source_to_target'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        if ($payload['name'] === '') {
            $payload['name'] = $this->humanize($payload['source_table']) . ' ' . $this->humanize($payload['source_field']) . ' To ' . $this->humanize($payload['target_table']) . ' ' . $this->humanize($payload['target_field']);
        }
        if (empty($data['id'])) {
            $payload['created_by'] = get_staff_user_id();
            $payload['created_at'] = date('Y-m-d H:i:s');
            $this->db->insert(db_prefix() . 'smart_merge_mappings', $payload);
            $id = $this->db->insert_id();
        } else {
            $id = (int) $data['id'];
            $this->db->where('id', $id)->update(db_prefix() . 'smart_merge_mappings', $payload);
        }
        $this->log_action($id, 'Mapping Saved', 'Merge field mapping saved successfully.', 1);
        return $id;
    }

    public function delete_mapping($id)
    {
        $this->db->where('id', (int) $id)->delete(db_prefix() . 'smart_merge_mappings');
        $this->log_action((int) $id, 'Mapping Deleted', 'Merge field mapping deleted.', 1);
        return true;
    }

    public function sync_mapping($id)
    {
        $mapping = $this->get_mapping($id);
        if (!$mapping) {
            return ['success' => false, 'message' => 'Mapping was not found.', 'affected' => 0];
        }
        if (!$this->is_allowed_table($mapping['source_table']) || !$this->is_allowed_table($mapping['target_table'])) {
            return ['success' => false, 'message' => 'One selected table is not allowed.', 'affected' => 0];
        }
        if (!$this->field_exists($mapping['source_table'], $mapping['source_field']) || !$this->field_exists($mapping['target_table'], $mapping['target_field'])) {
            return ['success' => false, 'message' => 'One selected field does not exist.', 'affected' => 0];
        }
        if (empty($mapping['relation_source_field']) || empty($mapping['relation_target_field'])) {
            return ['success' => false, 'message' => 'Relation fields are required before database synchronization.', 'affected' => 0];
        }
        if (!$this->field_exists($mapping['source_table'], $mapping['relation_source_field']) || !$this->field_exists($mapping['target_table'], $mapping['relation_target_field'])) {
            return ['success' => false, 'message' => 'One selected relation field does not exist.', 'affected' => 0];
        }

        $sourceTable = $this->db->protect_identifiers($mapping['source_table'], true);
        $targetTable = $this->db->protect_identifiers($mapping['target_table'], true);
        $sourceField = $this->db->protect_identifiers($mapping['source_field']);
        $targetField = $this->db->protect_identifiers($mapping['target_field']);
        $relationSource = $this->db->protect_identifiers($mapping['relation_source_field']);
        $relationTarget = $this->db->protect_identifiers($mapping['relation_target_field']);

        $sql = 'UPDATE ' . $targetTable . ' target_table_ref INNER JOIN ' . $sourceTable . ' source_table_ref ON target_table_ref.' . $relationTarget . ' = source_table_ref.' . $relationSource . ' SET target_table_ref.' . $targetField . ' = source_table_ref.' . $sourceField . ' WHERE source_table_ref.' . $sourceField . ' IS NOT NULL AND source_table_ref.' . $sourceField . ' <> ""';
        $this->db->query($sql);
        $affected = $this->db->affected_rows();
        $this->db->where('id', (int) $id)->update(db_prefix() . 'smart_merge_mappings', ['last_run' => date('Y-m-d H:i:s')]);
        $this->log_action((int) $id, 'Mapping Synchronized', 'Merge field synchronization completed.', $affected);
        return ['success' => true, 'message' => 'Synchronization completed successfully.', 'affected' => $affected];
    }

    public function create_lead_custom_field($name, $type = 'input')
    {
        $name = trim($name);
        if ($name === '') {
            return false;
        }
        if (!$this->db->table_exists(db_prefix() . 'customfields')) {
            return false;
        }
        $exists = $this->db->where('fieldto', 'leads')->where('name', $name)->get(db_prefix() . 'customfields')->row();
        if ($exists) {
            return (int) $exists->id;
        }
        $this->db->insert(db_prefix() . 'customfields', [
            'fieldto' => 'leads',
            'name' => $name,
            'slug' => 'leads_' . strtolower(preg_replace('/[^a-zA-Z0-9]+/', '_', $name)),
            'required' => 0,
            'type' => $type,
            'options' => '',
            'display_inline' => 0,
            'field_order' => 0,
            'active' => 1,
            'show_on_pdf' => 1,
            'show_on_ticket_form' => 0,
            'only_admin' => 0,
            'show_on_table' => 1,
            'show_on_client_portal' => 0,
            'disalow_client_to_edit' => 0,
            'bs_column' => 12,
        ]);
        $id = $this->db->insert_id();
        $this->log_action(null, 'Lead Field Created', 'Lead custom field created: ' . $name, 1);
        return $id;
    }

    public function save_settings($data)
    {
        foreach ($data as $key => $value) {
            $this->db->replace(db_prefix() . 'smart_merge_settings', [
                'setting_key' => $key,
                'setting_value' => is_array($value) ? json_encode($value) : (string) $value,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }
        return true;
    }

    public function get_settings()
    {
        $rows = $this->db->get(db_prefix() . 'smart_merge_settings')->result_array();
        $settings = [];
        foreach ($rows as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        return $settings;
    }

    public function health_check()
    {
        $checks = [];
        $required = [
            db_prefix() . 'smart_merge_mappings',
            db_prefix() . 'smart_merge_scan_cache',
            db_prefix() . 'smart_merge_logs',
            db_prefix() . 'smart_merge_settings',
        ];
        foreach ($required as $table) {
            $checks[] = [
                'name' => 'Table ' . $this->humanize($table),
                'status' => $this->db->table_exists($table) ? 'Passed' : 'Failed',
                'message' => $this->db->table_exists($table) ? 'Table exists.' : 'Table is missing.',
            ];
        }
        $scanCount = $this->db->count_all_results(db_prefix() . 'smart_merge_scan_cache');
        $checks[] = ['name' => 'Field Scan Cache', 'status' => $scanCount > 0 ? 'Passed' : 'Warning', 'message' => $scanCount . ' fields discovered.'];
        $mappingCount = $this->db->count_all_results(db_prefix() . 'smart_merge_mappings');
        $checks[] = ['name' => 'Active Mapping Records', 'status' => 'Passed', 'message' => $mappingCount . ' mappings stored.'];
        return $checks;
    }

    public function get_logs($limit = 100)
    {
        return $this->db->order_by('id', 'desc')->limit((int) $limit)->get(db_prefix() . 'smart_merge_logs')->result_array();
    }

    public function log_action($mappingId, $action, $message, $recordsAffected = 0)
    {
        $this->db->insert(db_prefix() . 'smart_merge_logs', [
            'mapping_id' => $mappingId,
            'action' => $action,
            'message' => $message,
            'records_affected' => (int) $recordsAffected,
            'created_by' => function_exists('get_staff_user_id') ? get_staff_user_id() : null,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function export_mappings_csv()
    {
        $rows = $this->get_mappings();
        $out = fopen('php://temp', 'r+');
        fputcsv($out, ['Name', 'Source Table', 'Source Field', 'Target Table', 'Target Field', 'Relation Source Field', 'Relation Target Field', 'Status', 'Last Run']);
        foreach ($rows as $row) {
            fputcsv($out, [$row['name'], $row['source_table'], $row['source_field'], $row['target_table'], $row['target_field'], $row['relation_source_field'], $row['relation_target_field'], $row['status'], $row['last_run']]);
        }
        rewind($out);
        return stream_get_contents($out);
    }

    private function build_merge_tag($table, $field)
    {
        $cleanTable = preg_replace('/^' . preg_quote(db_prefix(), '/') . '/', '', $table);
        return '{' . strtolower($cleanTable) . '.' . strtolower($field) . '}';
    }

    private function is_allowed_table($table)
    {
        if (stripos($table, db_prefix() . 'sessions') === 0) {
            return false;
        }
        if (stripos($table, db_prefix() . 'options') === 0) {
            return false;
        }
        if (stripos($table, db_prefix() . 'user_meta') === 0) {
            return false;
        }
        return true;
    }

    private function is_allowed_field($field)
    {
        $blocked = ['password', 'pass', 'remember_token', 'token', 'authtoken', 'secret', 'hash'];
        foreach ($blocked as $word) {
            if (stripos($field, $word) !== false) {
                return false;
            }
        }
        return true;
    }

    private function field_exists($table, $field)
    {
        return $this->db->field_exists($field, $table);
    }

    private function humanize($text)
    {
        $text = preg_replace('/^' . preg_quote(db_prefix(), '/') . '/', '', $text);
        $text = str_replace(['_', '-'], ' ', $text);
        return ucwords(trim($text));
    }
}
