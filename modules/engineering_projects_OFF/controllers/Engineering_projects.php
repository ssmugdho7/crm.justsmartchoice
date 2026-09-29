<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Engineering_projects extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('engineering_projects/engineering_projects_model');
        $this->load->helper('engineering_projects/project_files');
    }

    public function index()
    {
        if (!$this->can_view()) {
            access_denied('engineering_projects');
        }

        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path('engineering_projects', 'table'));
            return;
        }

        $this->load->model('engineering_projects/contractors_model');
        $this->load->model('engineering_projects/drawings_model');

        $data['title']       = 'Engineering Hub';
        $data['contractors'] = $this->contractors_model->get('');
        $data['drawings']    = $this->drawings_model->get('');

        $this->load->view('engineering_projects/manage', $data);
    }

    public function engineering_project($id = '')
    {
        if (!$this->can_view()) {
            access_denied('engineering_projects');
        }

        $id = is_numeric($id) ? (int) $id : 0;

        if ($this->input->post()) {
            if ($id === 0 && !$this->can_create()) {
                access_denied('engineering_projects');
            }

            if ($id > 0 && !$this->can_edit()) {
                access_denied('engineering_projects');
            }

            $data = $this->project_payload();

            if ($id === 0) {
                $data['created_by'] = get_staff_user_id();
                $newId = $this->engineering_projects_model->add($data);

                set_alert(
                    $newId ? 'success' : 'warning',
                    $newId ? 'Engineering project created successfully.' : 'The engineering project could not be saved.'
                );
            } else {
                $this->engineering_projects_model->update($data, $id);
                set_alert('success', 'Engineering project updated successfully.');
            }

            redirect(admin_url('engineering_projects'));
        }

        $data                         = $this->common_form_data();
        $data['title']                = $id > 0 ? 'Edit Engineering Project' : 'New Engineering Project';
        $data['engineering_project']  = $id > 0 ? $this->engineering_projects_model->get($id) : null;
        $data['id']                   = $id;

        $this->load->view('engineering_projects/forms/project_form', $data);
    }

    public function drawings($action = '', $id = '')
    {
        $this->load->model('engineering_projects/drawings_model');

        if ($action === 'drawing' || $action === 'new' || is_numeric($action)) {
            if (is_numeric($action) && $id === '') {
                $id = $action;
            }

            return $this->drawing_form($id);
        }

        if (!$this->can_view()) {
            access_denied('engineering_projects');
        }

        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path('engineering_projects', 'drawings/table'));
            return;
        }

        $data['title'] = 'Drawings';

        $this->load->view('engineering_projects/drawings/manage', $data);
    }

    public function drawing_form($id = '')
    {
        $this->load->model('engineering_projects/drawings_model');

        if (!$this->can_view()) {
            access_denied('engineering_projects');
        }

        $id = is_numeric($id) ? (int) $id : 0;

        if ($this->input->post()) {
            if ($id === 0 && !$this->can_create()) {
                access_denied('engineering_projects');
            }

            if ($id > 0 && !$this->can_edit()) {
                access_denied('engineering_projects');
            }

            $postId = $this->input->post('id');

            if (is_numeric($postId) && (int) $postId > 0) {
                $id = (int) $postId;
            }

            $data = [
                'name'                   => trim((string) $this->input->post('name', true)),
                'type'                   => trim((string) $this->input->post('type', true)),
                'engineering_project_id' => (int) $this->input->post('engineering_project_id'),
                'project_id'             => (int) $this->input->post('project_id'),
                'customer_id'            => (int) $this->input->post('customer_id'),
            ];

            $data['folder_path'] = engproj_project_folder($data['customer_id'], $data['project_id'], 'Drawings');

            foreach (['draf', 'final_doc'] as $field) {
                $file = $this->upload_file($field, $data['folder_path']);

                if ($file !== '') {
                    $data[$field] = $file;
                }
            }

            if ($id === 0) {
                $newId = $this->drawings_model->add($data);

                set_alert(
                    $newId ? 'success' : 'warning',
                    $newId ? 'Drawing created successfully.' : 'The drawing could not be saved.'
                );
            } else {
                $this->drawings_model->update($data, $id);
                set_alert('success', 'Drawing updated successfully.');
            }

            redirect(admin_url('engineering_projects/drawings'));
        }

        $data            = $this->common_form_data();
        $data['title']   = $id > 0 ? 'Edit Drawing' : 'New Drawing';
        $data['drawing'] = $id > 0 ? $this->drawings_model->get($id) : null;
        $data['id']      = $id;

        $this->load->view('engineering_projects/forms/drawing_form', $data);
    }

    public function documents($action = '', $id = '')
    {
        $this->load->model('engineering_projects/documents_model');

        if ($action === 'document' || $action === 'new' || is_numeric($action)) {
            if (is_numeric($action) && $id === '') {
                $id = $action;
            }

            return $this->document_form($id);
        }

        if (!$this->can_view()) {
            access_denied('engineering_projects');
        }

        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path('engineering_projects', 'documents/table'));
            return;
        }

        $data['title'] = 'Documents';

        $this->load->view('engineering_projects/documents/manage', $data);
    }

    public function document_form($id = '')
    {
        $this->load->model('engineering_projects/documents_model');

        if (!$this->can_view()) {
            access_denied('engineering_projects');
        }

        $id = is_numeric($id) ? (int) $id : 0;

        if ($this->input->post()) {
            if ($id === 0 && !$this->can_create()) {
                access_denied('engineering_projects');
            }

            if ($id > 0 && !$this->can_edit()) {
                access_denied('engineering_projects');
            }

            $postId = $this->input->post('id');

            if (is_numeric($postId) && (int) $postId > 0) {
                $id = (int) $postId;
            }

            $data = [
                'name'                   => trim((string) $this->input->post('name', true)),
                'engineering_project_id' => (int) $this->input->post('engineering_project_id'),
                'project_id'             => (int) $this->input->post('project_id'),
                'customer_id'            => (int) $this->input->post('customer_id'),
            ];

            $data['folder_path'] = engproj_project_folder($data['customer_id'], $data['project_id'], 'Documents');

            foreach (['noc', 'eng_letter', 'site_insp', 'permit'] as $field) {
                $file = $this->upload_file($field, $data['folder_path']);

                if ($file !== '') {
                    $data[$field] = $file;
                }
            }

            if ($id === 0) {
                $newId = $this->documents_model->add($data);

                set_alert(
                    $newId ? 'success' : 'warning',
                    $newId ? 'Document created successfully.' : 'The document could not be saved.'
                );
            } else {
                $this->documents_model->update($data, $id);
                set_alert('success', 'Document updated successfully.');
            }

            redirect(admin_url('engineering_projects/documents'));
        }

        $data             = $this->common_form_data();
        $data['title']    = $id > 0 ? 'Edit Document' : 'New Document';
        $data['document'] = $id > 0 ? $this->documents_model->get($id) : null;
        $data['id']       = $id;

        $this->load->view('engineering_projects/forms/document_form', $data);
    }

    public function settings()
    {
        if (!is_admin()) {
            access_denied('engineering_projects');
        }

        if ($this->input->post()) {
            update_option('engproj_required_fields', json_encode($this->input->post('required_fields') ?: []));
            update_option('engproj_option_fields', json_encode($this->input->post('option_fields') ?: []));

            set_alert('success', 'Engineering Hub settings updated.');

            redirect(admin_url('engineering_projects/settings'));
        }

        $data['title']         = 'Engineering Hub Settings';
        $data['required']      = json_decode(get_option('engproj_required_fields') ?: '{}', true);
        $data['option_fields'] = json_decode(get_option('engproj_option_fields') ?: '[]', true);

        $this->load->view('engineering_projects/settings', $data);
    }

    public function delete($id)
    {
        if (!$this->can_delete()) {
            access_denied('engineering_projects');
        }

        if ($id) {
            $this->engineering_projects_model->delete((int) $id);
            set_alert('success', 'Engineering project deleted.');
        }

        redirect(admin_url('engineering_projects'));
    }

    private function project_payload()
    {
        $data = $this->input->post(null, false) ?: [];

        foreach (['drawings_ids', 'document_ids', 'shared_file_ids'] as $multi) {
            $data[$multi] = isset($data[$multi]) && is_array($data[$multi])
                ? implode(',', array_filter($data[$multi]))
                : ($data[$multi] ?? '');
        }

        foreach (['site_survey_schedule_date', 'install_schedule_date', 'final_inspection_date', 'start_date', 'end_date'] as $date) {
            if (isset($data[$date])) {
                $data[$date] = $data[$date] === '' ? null : to_sql_date($data[$date]);
            }
        }

        if (isset($data['options']) && is_array($data['options'])) {
            $data['extra_meta'] = json_encode($data['options']);
        }

        unset($data['options']);

        $allowed = [
            'project_id',
            'customer_id',
            'name',
            'start_date',
            'end_date',
            'site_survey_schedule',
            'site_survey_schedule_date',
            'contractor_id',
            'install_schedule',
            'install_schedule_date',
            'drawings_ids',
            'final_inspection',
            'final_inspection_date',
            'document_ids',
            'shared_file_ids',
            'folder_path',
            'tpo_status',
            'created_by',
            'extra_meta',
        ];

        $data = array_intersect_key($data, array_flip($allowed));

        $data['project_id']  = (int) ($data['project_id'] ?? 0);
        $data['customer_id'] = (int) ($data['customer_id'] ?? 0);
        $data['name']        = trim((string) ($data['name'] ?? ''));

        if ($data['name'] === '') {
            $data['name'] = 'Engineering Project ' . date('Y-m-d H:i');
        }

        $data['folder_path'] = engproj_project_folder($data['customer_id'], $data['project_id'], 'Engineering');

        return $data;
    }

    private function common_form_data()
    {
        $this->load->model('projects_model');
        $this->load->model('clients_model');
        $this->load->model('engineering_projects/engineering_projects_model');

        $data = [];

        $data['projects'] = $this->projects_model->get('');

        /*
         * Fix for MySQL error:
         * Column 'active' in where clause is ambiguous.
         *
         * Perfex clients_model joins other tables that may also contain an active column.
         * This forces MySQL to use tblclients.active.
         */
        $data['customers'] = $this->clients_model->get('', [
            db_prefix() . 'clients.active' => 1,
        ]);

        $data['engineering_projects'] = $this->engineering_projects_model->get('');

        return $data;
    }

    private function upload_file($field, $path)
    {
        if (empty($_FILES[$field]['name']) || empty($_FILES[$field]['tmp_name'])) {
            return '';
        }

        if (!is_uploaded_file($_FILES[$field]['tmp_name'])) {
            return '';
        }

        _maybe_create_upload_path($path);

        if (function_exists('_perfex_upload_error') && _perfex_upload_error($_FILES[$field]['error'])) {
            return '';
        }

        if (function_exists('_upload_extension_allowed') && !_upload_extension_allowed($_FILES[$field]['name'])) {
            return '';
        }

        $filename = unique_filename($path, $_FILES[$field]['name']);

        return move_uploaded_file($_FILES[$field]['tmp_name'], $path . $filename) ? $filename : '';
    }

    private function can_view()
    {
        return has_permission('engineering_projects', '', 'view') || is_admin();
    }

    private function can_create()
    {
        return has_permission('engineering_projects', '', 'create') || is_admin();
    }

    private function can_edit()
    {
        return has_permission('engineering_projects', '', 'edit') || is_admin();
    }

    private function can_delete()
    {
        return has_permission('engineering_projects', '', 'delete') || is_admin();
    }
}