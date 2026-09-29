<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Social_lead_funnel_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get_opportunities($filters = [])
    {
        if (!empty($filters['status'])) {
            $this->db->where('status', $filters['status']);
        }
        if (!empty($filters['source'])) {
            $this->db->where('source', $filters['source']);
        }
        if (!empty($filters['search'])) {
            $this->db->group_start();
            $this->db->like('title', $filters['search']);
            $this->db->or_like('message', $filters['search']);
            $this->db->or_like('author_name', $filters['search']);
            $this->db->or_like('service_type', $filters['search']);
            $this->db->group_end();
        }
        $this->db->order_by('id', 'DESC');
        return $this->db->get(db_prefix() . 'slf_opportunities')->result_array();
    }

    public function get_opportunity($id)
    {
        return $this->db->where('id', (int)$id)->get(db_prefix() . 'slf_opportunities')->row_array();
    }

    public function create_opportunity($data)
    {
        $insert = [
            'source' => $data['source'] ?? 'manual',
            'external_id' => $data['external_id'] ?? null,
            'author_name' => $data['author_name'] ?? '',
            'author_profile_url' => $data['author_profile_url'] ?? '',
            'contact_email' => $data['contact_email'] ?? '',
            'contact_phone' => $data['contact_phone'] ?? '',
            'title' => $data['title'] ?? '',
            'message' => $data['message'] ?? '',
            'keyword_matched' => $data['keyword_matched'] ?? $this->detect_keyword($data['message'] ?? ''),
            'service_type' => $data['service_type'] ?? $this->detect_service($data['message'] ?? ''),
            'city' => $data['city'] ?? '',
            'state' => $data['state'] ?? '',
            'post_url' => $data['post_url'] ?? '',
            'ai_score' => $data['ai_score'] ?? $this->score_message($data['message'] ?? ''),
            'ai_reply_draft' => $data['ai_reply_draft'] ?? $this->draft_reply($data['author_name'] ?? '', $data['message'] ?? ''),
            'status' => $data['status'] ?? get_option('social_lead_funnel_default_status'),
            'assigned_staff_id' => !empty($data['assigned_staff_id']) ? (int)$data['assigned_staff_id'] : null,
            'department_id' => !empty($data['department_id']) ? (int)$data['department_id'] : null,
            'created_by' => get_staff_user_id(),
            'datecreated' => date('Y-m-d H:i:s'),
        ];
        $this->db->insert(db_prefix() . 'slf_opportunities', $insert);
        $id = $this->db->insert_id();
        $this->log('info', 'opportunity_created', 'Social opportunity created', ['id' => $id]);
        return $id;
    }

    public function update_opportunity($id, $data)
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->where('id', (int)$id)->update(db_prefix() . 'slf_opportunities', $data);
        $this->log('info', 'opportunity_updated', 'Social opportunity updated', ['id' => (int)$id]);
        return $this->db->affected_rows() >= 0;
    }

    public function delete_opportunity($id)
    {
        $this->db->where('id', (int)$id)->delete(db_prefix() . 'slf_opportunities');
        $this->log('warning', 'opportunity_deleted', 'Social opportunity deleted', ['id' => (int)$id]);
        return $this->db->affected_rows() > 0;
    }

    public function create_crm_lead($id)
    {
        $row = $this->get_opportunity($id);
        if (!$row) {
            return false;
        }
        if (!empty($row['lead_id'])) {
            return (int)$row['lead_id'];
        }

        $lead = [
            'name' => $row['author_name'] ?: ($row['title'] ?: 'Social Lead'),
            'title' => $row['title'] ?: 'Social Lead Opportunity',
            'email' => $row['contact_email'],
            'phonenumber' => $row['contact_phone'],
            'company' => $row['author_name'],
            'description' => "Source: " . $row['source'] . "\nService: " . $row['service_type'] . "\nKeyword: " . $row['keyword_matched'] . "\nPost URL: " . $row['post_url'] . "\n\nMessage:\n" . $row['message'] . "\n\nAI Suggested Reply:\n" . $row['ai_reply_draft'],
            'status' => 1,
            'source' => 1,
            'assigned' => !empty($row['assigned_staff_id']) ? (int)$row['assigned_staff_id'] : 0,
            'dateadded' => date('Y-m-d H:i:s'),
            'addedfrom' => get_staff_user_id(),
        ];

        $this->db->insert(db_prefix() . 'leads', $lead);
        $lead_id = $this->db->insert_id();
        $this->update_opportunity($id, ['lead_id' => $lead_id, 'status' => 'converted']);
        $this->maybe_create_task($row, $lead_id);
        $this->log('info', 'lead_created', 'CRM lead created from social opportunity', ['opportunity_id' => $id, 'lead_id' => $lead_id]);
        return $lead_id;
    }

    public function maybe_create_task($row, $lead_id)
    {
        if (get_option('social_lead_funnel_auto_create_task') !== '1') {
            return false;
        }
        if (!$this->db->table_exists(db_prefix() . 'tasks')) {
            return false;
        }
        $task = [
            'name' => 'Follow up social lead: ' . ($row['author_name'] ?: $row['title']),
            'description' => 'Review and reply to this social media opportunity. Lead ID: ' . $lead_id,
            'priority' => 2,
            'dateadded' => date('Y-m-d H:i:s'),
            'startdate' => date('Y-m-d'),
            'duedate' => date('Y-m-d', strtotime('+1 day')),
            'addedfrom' => get_staff_user_id(),
            'is_added_from_contact' => 0,
            'status' => 1,
        ];
        if (isset($task['rel_type'])) {
            $task['rel_type'] = 'lead';
        }
        $this->db->insert(db_prefix() . 'tasks', $task);
        return $this->db->insert_id();
    }

    public function get_templates()
    {
        return $this->db->order_by('id', 'DESC')->get(db_prefix() . 'slf_templates')->result_array();
    }

    public function save_template($data)
    {
        $payload = [
            'name' => $data['name'] ?? 'Template',
            'source' => $data['source'] ?? 'all',
            'language' => $data['language'] ?? 'english',
            'body' => $data['body'] ?? '',
            'active' => !empty($data['active']) ? 1 : 0,
        ];
        if (!empty($data['id'])) {
            $this->db->where('id', (int)$data['id'])->update(db_prefix() . 'slf_templates', $payload);
            return (int)$data['id'];
        }
        $payload['datecreated'] = date('Y-m-d H:i:s');
        $this->db->insert(db_prefix() . 'slf_templates', $payload);
        return $this->db->insert_id();
    }

    public function health()
    {
        $tables = ['slf_opportunities', 'slf_logs', 'slf_templates'];
        $result = [];
        foreach ($tables as $table) {
            $name = db_prefix() . $table;
            $result[$name] = [
                'exists' => $this->db->table_exists($name),
                'rows' => $this->db->table_exists($name) ? $this->db->count_all($name) : 0,
            ];
        }
        $result['facebook_enabled'] = get_option('social_lead_funnel_enable_facebook') === '1';
        $result['nextdoor_enabled'] = get_option('social_lead_funnel_enable_nextdoor') === '1';
        $result['ai_drafts_enabled'] = get_option('social_lead_funnel_enable_ai_drafts') === '1';
        return $result;
    }

    public function detect_keyword($message)
    {
        $keywords = array_map('trim', explode(',', (string)get_option('social_lead_funnel_keywords')));
        foreach ($keywords as $keyword) {
            if ($keyword !== '' && stripos((string)$message, $keyword) !== false) {
                return $keyword;
            }
        }
        return '';
    }

    public function detect_service($message)
    {
        $text = strtolower((string)$message);
        $map = [
            'plumb' => 'Plumbing',
            'electric' => 'Electrical',
            'bath' => 'Bathroom Remodeling',
            'kitchen' => 'Kitchen Remodeling',
            'paint' => 'Painting',
            'stucco' => 'Stucco',
            'floor' => 'Flooring',
            'drywall' => 'Drywall',
            'roof' => 'Roofing',
        ];
        foreach ($map as $needle => $service) {
            if (strpos($text, $needle) !== false) {
                return $service;
            }
        }
        return 'General Construction';
    }

    public function score_message($message)
    {
        $score = 20;
        $text = strtolower((string)$message);
        foreach (['estimate', 'quote', 'price', 'cost', 'need', 'looking for', 'asap', 'today', 'tomorrow'] as $word) {
            if (strpos($text, $word) !== false) {
                $score += 10;
            }
        }
        return min(100, $score);
    }

    public function draft_reply($name, $message)
    {
        if (get_option('social_lead_funnel_enable_ai_drafts') !== '1') {
            return '';
        }
        $first = trim((string)$name) ?: 'there';
        $service = $this->detect_service($message);
        return "Hi {$first}, we can help with {$service}. Smart Choice Contractors USA can review the details and guide you through the right next step. Please call 86 or send the project address, photos, and what you need done.";
    }

    public function log($level, $event, $message, $data = [])
    {
        $this->db->insert(db_prefix() . 'slf_logs', [
            'level' => $level,
            'event_type' => $event,
            'message' => $message,
            'data' => json_encode($data),
            'staff_id' => function_exists('get_staff_user_id') ? get_staff_user_id() : null,
            'datecreated' => date('Y-m-d H:i:s'),
        ]);
    }
}
