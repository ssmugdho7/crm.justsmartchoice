<?php

defined('BASEPATH') or exit('No direct script access allowed');

function engproj_safe_slug($text)
{
    $text = trim((string)$text);
    $text = preg_replace('/[^A-Za-z0-9\-_\s]/', '', $text);
    $text = preg_replace('/\s+/', ' ', $text);
    $text = $text !== '' ? $text : 'Unassigned';
    return substr($text, 0, 80);
}

function engproj_customer_name($customer_id)
{
    $CI = &get_instance();
    if (!$customer_id || !$CI->db->table_exists(db_prefix() . 'clients')) {
        return 'Unassigned Customer';
    }
    $row = $CI->db->select('company')->where('userid', (int)$customer_id)->get(db_prefix() . 'clients')->row();
    return $row && $row->company ? $row->company : 'Customer ' . (int)$customer_id;
}

function engproj_project_name($project_id)
{
    $CI = &get_instance();
    if (!$project_id || !$CI->db->table_exists(db_prefix() . 'projects')) {
        return 'Engineering Files';
    }
    $row = $CI->db->select('name')->where('id', (int)$project_id)->get(db_prefix() . 'projects')->row();
    return $row && $row->name ? $row->name : 'Project ' . (int)$project_id;
}

function engproj_project_folder($customer_id = 0, $project_id = 0, $section = 'Engineering')
{
    $customer = engproj_safe_slug(engproj_customer_name($customer_id));
    $project  = engproj_safe_slug(engproj_project_name($project_id));
    $section  = engproj_safe_slug($section);
    $path = FCPATH . 'uploads/Projects/' . $customer . '/' . $project . '/' . $section . '/';
    _maybe_create_upload_path($path);
    return $path;
}

function engproj_public_file_url($file, $folder_path = '')
{
    $file = basename((string)$file);
    if ($file === '') { return ''; }
    if ($folder_path) {
        $normalized = str_replace('\\', '/', $folder_path);
        $base = str_replace('\\', '/', FCPATH);
        if (strpos($normalized, $base) === 0) {
            $rel = substr($normalized, strlen($base));
            return base_url($rel . $file);
        }
    }
    return base_url('uploads/Projects/' . $file);
}

function handle_project_files_attachments_upload($id = 0, $customer_upload = false)
{
    $CI = &get_instance();
    $project_id = (int)$CI->input->post('project_id');
    $customer_id = (int)$CI->input->post('customer_id');
    $path = engproj_project_folder($customer_id, $project_id, 'Shared Files');
    $totalUploaded = 0;

    if (!isset($_FILES['file']['name']) || ($_FILES['file']['name'] === '' && !is_array($_FILES['file']['name']))) {
        return false;
    }
    if (!is_array($_FILES['file']['name'])) {
        foreach (['name','type','tmp_name','error','size'] as $key) { $_FILES['file'][$key] = [$_FILES['file'][$key]]; }
    }
    _file_attachments_index_fix('file');
    for ($i = 0; $i < count($_FILES['file']['name']); $i++) {
        $tmpFilePath = $_FILES['file']['tmp_name'][$i] ?? '';
        if ($tmpFilePath === '' || _perfex_upload_error($_FILES['file']['error'][$i]) || !_upload_extension_allowed($_FILES['file']['name'][$i])) { continue; }
        $filename = unique_filename($path, $_FILES['file']['name'][$i]);
        $newFilePath = $path . $filename;
        if (move_uploaded_file($tmpFilePath, $newFilePath)) {
            $attachment = [[ 'file_name' => $filename, 'filetype' => $_FILES['file']['type'][$i] ]];
            if (is_image($newFilePath)) { create_img_thumb($newFilePath, $filename); }
            if ($customer_upload == true) {
                $attachment[0]['staffid'] = 0;
                $attachment[0]['contact_id'] = get_contact_user_id();
                $attachment[0]['visible_to_customer'] = 1;
            }
            // Relate to Perfex CRM project when available; fallback to customer to avoid duplicate file areas.
            $rel_type = $project_id > 0 ? 'project' : 'customer';
            $rel_id = $project_id > 0 ? $project_id : ($customer_id ?: $id);
            $CI->misc_model->add_attachment_to_database($rel_id, $rel_type, $attachment);
            $totalUploaded++;
        }
    }
    return (bool)$totalUploaded;
}

