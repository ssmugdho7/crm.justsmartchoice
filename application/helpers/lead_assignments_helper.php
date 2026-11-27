<?php

defined('BASEPATH') or exit('No direct script access allowed');

function sc_lead_assignee_ids($lead): array
{
    $lead = (array) $lead;
    $ids = array_merge([(int) ($lead['assigned'] ?? 0)], explode(',', $lead['additional_assigned_staff'] ?? ''));
    return array_values(array_unique(array_filter(array_map('intval', $ids), fn ($id) => $id > 0)));
}

function sc_lead_assigned_sql($staffId): string
{
    $staffId = (int) $staffId;
    $table = db_prefix() . 'leads';
    return '(' . $table . '.assigned=' . $staffId . ' OR FIND_IN_SET(' . $staffId . ", COALESCE(" . $table . ".additional_assigned_staff, ''))>0)";
}

function sc_lead_prepare_assignees(array $data, $previous = null): array
{
    unset($data['additional_assigned_staff']);
    $previous = (array) $previous;
    if ($previous && (int) $previous['assigned'] === (int) get_staff_user_id()
        && (int) $previous['addedfrom'] !== (int) get_staff_user_id()
        && !is_admin() && staff_cant('view', 'leads')) {
        unset($data['assigned'], $data['assigned_staff'], $data['assigned_staff_present']);
        return $data;
    }
    if (!isset($data['assigned_staff_present'])) {
        // Legacy API and bulk reassignment replace the complete assignment.
        if (array_key_exists('assigned', $data)) $data['additional_assigned_staff'] = '';
        return $data;
    }
    $CI = &get_instance();
    $ids = array_values(array_unique(array_filter(array_map('intval', (array) ($data['assigned_staff'] ?? [])), fn ($id) => $id > 0)));
    $valid = [];
    if ($ids) {
        $valid = array_map('intval', array_column($CI->db->select('staffid')->where_in('staffid', $ids)->where('active', 1)->where('is_not_staff', 0)->get(db_prefix() . 'staff')->result_array(), 'staffid'));
    }
    $previousIds = sc_lead_assignee_ids($previous);
    $ids = array_values(array_intersect($ids, array_unique(array_merge($valid, $previousIds))));
    $primary = (int) (((array) $previous)['assigned'] ?? 0);
    if (!in_array($primary, $ids, true)) $primary = $ids[0] ?? 0;
    $data['assigned'] = $primary;
    $data['additional_assigned_staff'] = implode(',', array_diff($ids, [$primary]));
    unset($data['assigned_staff'], $data['assigned_staff_present']);
    return $data;
}
