<?php
defined('BASEPATH') or exit('No direct script access allowed');

function scaf_permit_controls_allowed($projectId, $write = false)
{
    return scaf_project_allowed($projectId)
        && (is_admin() || staff_can('view_project_controls', 'smart_choice_enterprise_core'))
        && (!$write || (staff_can('edit', 'projects')
            && (is_admin() || staff_can('manage', 'smart_choice_enterprise_core'))));
}

function scaf_permit_stages()
{
    return [
        'planned' => 'Notice of Commencement',
        'plan_review' => 'Plan Review',
        'issued' => 'Permit Issued',
        'rough' => 'Rough Inspections',
        'final_passed' => 'Final Inspection Passed',
    ];
}

function scaf_permit_custom_fields($projectId)
{
    $CI = &get_instance();
    return $CI->db->select('f.id, f.name, v.value')
        ->from(db_prefix().'customfields f')
        ->join(db_prefix().'customfieldsvalues v', 'v.fieldid=f.id AND v.fieldto=f.fieldto AND v.relid='.(int) $projectId, 'left')
        ->where('f.fieldto', 'projects')->where('f.active', 1)
        ->get()->result_array();
}

function scaf_permit_contact_link($value)
{
    $value = trim((string) $value);
    if (!preg_match('/^\+?[0-9(). -]+$/D', $value)) {
        return '';
    }
    $digits = preg_replace('/\D/', '', $value);
    return strlen($digits) >= 7 && strlen($digits) <= 15
        ? (str_starts_with($value, '+') ? '+' : '').$digits : '';
}

function scaf_permit_records($projectId)
{
    $CI = &get_instance();
    $records = [];
    foreach (['permits', 'change_orders'] as $key) {
        $table = db_prefix().'sce_'.$key;
        $records[$key] = $CI->db->table_exists($table)
            ? $CI->db->where('project_id', $projectId)->order_by('id', 'DESC')->get($table)->result_array()
            : null;
    }
    return $records;
}

function scaf_project_finish_requires_inspection($projectId, array $submittedFields = [])
{
    foreach (scaf_permit_custom_fields($projectId) as $field) {
        if (!preg_match('/^permit\s+(needed|required)\??$/i', trim($field['name']))) {
            continue;
        }
        // A saved requirement remains effective during the same request that tries to close the project.
        $submitted = $submittedFields[$field['id']] ?? '';
        $values = array_merge((array) $field['value'], (array) $submitted);
        foreach ($values as $value) {
            if (!in_array(strtolower(trim((string) $value)), ['yes', '1', 'required'], true)) {
                continue;
            }
            $permits = scaf_permit_records($projectId)['permits'];
            if (!$permits) {
                return true;
            }
            foreach ($permits as $permit) {
                if ($permit['inspection_status'] !== 'final_passed') {
                    return true;
                }
            }
        }
    }
    return false;
}
