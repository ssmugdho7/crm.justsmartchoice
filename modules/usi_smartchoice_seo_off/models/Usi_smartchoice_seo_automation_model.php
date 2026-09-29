<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Usi_smartchoice_seo_automation_model extends App_Model
{
    public function get_alerts($filters = [])
    {
        $table = db_prefix() . 'usi_ai_alerts';
        if (!$this->db->table_exists($table)) {
            return [];
        }
        if (!empty($filters['status'])) {
            $this->db->where('status', $filters['status']);
        }
        if (!empty($filters['priority'])) {
            $this->db->where('priority', $filters['priority']);
        }
        if (!empty($filters['source_area'])) {
            $this->db->where('source_area', $filters['source_area']);
        }
        $this->db->order_by('id', 'DESC');
        return $this->db->get($table)->result_array();
    }

    public function get_queue($filters = [])
    {
        $table = db_prefix() . 'usi_ai_automation_queue';
        if (!$this->db->table_exists($table)) {
            return [];
        }
        if (!empty($filters['status'])) {
            $this->db->where('status', $filters['status']);
        }
        $this->db->order_by('id', 'DESC');
        return $this->db->get($table)->result_array();
    }

    public function create_alert($data)
    {
        $table = db_prefix() . 'usi_ai_alerts';
        if (!$this->db->table_exists($table)) {
            return false;
        }
        $insert = [
            'alert_title' => isset($data['alert_title']) ? trim($data['alert_title']) : 'Sammy AI Alert',
            'alert_type' => isset($data['alert_type']) ? trim($data['alert_type']) : 'general',
            'source_area' => isset($data['source_area']) ? trim($data['source_area']) : 'sammy_ai',
            'source_id' => !empty($data['source_id']) ? (int) $data['source_id'] : null,
            'priority' => isset($data['priority']) ? trim($data['priority']) : 'medium',
            'status' => 'open',
            'assigned_staff_id' => !empty($data['assigned_staff_id']) ? (int) $data['assigned_staff_id'] : null,
            'due_date' => !empty($data['due_date']) ? $data['due_date'] : null,
            'message' => isset($data['message']) ? trim($data['message']) : '',
            'recommended_action' => isset($data['recommended_action']) ? trim($data['recommended_action']) : '',
            'created_by' => get_staff_user_id(),
            'created_at' => date('Y-m-d H:i:s'),
        ];
        $this->db->insert($table, $insert);
        return $this->db->insert_id();
    }

    public function complete_alert($id)
    {
        $table = db_prefix() . 'usi_ai_alerts';
        if (!$this->db->table_exists($table)) {
            return false;
        }
        $this->db->where('id', (int) $id);
        return $this->db->update($table, [
            'status' => 'completed',
            'completed_by' => get_staff_user_id(),
            'completed_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function delete_alerts($ids)
    {
        $table = db_prefix() . 'usi_ai_alerts';
        if (!$this->db->table_exists($table) || empty($ids)) {
            return false;
        }
        $clean = array_map('intval', (array) $ids);
        $this->db->where_in('id', $clean);
        return $this->db->delete($table);
    }

    public function build_default_alerts()
    {
        $count = 0;
        $now = date('Y-m-d H:i:s');
        $sources = [
            ['title' => 'Review open AI estimates', 'type' => 'estimate_follow_up', 'area' => 'ai_estimates', 'priority' => 'high', 'message' => 'Review AI estimates that still need calculation, approval, or customer package preparation.', 'action' => 'Open AI Estimates and process pending records.'],
            ['title' => 'Review purchasing status', 'type' => 'purchase_follow_up', 'area' => 'ai_purchasing', 'priority' => 'medium', 'message' => 'Check purchase orders that may need ordering, delivery confirmation, or receiving notes.', 'action' => 'Open AI Purchasing and update PO status.'],
            ['title' => 'Review schedule readiness', 'type' => 'schedule_follow_up', 'area' => 'ai_scheduling', 'priority' => 'medium', 'message' => 'Check upcoming schedule records for staffing, inspection, and job readiness.', 'action' => 'Open AI Scheduling and verify assignments.'],
        ];
        foreach ($sources as $source) {
            $exists = $this->db->where('alert_type', $source['type'])->where('status', 'open')->count_all_results(db_prefix() . 'usi_ai_alerts');
            if ((int) $exists === 0) {
                $this->db->insert(db_prefix() . 'usi_ai_alerts', [
                    'alert_title' => $source['title'],
                    'alert_type' => $source['type'],
                    'source_area' => $source['area'],
                    'priority' => $source['priority'],
                    'status' => 'open',
                    'message' => $source['message'],
                    'recommended_action' => $source['action'],
                    'created_by' => get_staff_user_id(),
                    'created_at' => $now,
                ]);
                $count++;
            }
        }
        return $count;
    }
}
