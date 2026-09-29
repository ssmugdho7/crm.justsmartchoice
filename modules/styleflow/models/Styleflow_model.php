<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Styleflow_model extends App_Model
{
    public function save_template($data, $id = 0)
    {
        $fonts = array_keys(styleflow_supported_fonts());
        $photoModes = array_column(styleflow_staff_photo_mode_options(), 'id');
        $name = trim((string)($data['name'] ?? ''));
        if ($name === '') return false;

        $slug = trim((string)($data['slug'] ?? ''));
        if ($slug === '') $slug = 'custom_' . time();
        $slug = preg_replace('/[^a-z0-9_\-]/','_',strtolower($slug));

        $photoMode = in_array($data['staff_photo_mode'] ?? '', $photoModes, true)
            ? $data['staff_photo_mode'] : 'none';
        $selectedStaffId = $photoMode === 'selected' ? (int)($data['selected_staff_id'] ?? 0) : 0;

        if ($photoMode === 'selected' && $selectedStaffId > 0) {
            $exists = $this->db->where('staffid', $selectedStaffId)->count_all_results(db_prefix() . 'staff') > 0;
            if (!$exists) $selectedStaffId = 0;
        }

        $payload = [
            'name'=>$name,
            'slug'=>$slug,
            'primary_color'=>styleflow_sanitize_color($data['primary_color'] ?? '', '#3598DB'),
            'secondary_color'=>styleflow_sanitize_color($data['secondary_color'] ?? '', '#F28C28'),
            'accent_color'=>styleflow_sanitize_color($data['accent_color'] ?? '', '#169179'),
            'text_color'=>styleflow_sanitize_color($data['text_color'] ?? '', '#333333'),
            'font_family'=>in_array($data['font_family'] ?? '', $fonts, true) ? $data['font_family'] : 'helvetica',
            'table_style'=>in_array($data['table_style'] ?? '', ['classic','rounded','stripe','boxed','minimal','line','double','soft'], true) ? $data['table_style'] : 'rounded',
            'header_style'=>in_array($data['header_style'] ?? '', ['clean','band','line','split'], true) ? $data['header_style'] : 'band',
            'staff_photo_mode'=>$photoMode,
            'selected_staff_id'=>$selectedStaffId,
            'available_invoice'=>!empty($data['available_invoice']) ? 1 : 0,
            'available_estimate'=>!empty($data['available_estimate']) ? 1 : 0,
            'available_proposal'=>!empty($data['available_proposal']) ? 1 : 0,
            'updated_at'=>date('Y-m-d H:i:s'),
        ];

        if ($id > 0) {
            $this->db->where('id',$id)->update(styleflow_templates_table(),$payload);
            return $this->db->affected_rows() >= 0;
        }

        $payload['is_system']=0;
        $payload['created_at']=date('Y-m-d H:i:s');
        return $this->db->insert(styleflow_templates_table(),$payload);
    }
}
