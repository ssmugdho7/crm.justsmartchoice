<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Sc_ai_repair_model extends App_Model
{
    public function start_run($type, $request)
    {
        $this->db->insert(db_prefix() . 'sc_ai_repair_runs', [
            'staff_id'    => get_staff_user_id(),
            'type'        => $type,
            'status'      => 'running',
            'request_text'=> $request,
            'created_at'  => date('Y-m-d H:i:s'),
        ]);
        return (int) $this->db->insert_id();
    }

    public function finish_run($id, $status, $summary)
    {
        $this->db->where('id', $id)->update(db_prefix() . 'sc_ai_repair_runs', [
            'status'       => $status,
            'summary'      => $summary,
            'completed_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function recent_runs()
    {
        return $this->db->order_by('id', 'DESC')->limit(50)->get(db_prefix() . 'sc_ai_repair_runs')->result_array();
    }

    public function latest_applied_run()
    {
        return $this->db->where('status', 'applied')->order_by('id', 'DESC')->limit(1)->get(db_prefix() . 'sc_ai_repair_runs')->row_array();
    }

    public function get_report($runId)
    {
        $run = $this->db->where('id', $runId)->get(db_prefix() . 'sc_ai_repair_runs')->row_array();
        if (!$run) {
            return null;
        }

        $run['findings'] = $this->db->where('run_id', $runId)->order_by('id', 'ASC')->get(db_prefix() . 'sc_ai_repair_findings')->result_array();
        $run['changes']  = $this->db->where('run_id', $runId)->order_by('id', 'ASC')->get(db_prefix() . 'sc_ai_repair_changes')->result_array();
        return $run;
    }

    public function delete_run($runId)
    {
        $run = $this->db->where('id', $runId)->get(db_prefix() . 'sc_ai_repair_runs')->row_array();
        if (!$run) {
            throw new RuntimeException(_l('sc_ai_repair_report_not_found'));
        }

        $this->db->trans_start();
        $this->db->where('run_id', $runId)->delete(db_prefix() . 'sc_ai_repair_findings');
        $this->db->where('run_id', $runId)->delete(db_prefix() . 'sc_ai_repair_changes');
        $this->db->where('id', $runId)->delete(db_prefix() . 'sc_ai_repair_runs');
        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            throw new RuntimeException(_l('sc_ai_repair_delete_failed'));
        }
    }

    public function add_finding($run, $severity, $category, $path, $message, $solution = '')
    {
        $this->db->insert(db_prefix() . 'sc_ai_repair_findings', [
            'run_id'   => $run,
            'severity' => $severity,
            'category' => $category,
            'path'     => $path,
            'message'  => $message,
            'solution' => $solution,
        ]);
    }
}
