<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @property-read Authentication_model $authentication_model
 * @property-read Staff_model $staff_model
 */
class Staff extends AdminController
{
    /* List all staff members */
    public function index()
    {
        if (staff_cant('view', 'staff')) {
            access_denied('staff');
        }
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data('staff');
        }
        $data['staff_members'] = $this->staff_model->get('', ['active' => 1]);
        $data['title']         = _l('staff_members');
        $this->load->view('admin/staff/manage', $data);
    }

    /* Add new staff member or edit existing */
    public function member($id = '')
    {
        if (staff_cant('view', 'staff')) {
            access_denied('staff');
        }
        hooks()->do_action('staff_member_edit_view_profile', $id);

        $this->load->model('departments_model');
        if ($this->input->post()) {
            if (staff_cant($id == '' ? 'create' : 'edit', 'staff')) {
                access_denied('staff');
            }
            $data = $this->input->post();
            $allowedProfileFields = [
                'firstname','lastname','email','phonenumber','default_language','direction','facebook','linkedin','skype','telegram','instagram','tiktok',
                'server_login','server_ip','server_password','email_signature','hourly_rate','bank_account_number','bank_account_type',
                'bank_routing_number','bank_swift_aba','bank_name','bank_website','bank_account_name','bank_account_address',
                'employee_staff_id','start_date','employment_type','supervisor_staff_id','team','probation_end_date','contract_expiration_date',
                'emergency_contact_name','emergency_contact_relationship','emergency_contact_phone','marital_status','religion','children_count','children_json',
                'administrator','is_not_staff','role','departments','permissions','custom_fields','send_welcome_email'
            ];
            $data = array_intersect_key($data, array_flip($allowedProfileFields));
            if (isset($data['email'])) {
                $data['email'] = trim($data['email']);
                $duplicateEmail = $this->db
                    ->where('email', $data['email'])
                    ->where('staffid !=', (int) $id)
                    ->count_all_results(db_prefix() . 'staff');
                if ($duplicateEmail > 0) {
                    set_alert('danger', _l('staff_email_already_exists'));
                    redirect(admin_url('staff/member/' . $id));
                }
            }
            foreach (['start_date', 'probation_end_date', 'contract_expiration_date'] as $dateField) {
                if (array_key_exists($dateField, $data) && ($data[$dateField] === '' || $data[$dateField] === '0000-00-00')) {
                    $data[$dateField] = null;
                }
            }
            // Don't do XSS clean here.
            $data['email_signature'] = (string) $this->input->post('email_signature', false);
            $data['email_signature'] = html_entity_decode($data['email_signature']);

            if ($data['email_signature'] == strip_tags($data['email_signature'])) {
                // not contains HTML, add break lines
                $data['email_signature'] = nl2br_save_html($data['email_signature']);
            }

            $data['password'] = $this->input->post('password', false);

            if ($id == '') {
                if (staff_cant('create', 'staff')) {
                    access_denied('staff');
                }
                $id = $this->staff_model->add($data);
                if ($id) {
                    handle_staff_profile_image_upload($id);
                    $this->handle_sc_staff_documents($id);
                    set_alert('success', _l('added_successfully', _l('staff_member')));
                    redirect(admin_url('staff/member/' . $id));
                }
            } else {
                if (staff_cant('edit', 'staff')) {
                    access_denied('staff');
                }
                handle_staff_profile_image_upload($id);
                $this->handle_sc_staff_documents($id);
                $response = $this->staff_model->update($data, $id);
                if (is_array($response)) {
                    if (isset($response['cant_remove_main_admin'])) {
                        set_alert('warning', _l('staff_cant_remove_main_admin'));
                    } elseif (isset($response['cant_remove_yourself_from_admin'])) {
                        set_alert('warning', _l('staff_cant_remove_yourself_from_admin'));
                    }
                } elseif ($response == true) {
                    set_alert('success', _l('updated_successfully', _l('staff_member')));
                }
                redirect(admin_url('staff/member/' . $id));
            }
        }
        if ($id == '') {
            $title = _l('add_new', _l('staff_member'));
        } else {
            $member = $this->staff_model->get($id);
            if (!$member) {
                blank_page('Staff Member Not Found', 'danger');
            }
            $data['member']            = $member;
            $title                     = $member->firstname . ' ' . $member->lastname;
            $data['staff_departments'] = $this->departments_model->get_staff_departments($member->staffid);
        $data['staff_members']      = $this->staff_model->get('', ['active' => 1]);

            $ts_filter_data = [];
            if ($this->input->get('filter')) {
                if ($this->input->get('range') != 'period') {
                    $ts_filter_data[$this->input->get('range')] = true;
                } else {
                    $ts_filter_data['period-from'] = $this->input->get('period-from');
                    $ts_filter_data['period-to']   = $this->input->get('period-to');
                }
            } else {
                $ts_filter_data['this_month'] = true;
            }

            $data['logged_time'] = $this->staff_model->get_logged_time_data($id, $ts_filter_data);
            $data['timesheets']  = $data['logged_time']['timesheets'];
            $data['sc_staff_documents'] = $this->db->where('staff_id',(int)$id)->order_by('created_at','desc')->get(db_prefix().'sc_staff_documents')->result_array();
        }
        $this->load->model('currencies_model');
        $data['base_currency'] = $this->currencies_model->get_base_currency();
        $data['roles']         = $this->roles_model->get();
        $data['user_notes']    = $this->misc_model->get_notes($id, 'staff');
        $data['departments']   = $this->departments_model->get();
        // Smart Choice 4.2.8: populate the Supervisor/Manager searchable selector on the member form.
        $data['staff_members'] = $this->staff_model->get('', ['active' => 1]);
        $data['title']         = $title;

        $this->load->view('admin/staff/member', $data);
    }

    /* Get role permission for specific role id */
    public function role_changed($id)
    {
        if (staff_cant('view', 'staff')) {
            ajax_access_denied('staff');
        }

        echo json_encode($this->roles_model->get($id)->permissions);
    }

    public function save_dashboard_widgets_order()
    {
        hooks()->do_action('before_save_dashboard_widgets_order');

        $post_data = $this->input->post();
        foreach ($post_data as $container => $widgets) {
            if ($widgets == 'empty') {
                $post_data[$container] = [];
            }
        }
        update_staff_meta(get_staff_user_id(), 'dashboard_widgets_order', serialize($post_data));
    }

    public function save_dashboard_widgets_visibility()
    {
        hooks()->do_action('before_save_dashboard_widgets_visibility');

        $post_data = $this->input->post();
        update_staff_meta(get_staff_user_id(), 'dashboard_widgets_visibility', serialize($post_data['widgets']));
    }

    public function reset_dashboard()
    {
        update_staff_meta(get_staff_user_id(), 'dashboard_widgets_visibility', null);
        update_staff_meta(get_staff_user_id(), 'dashboard_widgets_order', null);

        redirect(admin_url());
    }

    public function save_hidden_table_columns()
    {
        hooks()->do_action('before_save_hidden_table_columns');
        $data   = $this->input->post();
        $id     = $data['id'];
        $hidden = isset($data['hidden']) ? $data['hidden'] : [];
        update_staff_meta(get_staff_user_id(), 'hidden-columns-' . $id, json_encode($hidden));
    }

    public function change_language($lang = '')
    {
        hooks()->do_action('before_staff_change_language', $lang);

        $this->db->where('staffid', get_staff_user_id());
        $this->db->update(db_prefix() . 'staff', ['default_language' => $lang]);
        
        redirect(previous_url() ?: $_SERVER['HTTP_REFERER']);
    }

    public function timesheets()
    {
        $data['view_all'] = false;
        if (staff_can('view-timesheets', 'reports') && $this->input->get('view') == 'all') {
            $data['staff_members_with_timesheets'] = $this->db->query('SELECT DISTINCT staff_id FROM ' . db_prefix() . 'taskstimers WHERE staff_id !=' . get_staff_user_id())->result_array();
            $data['view_all']                      = true;
        }

        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data('staff_timesheets', ['view_all' => $data['view_all']]);
        }

        if ($data['view_all'] == false) {
            unset($data['view_all']);
        }

        $data['logged_time'] = $this->staff_model->get_logged_time_data(get_staff_user_id());
        $data['title']       = '';
        $this->load->view('admin/staff/timesheets', $data);
    }

    public function delete()
    {
        if (!is_admin() && is_admin($this->input->post('id'))) {
            die('Busted, you can\'t delete administrators');
        }

        if (staff_can('delete',  'staff')) {
            $success = $this->staff_model->delete($this->input->post('id'), $this->input->post('transfer_data_to'));
            if ($success) {
                set_alert('success', _l('deleted', _l('staff_member')));
            }
        }

        redirect(admin_url('staff'));
    }

    /* When staff edit his profile */
    public function edit_profile()
    {
        hooks()->do_action('edit_logged_in_staff_profile');

        if ($this->input->post()) {
            handle_staff_profile_image_upload();
            $data = $this->input->post();
            // Don't do XSS clean here.
            $data['email_signature'] = $this->input->post('email_signature', false);
            $data['email_signature'] = html_entity_decode($data['email_signature']);

            if ($data['email_signature'] == strip_tags($data['email_signature'])) {
                // not contains HTML, add break lines
                $data['email_signature'] = nl2br_save_html($data['email_signature']);
            }

            $success = $this->staff_model->update_profile($data, get_staff_user_id());

            if ($success) {
                set_alert('success', _l('staff_profile_updated'));
            }

            redirect(admin_url('staff/edit_profile/' . get_staff_user_id()));
        }
        $member = $this->staff_model->get(get_staff_user_id());
        $this->load->model('departments_model');
        $data['member']            = $member;
        $data['departments']       = $this->departments_model->get();
        $data['staff_departments'] = $this->departments_model->get_staff_departments($member->staffid);
        $data['title']             = $member->firstname . ' ' . $member->lastname;
        $this->load->view('admin/staff/profile', $data);
    }

    /* Remove staff profile image / ajax */
    public function remove_staff_profile_image($id = '')
    {
        $staff_id = get_staff_user_id();
        if (is_numeric($id) && (staff_can('create',  'staff') || staff_can('edit',  'staff'))) {
            $staff_id = $id;
        }
        hooks()->do_action('before_remove_staff_profile_image');
        $member = $this->staff_model->get($staff_id);
        if (file_exists(get_upload_path_by_type('staff') . $staff_id)) {
            delete_dir(get_upload_path_by_type('staff') . $staff_id);
        }
        $this->db->where('staffid', $staff_id);
        $this->db->update(db_prefix() . 'staff', [
            'profile_image' => null,
        ]);

        if (!is_numeric($id)) {
            redirect(admin_url('staff/edit_profile/' . $staff_id));
        } else {
            redirect(admin_url('staff/member/' . $staff_id));
        }
    }

    /* When staff change his password */
    public function change_password_profile()
    {
        if ($this->input->post()) {
            $response = $this->staff_model->change_password($this->input->post(null, false), get_staff_user_id());
            if (is_array($response) && isset($response[0]['passwordnotmatch'])) {
                set_alert('danger', _l('staff_old_password_incorrect'));
            } else {
                if ($response == true) {
                    set_alert('success', _l('staff_password_changed'));
                } else {
                    set_alert('warning', _l('staff_problem_changing_password'));
                }
            }
            redirect(admin_url('staff/edit_profile'));
        }
    }

    /* View public profile. If id passed view profile by staff id else current user*/
    public function profile($id = '')
    {
        if ($id == '') {
            $id = get_staff_user_id();
        }

        hooks()->do_action('staff_profile_access', $id);

        $data['logged_time'] = $this->staff_model->get_logged_time_data($id);
        $data['staff_p']     = $this->staff_model->get($id);

        if (!$data['staff_p']) {
            blank_page('Staff Member Not Found', 'danger');
        }

        $this->load->model('departments_model');
        $data['staff_departments'] = $this->departments_model->get_staff_departments($data['staff_p']->staffid);
        $data['departments']       = $this->departments_model->get();
        $data['title']             = _l('staff_profile_string') . ' - ' . $data['staff_p']->firstname . ' ' . $data['staff_p']->lastname;
        // notifications
        $total_notifications = total_rows(db_prefix() . 'notifications', [
            'touserid' => get_staff_user_id(),
        ]);
        $data['total_pages'] = ceil($total_notifications / $this->misc_model->get_notifications_limit());
        $this->load->view('admin/staff/myprofile', $data);
    }

    /* Change status to staff active or inactive / ajax */
    public function change_staff_status($id, $status)
    {
        if (staff_can('edit',  'staff')) {
            if ($this->input->is_ajax_request()) {
                $this->staff_model->change_staff_status($id, $status);
            }
        }
    }

    /* Logged in staff notifications*/
    public function notifications()
    {
        $this->load->model('misc_model');
        if ($this->input->post()) {
            $page   = $this->input->post('page');
            $offset = ($page * $this->misc_model->get_notifications_limit());
            $this->db->limit($this->misc_model->get_notifications_limit(), $offset);
            $this->db->where('touserid', get_staff_user_id());
            $this->db->order_by('date', 'desc');
            $notifications = $this->db->get(db_prefix() . 'notifications')->result_array();
            $i             = 0;
            foreach ($notifications as $notification) {
                if (($notification['fromcompany'] == null && $notification['fromuserid'] != 0) || ($notification['fromcompany'] == null && $notification['fromclientid'] != 0)) {
                    if ($notification['fromuserid'] != 0) {
                        $notifications[$i]['profile_image'] = '<a href="' . admin_url('staff/profile/' . $notification['fromuserid']) . '">' . staff_profile_image($notification['fromuserid'], [
                            'staff-profile-image-small',
                            'img-circle',
                            'pull-left',
                        ]) . '</a>';
                    } else {
                        $notifications[$i]['profile_image'] = '<a href="' . admin_url('clients/client/' . $notification['fromclientid']) . '">
                    <img class="client-profile-image-small img-circle pull-left" src="' . contact_profile_image_url($notification['fromclientid']) . '"></a>';
                    }
                } else {
                    $notifications[$i]['profile_image'] = '';
                    $notifications[$i]['full_name']     = '';
                }
                $additional_data = '';
                if (!empty($notification['additional_data'])) {
                    $additional_data = unserialize($notification['additional_data']);
                    $x               = 0;
                    foreach ($additional_data as $data) {
                        if (strpos($data, '<lang>') !== false) {
                            $lang = get_string_between($data, '<lang>', '</lang>');
                            $temp = _l($lang);
                            if (strpos($temp, 'project_status_') !== false) {
                                $status = get_project_status_by_id(strafter($temp, 'project_status_'));
                                $temp   = $status['name'];
                            }
                            $additional_data[$x] = $temp;
                        }
                        $x++;
                    }
                }
                $notifications[$i]['description'] = _l($notification['description'], $additional_data);
                $notifications[$i]['date']        = time_ago($notification['date']);
                $notifications[$i]['full_date']   = _dt($notification['date']);
                $i++;
            } //$notifications as $notification
            echo json_encode($notifications);
            die;
        }
    }

    public function update_two_factor()
    {
        $fail_reason = _l('set_two_factor_authentication_failed');
        if ($this->input->post()) {
            $this->load->library('form_validation');
            $this->form_validation->set_rules('two_factor_auth', _l('two_factor_auth'), 'required');

            if ($this->input->post('two_factor_auth') == 'google') {
                $this->form_validation->set_rules('google_auth_code', _l('google_authentication_code'), 'required');
            }

            if ($this->form_validation->run() !== false) {
                $two_factor_auth_mode = $this->input->post('two_factor_auth');
                $id = get_staff_user_id();
                if ($two_factor_auth_mode == 'google') {
                    $this->load->model('Authentication_model');
                    $secret = $this->input->post('secret');
                    $success = $this->authentication_model->set_google_two_factor($secret);
                    $fail_reason = _l('set_google_two_factor_authentication_failed');
                } elseif ($two_factor_auth_mode == 'email') {
                    $this->db->where('staffid', $id);
                    $success = $this->db->update(db_prefix() . 'staff', ['two_factor_auth_enabled' => 1]);
                } else {
                    $this->db->where('staffid', $id);
                    $success = $this->db->update(db_prefix() . 'staff', ['two_factor_auth_enabled' => 0]);
                }
                if ($success) {
                    set_alert('success', _l('set_two_factor_authentication_successful'));
                    redirect(admin_url('staff/edit_profile/' . get_staff_user_id()));
                }
            }
        }
        set_alert('danger', $fail_reason);
        redirect(admin_url('staff/edit_profile/' . get_staff_user_id()));
    }

    public function verify_google_two_factor()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
            die;
        }

        if ($this->input->post()) {
            $data = $this->input->post();
            $this->load->model('authentication_model');
            $is_success = $this->authentication_model->is_google_two_factor_code_valid($data['code'],$data['secret']);
            $result = [];

            header('Content-Type: application/json');
            if ($is_success) {
                $result['status'] = 'success';
                $result['message'] = _l('google_2fa_code_valid');;

                echo json_encode($result);
                die;
            }

            $result['status'] = 'failed';
            $result['message'] = _l('google_2fa_code_invalid');;

            echo json_encode($result);
            die;
        }
    }


    public function sample_header()
    {
        if (staff_cant('create', 'staff') && !is_admin()) {
            access_denied('staff');
        }
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="staff_import_sample_header.csv"');
        echo "firstname,lastname,email,role,active,phonenumber,facebook,linkedin,whatsapp,telegram,religion,marital_status,children_count,children_json,server_login,server_ip,server_password\n";
        exit;
    }

    public function import_csv()
    {
        if (staff_cant('create', 'staff') && !is_admin()) {
            access_denied('staff');
        }
        if (!isset($_FILES['staff_import_file']) || $_FILES['staff_import_file']['error'] !== UPLOAD_ERR_OK) {
            set_alert('warning', 'Please select a valid CSV file.');
            redirect(admin_url('staff'));
        }
        $handle = fopen($_FILES['staff_import_file']['tmp_name'], 'r');
        if (!$handle) {
            set_alert('danger', 'Unable to read import file.');
            redirect(admin_url('staff'));
        }
        $header = fgetcsv($handle);
        if (!$header) {
            fclose($handle);
            set_alert('warning', 'The CSV file is empty.');
            redirect(admin_url('staff'));
        }
        $header = array_map(static function ($v) { return strtolower(trim($v)); }, $header);
        $created = 0;
        $skipped = 0;
        while (($row = fgetcsv($handle)) !== false) {
            $data = [];
            foreach ($header as $i => $key) {
                $data[$key] = isset($row[$i]) ? trim($row[$i]) : '';
            }
            if (empty($data['email']) || empty($data['firstname'])) {
                $skipped++;
                continue;
            }
            $exists = $this->db->where('email', $data['email'])->get(db_prefix().'staff')->row();
            if ($exists) {
                $skipped++;
                continue;
            }
            $insert = [
                'firstname' => $data['firstname'],
                'lastname' => $data['lastname'] ?? '',
                'email' => $data['email'],
                'phonenumber' => $data['phonenumber'] ?? '',
                'facebook' => $data['facebook'] ?? '',
                'linkedin' => $data['linkedin'] ?? '',
                'skype' => $data['whatsapp'] ?? ($data['skype'] ?? ''),
                'telegram' => $data['telegram'] ?? '',
                'religion' => $data['religion'] ?? '',
                'marital_status' => $data['marital_status'] ?? '',
                'children_count' => isset($data['children_count']) ? (int)$data['children_count'] : 0,
                'children_json' => $data['children_json'] ?? '',
                'server_login' => $data['server_login'] ?? '',
                'server_ip' => $data['server_ip'] ?? '',
                'server_password' => $data['server_password'] ?? '',
                'active' => isset($data['active']) && $data['active'] !== '' ? (int)$data['active'] : 1,
                'admin' => 0,
                'password' => app_hash_password(bin2hex(random_bytes(8))),
                'datecreated' => date('Y-m-d H:i:s'),
            ];
            if (!empty($data['role'])) {
                $role = $this->db->where('name', $data['role'])->get(db_prefix().'roles')->row();
                if ($role) { $insert['role'] = $role->roleid; }
            }
            $this->db->insert(db_prefix().'staff', $insert);
            if ($this->db->insert_id()) { $created++; }
        }
        fclose($handle);
        set_alert('success', 'Staff import finished. Created: '.$created.'. Skipped: '.$skipped.'.');
        redirect(admin_url('staff'));
    }

    public function mass_delete()
    {
        if (staff_cant('delete', 'staff') && !is_admin()) {
            echo json_encode(['success' => false, 'message' => 'Permission denied.']);
            return;
        }
        $ids = $this->input->post('ids');
        if (!is_array($ids)) { $ids = []; }
        $deleted = 0;
        foreach ($ids as $id) {
            $id = (int)$id;
            if ($id <= 0 || $id == get_staff_user_id() || $id == 1) { continue; }
            $this->db->where('staffid', $id)->delete(db_prefix().'staff');
            if ($this->db->affected_rows() > 0) { $deleted++; }
        }
        echo json_encode(['success' => $deleted > 0, 'message' => 'Deleted: '.$deleted]);
    }

    public function save_completed_checklist_visibility()
    {
        hooks()->do_action('before_save_completed_checklist_visibility');

        $post_data = $this->input->post();
        if (is_numeric($post_data['task_id'])) {
            update_staff_meta(get_staff_user_id(), 'task-hide-completed-items-'. $post_data['task_id'], $post_data['hideCompleted']);
        }
    }

    public function save_resume_certifications($id)
    {
        $id=(int)$id;
        if($id<1 || staff_cant('edit','staff')){ access_denied('staff'); }
        if($this->input->method()!=='post'){ show_404(); }
        $data=[
            'sc_resume_text'=>(string)$this->input->post('sc_resume_text', false),
            'sc_certifications_text'=>(string)$this->input->post('sc_certifications_text', false),
        ];
        $this->db->where('staffid',$id)->update(db_prefix().'staff',$data);
        $this->handle_sc_staff_documents($id);
        set_alert('success',_l('updated_successfully',_l('staff_member')));
        redirect(admin_url('staff/member/'.$id.'#staff_resume'));
    }

    /**
     * Store Smart Choice staff resume/certification uploads without affecting
     * the native staff profile save workflow.
     */
    private function handle_sc_staff_documents($staffId)
    {
        $staffId = (int) $staffId;
        if ($staffId < 1 || !$this->db->table_exists(db_prefix() . 'sc_staff_documents')) {
            return;
        }

        $groups = [
            'sc_resume_files' => ['type' => 'resume', 'allowed' => ['pdf','doc','docx','txt','rtf']],
            'sc_certification_files' => ['type' => 'certification', 'allowed' => ['pdf','jpg','jpeg','png','webp','doc','docx']],
        ];
        $baseRelative = 'uploads/staff_documents/' . $staffId . '/';
        $basePath = FCPATH . $baseRelative;
        _maybe_create_upload_path($basePath);

        foreach ($groups as $field => $cfg) {
            if (empty($_FILES[$field]['name']) || !is_array($_FILES[$field]['name'])) { continue; }
            foreach ($_FILES[$field]['name'] as $i => $originalName) {
                if ($originalName === '' || (int)($_FILES[$field]['error'][$i] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) { continue; }
                $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
                if (!in_array($ext, $cfg['allowed'], true)) {
                    log_message('error', 'Blocked staff document extension for staff ' . $staffId . ': ' . $ext);
                    continue;
                }
                $safeName = unique_filename($basePath, preg_replace('/[^A-Za-z0-9._-]/', '_', basename($originalName)));
                $target = $basePath . $safeName;
                if (!move_uploaded_file($_FILES[$field]['tmp_name'][$i], $target)) {
                    log_message('error', 'Unable to move staff document upload for staff ' . $staffId);
                    continue;
                }
                $this->db->insert(db_prefix() . 'sc_staff_documents', [
                    'staff_id' => $staffId,
                    'document_type' => $cfg['type'],
                    'file_name' => basename($originalName),
                    'file_type' => (string)($_FILES[$field]['type'][$i] ?? ''),
                    'file_path' => $baseRelative . $safeName,
                    'uploaded_by' => (int)get_staff_user_id(),
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }
        }
    }

}
