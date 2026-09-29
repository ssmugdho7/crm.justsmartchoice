<?php

defined('BASEPATH') or exit('No direct script access allowed');
class Stripe_hub_model extends App_Model
{
    public function log_action($data)
    {
        $data['staff_id'] = isset($data['staff_id']) ? $data['staff_id'] : get_staff_user_id();
        $data['datecreated'] = date('Y-m-d H:i:s');
        if (isset($data['metadata']) && !is_string($data['metadata'])) $data['metadata'] = json_encode($data['metadata']);
        $this->db->insert(db_prefix() . 'stripe_hub_actions', $data);
        return $this->db->insert_id();
    }

    public function get_actions($limit = 100)
    {
        $this->db->order_by('id', 'DESC')->limit((int) $limit);
        if (!is_admin() && !staff_can('view', 'stripe_hub')) $this->db->where('staff_id', get_staff_user_id());
        return $this->db->get(db_prefix() . 'stripe_hub_actions')->result_array();
    }

    public function save_event($data)
    {
        if (!empty($data['stripe_event_id'])) {
            $existing = $this->db->where('stripe_event_id', $data['stripe_event_id'])->get(db_prefix().'stripe_hub_events')->row();
            if ($existing) return $existing->id;
        }
        $data['datecreated'] = date('Y-m-d H:i:s');
        $this->db->insert(db_prefix() . 'stripe_hub_events', $data);
        return $this->db->insert_id();
    }

    public function get_events($limit = 100)
    {
        return $this->db->order_by('id', 'DESC')->limit((int)$limit)->get(db_prefix().'stripe_hub_events')->result_array();
    }
}