function get_upload_path_by_type_module($type)
{
    $CI = &get_instance();
    $project_id = (int)$CI->input->post('project_id');
    $customer_id = (int)$CI->input->post('customer_id');
    if ($type === 'eng_drawing_files') { return engproj_project_folder($customer_id, $project_id, 'Drawings'); }
    if ($type === 'eng_document_files') { return engproj_project_folder($customer_id, $project_id, 'Documents'); }
    return engproj_project_folder($customer_id, $project_id, 'Shared Files');
}

function get_drawing_file($file, $type = 0, $style = false, $folder_path = '')
{
    if (!$file) { return '<span class="text-muted">—</span>'; }
    $url = engproj_public_file_url($file, $folder_path);
    $css = $style ? 'color:#004b6d' : '';
    if ((int)$type === 3) { return '<a href="'.$url.'" target="_blank"><img src="'.$url.'" height="50" width="50" style="object-fit:cover;border-radius:6px;"></a>'; }
    return '<a href="'.$url.'" target="_blank" style="'.$css.'"><i class="fa fa-file fa-2x"></i></a>';
}

function document_file($file, $folder_path = ''){ return get_drawing_file($file, 0, false, $folder_path); }
function drawing_file($file, $folder_path = ''){ return get_drawing_file($file, 0, false, $folder_path); }

function get_engeniering_status(){ return [ ['id'=>'1','name'=>_l('engeniering_status_passed')], ['id'=>'2','name'=>_l('engeniering_status_faild')], ['id'=>'3','name'=>_l('engeniering_status_no_sch')], ['id'=>'4','name'=>_l('engeniering_status_date')], ['id'=>'5','name'=>_l('engeniering_status_sch')] ]; }
function get_engeniering_tpo_status(){ return [ ['id'=>'1','name'=>_l('engeniering_status_tpo_approved')], ['id'=>'2','name'=>_l('engeniering_status_tpo_applied')], ['id'=>'3','name'=>_l('engeniering_status_tpo_no_process')], ['id'=>'4','name'=>_l('engeniering_status_tpo_need_more')], ['id'=>'5','name'=>_l('engeniering_status_tpo_need_apply')] ]; }
function drawing_type($return = false, $key = null){ $status = [ ['id'=>'1','name'=>_l('drawing_type_pdf')], ['id'=>'2','name'=>_l('drawing_type_document')], ['id'=>'3','name'=>_l('drawing_type_image')], ['id'=>'4','name'=>_l('drawing_type_cad')], ['id'=>'5','name'=>_l('drawing_type_layout')], ['id'=>'6','name'=>_l('drawing_type_skp')] ]; if($return){ foreach($status as $s){ if((string)$s['id']===(string)$key){ return $s['name']; } } return ''; } return $status; }
function show_eng_tpo_status($key){ $s=['1'=>_l('engeniering_status_tpo_approved'),'2'=>_l('engeniering_status_tpo_applied'),'3'=>_l('engeniering_status_tpo_no_process'),'4'=>_l('engeniering_status_tpo_need_more'),'5'=>_l('engeniering_status_tpo_need_apply')]; return $s[(string)$key] ?? '<span class="text-muted">—</span>'; }
function show_eng_status($key){ $s=['1'=>_l('engeniering_status_passed'),'2'=>_l('engeniering_status_faild'),'3'=>_l('engeniering_status_no_sch'),'4'=>_l('engeniering_status_date'),'5'=>_l('engeniering_status_sch')]; return $s[(string)$key] ?? '<span class="text-muted">—</span>'; }
function show_contractor_name($id){ if(!$id) return '<span class="text-muted">—</span>'; $CI=&get_instance(); $CI->load->model('engineering_projects/contractors_model'); $c=$CI->contractors_model->get($id); return $c && isset($c->contractor) ? html_escape($c->contractor) : '<span class="text-muted">—</span>'; }
function get_drawing($id){ if(!$id) return null; $CI=&get_instance(); $CI->load->model('engineering_projects/drawings_model'); return $CI->drawings_model->get($id); }
function get_document($id){ if(!$id) return null; $CI=&get_instance(); $CI->load->model('engineering_projects/documents_model'); return $CI->documents_model->get($id); }
