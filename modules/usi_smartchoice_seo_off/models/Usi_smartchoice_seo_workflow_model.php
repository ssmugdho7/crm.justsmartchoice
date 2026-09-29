<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Usi_smartchoice_seo_workflow_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('usi_smartchoice_seo/usi_smartchoice_seo');
        usi_smartchoice_seo_ensure_schema();
    }

    public function get_workflows(array $filters = []): array
    {
        if (!usi_smartchoice_seo_table_exists('usi_ai_workflows')) { return []; }
        $this->db->from(db_prefix() . 'usi_ai_workflows');
        if (isset($filters['is_active']) && $filters['is_active'] !== '') { $this->db->where('is_active', (int) $filters['is_active']); }
        if (!empty($filters['trigger_area'])) { $this->db->where('trigger_area', $filters['trigger_area']); }
        if (!empty($filters['workflow_type'])) { $this->db->like('workflow_type', $filters['workflow_type']); }
        return $this->db->order_by('id', 'DESC')->limit(100)->get()->result_array();
    }

    public function get_workflow(int $id): ?array
    {
        if (!usi_smartchoice_seo_table_exists('usi_ai_workflows')) { return null; }
        $row = $this->db->where('id', $id)->get(db_prefix() . 'usi_ai_workflows')->row_array();
        return $row ?: null;
    }

    public function get_steps(int $workflowId): array
    {
        if (!usi_smartchoice_seo_table_exists('usi_ai_workflow_steps')) { return []; }
        return $this->db->where('workflow_id', $workflowId)->order_by('step_order', 'ASC')->get(db_prefix() . 'usi_ai_workflow_steps')->result_array();
    }

    public function get_runs(int $workflowId = 0): array
    {
        if (!usi_smartchoice_seo_table_exists('usi_ai_workflow_runs')) { return []; }
        if ($workflowId > 0) { $this->db->where('workflow_id', $workflowId); }
        return $this->db->order_by('id', 'DESC')->limit(50)->get(db_prefix() . 'usi_ai_workflow_runs')->result_array();
    }

    public function get_logs(int $runId = 0): array
    {
        if (!usi_smartchoice_seo_table_exists('usi_ai_workflow_logs')) { return []; }
        if ($runId > 0) { $this->db->where('workflow_run_id', $runId); }
        return $this->db->order_by('id', 'DESC')->limit(100)->get(db_prefix() . 'usi_ai_workflow_logs')->result_array();
    }

    public function save_workflow(array $data, int $id = 0): int
    {
        $now = date('Y-m-d H:i:s');
        $row = [
            'workflow_name' => trim((string)($data['workflow_name'] ?? '')),
            'workflow_type' => trim((string)($data['workflow_type'] ?? 'manual')),
            'trigger_area' => trim((string)($data['trigger_area'] ?? 'manual')),
            'trigger_event' => trim((string)($data['trigger_event'] ?? 'manual_run')),
            'conditions_json' => trim((string)($data['conditions_json'] ?? '')),
            'is_active' => isset($data['is_active']) ? 1 : 0,
            'description' => trim((string)($data['description'] ?? '')),
            'updated_at' => $now,
        ];
        if ($row['workflow_name'] === '') { $row['workflow_name'] = 'Manual Workflow'; }
        if ($id > 0) {
            $this->db->where('id', $id)->update(db_prefix() . 'usi_ai_workflows', $row);
            return $id;
        }
        $row['created_by'] = get_staff_user_id();
        $row['created_at'] = $now;
        $this->db->insert(db_prefix() . 'usi_ai_workflows', $row);
        return (int) $this->db->insert_id();
    }

    public function save_step(array $data): int
    {
        $now = date('Y-m-d H:i:s');
        $row = [
            'workflow_id' => (int)($data['workflow_id'] ?? 0),
            'step_order' => (int)($data['step_order'] ?? 1),
            'step_name' => trim((string)($data['step_name'] ?? 'Workflow Step')),
            'step_type' => trim((string)($data['step_type'] ?? 'action')),
            'action_type' => trim((string)($data['action_type'] ?? 'create_alert')),
            'target_area' => trim((string)($data['target_area'] ?? 'sammy_ai')),
            'delay_minutes' => (int)($data['delay_minutes'] ?? 0),
            'requires_approval' => isset($data['requires_approval']) ? 1 : 0,
            'step_payload' => trim((string)($data['step_payload'] ?? '')),
            'created_by' => get_staff_user_id(),
            'created_at' => $now,
            'updated_at' => $now,
        ];
        $this->db->insert(db_prefix() . 'usi_ai_workflow_steps', $row);
        return (int) $this->db->insert_id();
    }

    public function run_workflow(int $workflowId, string $sourceArea = 'manual', int $sourceId = 0): array
    {
        $workflow = $this->get_workflow($workflowId);
        if (!$workflow) { return ['success' => false, 'message' => 'Workflow not found.']; }
        if ((int)$workflow['is_active'] !== 1) { return ['success' => false, 'message' => 'Workflow is disabled.']; }
        $now = date('Y-m-d H:i:s');
        $this->db->insert(db_prefix() . 'usi_ai_workflow_runs', [
            'workflow_id' => $workflowId,
            'source_area' => $sourceArea,
            'source_id' => $sourceId,
            'run_status' => 'running',
            'input_payload' => json_encode(['source_area' => $sourceArea, 'source_id' => $sourceId]),
            'started_by' => get_staff_user_id(),
            'started_at' => $now,
        ]);
        $runId = (int)$this->db->insert_id();
        $steps = $this->get_steps($workflowId);
        if (empty($steps)) {
            $this->write_log($runId, $workflowId, 0, 'warning', 'Workflow has no steps.');
            $this->db->where('id', $runId)->update(db_prefix() . 'usi_ai_workflow_runs', ['run_status' => 'completed', 'result_message' => 'Workflow completed with no steps.', 'completed_at' => date('Y-m-d H:i:s')]);
            return ['success' => true, 'message' => 'Workflow completed with no steps.'];
        }
        foreach ($steps as $step) {
            $this->write_log($runId, $workflowId, (int)$step['id'], 'step', 'Step ' . (int)$step['step_order'] . ': ' . $step['step_name'] . ' / ' . $step['action_type']);
            if ((int)$step['requires_approval'] === 1) {
                $this->write_log($runId, $workflowId, (int)$step['id'], 'approval', 'Approval required before automatic execution. Logged in review-first mode.');
            }
        }
        $this->db->where('id', $runId)->update(db_prefix() . 'usi_ai_workflow_runs', ['run_status' => 'completed', 'result_message' => 'Workflow test run completed in review-first mode.', 'completed_at' => date('Y-m-d H:i:s')]);
        return ['success' => true, 'message' => 'Workflow test run completed.'];
    }

    public function toggle_workflow(int $id, int $active): array
    {
        if (!$this->get_workflow($id)) { return ['success' => false, 'message' => 'Workflow not found.']; }
        $this->db->where('id', $id)->update(db_prefix() . 'usi_ai_workflows', ['is_active' => $active, 'updated_at' => date('Y-m-d H:i:s')]);
        return ['success' => true, 'message' => $active ? 'Workflow enabled.' : 'Workflow disabled.'];
    }

    public function duplicate_workflow(int $id): array
    {
        $workflow = $this->get_workflow($id);
        if (!$workflow) { return ['success' => false, 'message' => 'Workflow not found.']; }
        $steps = $this->get_steps($id);
        unset($workflow['id']);
        $workflow['workflow_name'] .= ' Copy';
        $workflow['created_by'] = get_staff_user_id();
        $workflow['created_at'] = date('Y-m-d H:i:s');
        $workflow['updated_at'] = date('Y-m-d H:i:s');
        $this->db->insert(db_prefix() . 'usi_ai_workflows', $workflow);
        $newId = (int)$this->db->insert_id();
        foreach ($steps as $step) {
            unset($step['id']);
            $step['workflow_id'] = $newId;
            $step['created_by'] = get_staff_user_id();
            $step['created_at'] = date('Y-m-d H:i:s');
            $step['updated_at'] = date('Y-m-d H:i:s');
            $this->db->insert(db_prefix() . 'usi_ai_workflow_steps', $step);
        }
        return ['success' => true, 'message' => 'Workflow duplicated.'];
    }

    public function delete_workflows(array $ids): int
    {
        $deleted = 0;
        foreach ($ids as $id) {
            $id = (int)$id;
            if ($id <= 0) { continue; }
            $this->db->where('workflow_id', $id)->delete(db_prefix() . 'usi_ai_workflow_logs');
            $this->db->where('workflow_id', $id)->delete(db_prefix() . 'usi_ai_workflow_runs');
            $this->db->where('workflow_id', $id)->delete(db_prefix() . 'usi_ai_workflow_steps');
            $this->db->where('id', $id)->delete(db_prefix() . 'usi_ai_workflows');
            $deleted++;
        }
        return $deleted;
    }

    private function write_log(int $runId, int $workflowId, int $stepId, string $type, string $message): void
    {
        $this->db->insert(db_prefix() . 'usi_ai_workflow_logs', [
            'workflow_run_id' => $runId,
            'workflow_id' => $workflowId,
            'step_id' => $stepId,
            'log_type' => $type,
            'log_message' => $message,
            'created_by' => get_staff_user_id(),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
