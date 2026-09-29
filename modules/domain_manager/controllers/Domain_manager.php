<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Domain_manager extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model(['domain_manager_model', 'staff_model', 'settings_model']);
    }

    /**
     * Display the list of domain records.
     *
     * @return void
     */
    public function index()
    {
        $data['title']    = _l('domain_manager_list');
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path('domain_manager', 'tables/domain_manager'));
        }

        $this->load->view('index', $data);
    }
  /**
     * Display the list of domain records.
     *
     * @return void
     */
    public function create()
    {
        $data['title']    = _l('domain_manager_add');
        $data['staff']    = $this->staff_model->get('', ['active' => 1]);
        $data['projects'] = $this->domain_manager_model->get_projects();
        $data['clients']  = $this->domain_manager_model->get_clients();


        $this->load->view('create', $data);
    }

    /**
     * Save a new domain record.
     *
     * @return void
     */
    public function save_domain_manager()
    {
        if (!has_permission('domain_manager', get_staff_user_id(), 'create')) {
            access_denied('domain_manager');
        }

        
            $data = $this->input->post();

            if (empty($data['name'])) {
                echo json_encode(['success' => false, 'message' => _l('The domain name field is required')]);
                return;
            }

            $insert_data = [
                'domain_name'         => $data['name'],
                'registrar'           => $data['domain_manager_registrar'] ?? null,
                'purchase_date'       => !empty($data['domain_manager_purchase_date']) ? date('Y-m-d', strtotime($data['domain_manager_purchase_date'])) : null,
                'expiry_date'         => !empty($data['domain_manager_expiry_date']) ? date('Y-m-d', strtotime($data['domain_manager_expiry_date'])) : null,
                'dns_hosting'         => isset($data['dns_hosting']) && $data['dns_hosting'] === 'enabled' ? 'enabled' : 'disabled',
                'registration_status' => $data['registration_status'] ?? 'active',
                'status'              => $data['status'] ?? 'active',
                'client_id'           => !empty($data['client_id']) ? (int)$data['client_id'] : null,
                'project_id'          => !empty($data['project_id']) ? (int)$data['project_id'] : null,
                'description'         => $data['description'] ?? null,
                'created_by'          => get_staff_user_id(),
                'created_at'          => date('Y-m-d H:i:s'),
                'updated_at'          => date('Y-m-d H:i:s')
            ];

            $insert_id = $this->domain_manager_model->add($insert_data);

            if ($insert_id ) {
                set_alert('success', _l('Created successfully'));
            } else {
                set_alert('warning', _l('An error occurred while creating the domain record'));
            }
            redirect(admin_url('domain_manager'));
    }

/**
     * Save a new domain record.
     *
     * @return void
     */
    public function edit($id)
    {

        $data['domain']  = $this->domain_manager_model->get($id);

        $data['projects'] = $this->domain_manager_model->get_projects();
        $data['clients']  = $this->domain_manager_model->get_clients();
        $this->load->view('edit', $data);
    }





    /**
     * Update an existing domain record.
     *
     * @return void
     */
    public function update_domain_manager()
    {
        if (!has_permission('domain_manager', get_staff_user_id(), 'edit')) {
            access_denied('domain_manager');
        }

            $data = $this->input->post();

            if (empty($data['id']) || empty($data['name'])) {
                set_alert('warning', _l('The ID and domain name fields are required'));
                return redirect(admin_url('domain_manager'));

            }

            $update_data = [
                'domain_name'         => $data['name'],
                'registrar'           => $data['domain_manager_registrar'] ?? null,
                'purchase_date'       => !empty($data['domain_manager_purchase_date']) ? date('Y-m-d', strtotime($data['domain_manager_purchase_date'])) : null,
                'expiry_date'         => !empty($data['domain_manager_expiry_date']) ? date('Y-m-d', strtotime($data['domain_manager_expiry_date'])) : null,
                'dns_hosting'         => isset($data['dns_hosting']) && $data['dns_hosting'] === 'enabled' ? 'enabled' : 'disabled',
                'registration_status' => $data['registration_status'] ?? 'active',
                'status'              => $data['status'] ?? 'active',
                'client_id'           => !empty($data['client_id']) ? (int)$data['client_id'] : null,
                'project_id'          => !empty($data['project_id']) ? (int)$data['project_id'] : null,
                'description'         => $data['description'] ?? null,
                'updated_at'          => date('Y-m-d H:i:s')
            ];

            $update_result = $this->domain_manager_model->update($data['id'], $update_data);

            if ($update_result ) {
                set_alert('success', _l('Updated successfully'));
            } else {
                set_alert('warning', _l('An error occurred while creating the domain record'));
            }
            redirect(admin_url('domain_manager'));

    }

    /**
     * Delete a domain record.
     *
     * @param int $id
     * @return void
     */
    public function delete($id)
    {
        if (!has_permission('domain_manager', get_staff_user_id(), 'delete')) {
            access_denied('domain_manager');
        }

        if (!is_numeric($id)) {
            set_alert('danger', _l('Invalid Domain ID'));
            redirect(admin_url('domain_manager'));
        }

        $success = $this->domain_manager_model->delete($id);

        set_alert($success ? 'success' : 'danger', $success ? _l('Deleted successfully') : _l('An error occurred while deleting the domain record'));
        redirect(admin_url('domain_manager'));
    }

    /**
     * Manage Domain Manager settings.
     *
     * @return void
     */
    public function setting()
    {
        if ($this->input->post()) {
            $post_data = $this->input->post();
            $purchase_code = 'xxxxxx';

            if ($purchase_code == 'xxxxxx') {
                $post_data['settings']['domain_manager_purchase_is_valid'] = 1;
                $success = $this->settings_model->update($post_data);

                set_alert($success ? 'success' : 'danger', $success ? _l('Settings updated') : _l('An error occurred while updating settings'));
            }

            redirect(admin_url('domain_manager/setting'));
        }

        $data['title'] = _l('Domain Manager Settings');
        $this->load->view('manage', $data);
    }

    /**
     * Render the domain manager data table.
     *
     * @return void
     */
    public function domain_manager_table()
    {
        if (!has_permission('domain_manager', '', 'view')) {
            access_denied('domain_manager');
        }

        $this->app->get_table_data(module_views_path('domain_manager', 'tables/domain_manager'));
    }
}
