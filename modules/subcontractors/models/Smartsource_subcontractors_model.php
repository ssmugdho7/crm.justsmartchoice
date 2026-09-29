<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Smartsource_subcontractors_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get($id = '')
    {
        if (is_numeric($id)) {
            $this->db->where('id', (int) $id);
            return $this->db->get(db_prefix() . 'smartsource_subcontractors')->row();
        }

        $this->db->order_by('company', 'ASC');
        return $this->db->get(db_prefix() . 'smartsource_subcontractors')->result_array();
    }

    public function get_by_project($project_id)
    {
        $this->db->select('s.*, l.trade as project_trade, l.scope as project_scope, l.datecreated as linked_date');
        $this->db->from(db_prefix() . 'smartsource_subcontractor_project_links l');
        $this->db->join(db_prefix() . 'smartsource_subcontractors s', 's.id = l.subcontractor_id', 'left');
        $this->db->where('l.project_id', (int) $project_id);
        return $this->db->get()->result_array();
    }

    public function add($data)
    {
        $data = $this->prepare_subcontractor_data($data);
        if (empty($data['portal_token'])) {
            $data['portal_token'] = bin2hex(random_bytes(24));
        }
        $data['created_by'] = function_exists('get_staff_user_id') ? ((int) get_staff_user_id()) : 0;
        $data['datecreated'] = date('Y-m-d H:i:s');

        $inserted = $this->db->insert(db_prefix() . 'smartsource_subcontractors', $data);
        $id = (int) $this->db->insert_id();
        if (!$inserted || $id <= 0) {
            $error = $this->db->error();
            throw new RuntimeException(!empty($error['message']) ? $error['message'] : 'The subcontractor record was not accepted by the database.');
        }

        if ($id) {
            $this->handle_custom_fields_safely($id, 'smartsource_subcontractors');
            log_activity('Smartsource Sub Contractor Created [ID: ' . $id . ']');
        }

        return $id;
    }

    public function update($data, $id)
    {
        $data = $this->prepare_subcontractor_data($data);
        $data['dateupdated'] = date('Y-m-d H:i:s');

        $this->db->where('id', (int) $id);
        $this->db->update(db_prefix() . 'smartsource_subcontractors', $data);

        $this->handle_custom_fields_safely($id, 'smartsource_subcontractors');

        log_activity('Smartsource Sub Contractor Updated [ID: ' . $id . ']');
        return true;
    }

    public function delete($id)
    {
        $id = (int) $id;
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'smartsource_subcontractors');

        if ($this->db->affected_rows() > 0) {
            $this->db->where('subcontractor_id', $id)->delete(db_prefix() . 'smartsource_subcontractor_project_links');
            log_activity('Smartsource Sub Contractor Deleted [ID: ' . $id . ']');
            return true;
        }

        return false;
    }

    public function add_contract($data)
    {
        $data = $this->prepare_contract_data($data);
        if (empty($data['subject']) || empty($data['subcontractor_id'])) {
            throw new InvalidArgumentException('Contract subject and subcontractor are required.');
        }
        if (!$this->get((int) $data['subcontractor_id'])) {
            throw new InvalidArgumentException('The selected subcontractor does not exist.');
        }
        $data['created_by'] = get_staff_user_id();
        $data['datecreated'] = date('Y-m-d H:i:s');
        if (empty($data['public_token'])) {
            $data['public_token'] = bin2hex(random_bytes(24));
        }

        $this->db->trans_begin();
        $this->db->insert(db_prefix() . 'smartsource_subcontractor_contracts', $data);
        $id = (int) $this->db->insert_id();
        if (!$id || $this->db->trans_status() === false) {
            $error = $this->db->error();
            $this->db->trans_rollback();
            throw new RuntimeException('Database error creating subcontractor contract' . (!empty($error['message']) ? ': ' . $error['message'] : '.'));
        }

        if (!empty($data['project_id'])) {
            $this->link_to_project((int) $data['subcontractor_id'], (int) $data['project_id'], $data['contract_type'] ?? '', 'Linked from subcontractor contract #' . $id);
        }
        $this->handle_custom_fields_safely($id, 'smartsource_subcontractor_contracts');
        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            throw new RuntimeException('The contract was not saved completely.');
        }
        $this->db->trans_commit();
        log_activity('Smartsource Subcontractor Contract Created [ID: ' . $id . ']');
        return $id;
    }

    public function update_contract($data, $id)
    {
        $data = $this->prepare_contract_data($data);
        $data['dateupdated'] = date('Y-m-d H:i:s');

        $this->db->where('id', (int) $id);
        $this->db->update(db_prefix() . 'smartsource_subcontractor_contracts', $data);

        if (!empty($data['project_id'])) {
            $this->link_to_project($data['subcontractor_id'], $data['project_id'], $data['contract_type'] ?? '', 'Linked from subcontractor contract #' . $id);
        }

        $this->handle_custom_fields_safely($id, 'smartsource_subcontractor_contracts');

        log_activity('Smartsource Subcontractor Contract Updated [ID: ' . $id . ']');
        return true;
    }

    public function get_contract($id = '', $filters = [])
    {
        $this->db->select('c.*, s.company as subcontractor_company, s.contact_name, s.email, s.phone, p.name as project_name');
        $this->db->from(db_prefix() . 'smartsource_subcontractor_contracts c');
        $this->db->join(db_prefix() . 'smartsource_subcontractors s', 's.id = c.subcontractor_id', 'left');
        $this->db->join(db_prefix() . 'projects p', 'p.id = c.project_id', 'left');

        if (is_numeric($id)) {
            $this->db->where('c.id', (int) $id);
            return $this->db->get()->row();
        }

        if (!isset($filters['include_trash']) || (string) $filters['include_trash'] !== '1') {
            $this->db->where('c.is_trash', 0);
        }

        if (!empty($filters['status'])) {
            $this->db->where('c.status', $filters['status']);
        }

        if (!empty($filters['contract_type'])) {
            $this->db->where('c.contract_type', $filters['contract_type']);
        }

        if (!empty($filters['subcontractor_id'])) {
            $this->db->where('c.subcontractor_id', (int) $filters['subcontractor_id']);
        }

        if (!empty($filters['stat'])) {
            $today = date('Y-m-d');
            $soon = date('Y-m-d', strtotime('+30 days'));
            if ($filters['stat'] === 'active') {
                $this->db->where('c.is_trash', 0)->where_in('c.status', ['sent', 'signed', 'completed']);
            } elseif ($filters['stat'] === 'expired') {
                $this->db->where('c.is_trash', 0)->where('c.end_date <', $today)->where('c.end_date IS NOT NULL', null, false);
            } elseif ($filters['stat'] === 'expiring') {
                $this->db->where('c.is_trash', 0)->where('c.end_date >=', $today)->where('c.end_date <=', $soon);
            } elseif ($filters['stat'] === 'trash') {
                $this->db->where('c.is_trash', 1);
            }
        }

        $this->db->order_by('c.datecreated', 'DESC');
        return $this->db->get()->result_array();
    }

    public function get_project_contracts($project_id)
    {
        return $this->get_contract('', ['include_trash' => '0', 'project_id' => (int) $project_id]);
    }

    public function get_contracts_for_project($project_id)
    {
        $this->db->select('c.*, s.company as subcontractor_company');
        $this->db->from(db_prefix() . 'smartsource_subcontractor_contracts c');
        $this->db->join(db_prefix() . 'smartsource_subcontractors s', 's.id = c.subcontractor_id', 'left');
        $this->db->where('c.project_id', (int) $project_id);
        $this->db->where('c.is_trash', 0);
        $this->db->order_by('c.datecreated', 'DESC');
        return $this->db->get()->result_array();
    }

    public function trash_contract($id)
    {
        $this->db->where('id', (int) $id);
        $this->db->update(db_prefix() . 'smartsource_subcontractor_contracts', ['is_trash' => 1, 'hidden_from_customer' => 1, 'dateupdated' => date('Y-m-d H:i:s')]);
        log_activity('Smartsource Subcontractor Contract Sent To Trash [ID: ' . (int) $id . ']');
        return true;
    }

    public function restore_contract($id)
    {
        $this->db->where('id', (int) $id);
        $this->db->update(db_prefix() . 'smartsource_subcontractor_contracts', ['is_trash' => 0, 'dateupdated' => date('Y-m-d H:i:s')]);
        log_activity('Smartsource Subcontractor Contract Restored [ID: ' . (int) $id . ']');
        return true;
    }

    public function permanent_delete_contract($id)
    {
        $id = (int) $id;
        $files = $this->get_files($id, 'contract');
        foreach ($files as $file) {
            $this->delete_file($file['id']);
        }
        $this->db->where('id', $id)->delete(db_prefix() . 'smartsource_subcontractor_contracts');
        log_activity('Smartsource Subcontractor Contract Permanently Deleted [ID: ' . $id . ']');
        return true;
    }

    public function link_to_project($subcontractor_id, $project_id, $trade = '', $scope = '')
    {
        if (empty($subcontractor_id) || empty($project_id)) {
            return 0;
        }

        $exists = $this->db->where('subcontractor_id', (int) $subcontractor_id)
            ->where('project_id', (int) $project_id)
            ->get(db_prefix() . 'smartsource_subcontractor_project_links')
            ->row();

        if ($exists) {
            return $exists->id;
        }

        $this->db->insert(db_prefix() . 'smartsource_subcontractor_project_links', [
            'subcontractor_id' => (int) $subcontractor_id,
            'project_id'       => (int) $project_id,
            'trade'            => $trade,
            'scope'            => $scope,
            'created_by'       => get_staff_user_id(),
            'datecreated'      => date('Y-m-d H:i:s'),
        ]);

        return $this->db->insert_id();
    }

    public function add_file($rel_id, $rel_type, $file)
    {
        $this->db->insert(db_prefix() . 'smartsource_subcontractor_files', [
            'rel_id'             => (int) $rel_id,
            'rel_type'           => $rel_type,
            'file_name'          => $file['file_name'],
            'original_file_name' => $file['original_file_name'] ?? $file['file_name'],
            'filetype'           => $file['filetype'] ?? '',
            'visible_to_customer'=> !empty($file['visible_to_customer']) ? 1 : 0,
            'staffid'            => get_staff_user_id(),
            'dateadded'          => date('Y-m-d H:i:s'),
        ]);

        return $this->db->insert_id();
    }

    public function get_files($rel_id, $rel_type)
    {
        return $this->db->where('rel_id', (int) $rel_id)
            ->where('rel_type', $rel_type)
            ->order_by('dateadded', 'DESC')
            ->get(db_prefix() . 'smartsource_subcontractor_files')
            ->result_array();
    }

    public function delete_file($id)
    {
        $file = $this->db->where('id', (int) $id)->get(db_prefix() . 'smartsource_subcontractor_files')->row();
        if (!$file) {
            return false;
        }

        $path = SMARTSOURCE_SUBCONTRACTORS_UPLOAD_FOLDER . $file->rel_type . '/' . $file->rel_id . '/' . $file->file_name;
        if (file_exists($path)) {
            @unlink($path);
        }

        $this->db->where('id', (int) $id)->delete(db_prefix() . 'smartsource_subcontractor_files');
        return $this->db->affected_rows() > 0;
    }

    public function get_stats()
    {
        $today = date('Y-m-d');
        $soon = date('Y-m-d', strtotime('+30 days'));
        return [
            'active_subcontractors' => (int) $this->db->where('status', 'active')->count_all_results(db_prefix() . 'smartsource_subcontractors'),
            'total_subcontractors'  => (int) $this->db->count_all_results(db_prefix() . 'smartsource_subcontractors'),
            'active_contracts'      => (int) $this->db->where('is_trash', 0)->where_in('status', ['sent','signed','completed'])->count_all_results(db_prefix() . 'smartsource_subcontractor_contracts'),
            'expired_contracts'     => (int) $this->db->where('is_trash', 0)->where('end_date <', $today)->where('end_date IS NOT NULL', null, false)->count_all_results(db_prefix() . 'smartsource_subcontractor_contracts'),
            'expiring_contracts'    => (int) $this->db->where('is_trash', 0)->where('end_date >=', $today)->where('end_date <=', $soon)->count_all_results(db_prefix() . 'smartsource_subcontractor_contracts'),
            'expiring_insurance'    => (int) $this->db->where('insurance_expiration >=', $today)->where('insurance_expiration <=', $soon)->count_all_results(db_prefix() . 'smartsource_subcontractors'),
            'trash_contracts'       => (int) $this->db->where('is_trash', 1)->count_all_results(db_prefix() . 'smartsource_subcontractor_contracts'),
            'total_contract_value'  => (float) ($this->db->select_sum('contract_value')->where('is_trash', 0)->get(db_prefix() . 'smartsource_subcontractor_contracts')->row()->contract_value ?? 0),
        ];
    }

    public function get_chart_data()
    {
        $types = $this->db->select('contract_type, COUNT(id) as total, SUM(contract_value) as value')
            ->where('is_trash', 0)
            ->group_by('contract_type')
            ->get(db_prefix() . 'smartsource_subcontractor_contracts')->result_array();

        return $types;
    }

    public function get_categories()
    {
        return $this->db->order_by('name', 'ASC')->get(db_prefix() . 'smartsource_subcontractor_categories')->result_array();
    }

    public function get_subcontractor_statuses_db()
    {
        return $this->db->order_by('name', 'ASC')->get(db_prefix() . 'smartsource_subcontractor_statuses')->result_array();
    }

    public function get_contract_statuses_db()
    {
        return $this->db->order_by('name', 'ASC')->get(db_prefix() . 'smartsource_subcontractor_contract_statuses')->result_array();
    }

    public function save_simple_record($table, $data)
    {
        $data['datecreated'] = date('Y-m-d H:i:s');
        $this->db->insert(db_prefix() . $table, $data);
        return $this->db->insert_id();
    }

    public function delete_simple_record($table, $id)
    {
        $this->db->where('id', (int) $id)->delete(db_prefix() . $table);
        return true;
    }

    public function get_templates($id = '')
    {
        if (is_numeric($id)) {
            return $this->db->where('id', (int) $id)->get(db_prefix() . 'smartsource_subcontractor_templates')->row();
        }
        return $this->db->order_by('name', 'ASC')->get(db_prefix() . 'smartsource_subcontractor_templates')->result_array();
    }

    public function add_template($data)
    {
        $data = [
            'name' => $data['name'] ?? '',
            'contract_type' => $data['contract_type'] ?? '',
            'content' => $data['content'] ?? '',
            'created_by' => get_staff_user_id(),
            'datecreated' => date('Y-m-d H:i:s'),
        ];
        $this->db->insert(db_prefix() . 'smartsource_subcontractor_templates', $data);
        return $this->db->insert_id();
    }

    public function update_template($data, $id)
    {
        $data = [
            'name' => $data['name'] ?? '',
            'contract_type' => $data['contract_type'] ?? '',
            'content' => $data['content'] ?? '',
            'dateupdated' => date('Y-m-d H:i:s'),
        ];
        $this->db->where('id', (int) $id)->update(db_prefix() . 'smartsource_subcontractor_templates', $data);
        return true;
    }

    public function delete_template($id)
    {
        $this->db->where('id', (int) $id)->delete(db_prefix() . 'smartsource_subcontractor_templates');
        return true;
    }

    public function copy_template($id)
    {
        $template = $this->get_templates($id);
        if (!$template) {
            return false;
        }
        return $this->add_template([
            'name' => $template->name . ' Copy',
            'contract_type' => $template->contract_type,
            'content' => $template->content,
        ]);
    }

    public function health_check()
    {
        $checks = [];
        $tables = [
            'smartsource_subcontractors',
            'smartsource_subcontractor_contracts',
            'smartsource_subcontractor_files',
            'smartsource_subcontractor_project_links',
            'smartsource_subcontractor_categories',
            'smartsource_subcontractor_statuses',
            'smartsource_subcontractor_contract_statuses',
            'smartsource_subcontractor_templates',
        ];
        foreach ($tables as $table) {
            $checks[] = ['name' => function_exists('smartsource_clean_label') ? smartsource_clean_label($table) : ucwords(str_replace('_', ' ', $table)), 'ok' => $this->db->table_exists(db_prefix() . $table)];
        }
        $checks[] = ['name' => 'Upload Folder Writable', 'ok' => is_dir(SMARTSOURCE_SUBCONTRACTORS_UPLOAD_FOLDER) && is_writable(SMARTSOURCE_SUBCONTRACTORS_UPLOAD_FOLDER)];
        $checks[] = ['name' => 'Module Enabled', 'ok' => get_option('smartsource_subcontractors_enabled') === '1'];
        return $checks;
    }


    public function save_contract_signature($contract_id, $role, $initials, $signatureData, $initialsData = '', array $identity = [])
    {
        $contract_id = (int) $contract_id;
        $role = $role === 'company' ? 'company' : 'subcontractor';
        $table = db_prefix() . 'smartsource_subcontractor_contracts';
        if (!$this->db->table_exists($table) || !$this->get_contract($contract_id)) {
            throw new RuntimeException('The subcontractor contract was not found.');
        }
        if ($signatureData === '' || $initialsData === '') {
            throw new RuntimeException('Both the full signature and drawn initials are required.');
        }
        if (!function_exists('process_digital_signature_image') || !function_exists('process_digital_initials_image')) {
            $this->load->helper('misc');
        }

        $uploadPath = SMARTSOURCE_SUBCONTRACTORS_UPLOAD_FOLDER . 'contract/' . $contract_id . '/signatures/';
        $this->db->trans_begin();
        try {
            if (!process_digital_signature_image($signatureData, $uploadPath)) {
                throw new RuntimeException('The signature image could not be saved.');
            }
            $signatureFile = isset($GLOBALS['processed_digital_signature']) ? (string) $GLOBALS['processed_digital_signature'] : '';
            $initialsFile = process_digital_initials_image($initialsData, $uploadPath);
            if ($signatureFile === '' || !$initialsFile) {
                throw new RuntimeException('The signature or initials file could not be saved.');
            }

            $relative = 'uploads/smartsource_subcontractors/contract/' . $contract_id . '/signatures/';
            $now = date('Y-m-d H:i:s');
            $ip = $this->input->ip_address();
            $typed = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', (string) $initials), 0, 12));
            if ($typed === '') {
                throw new RuntimeException('Typed initials are required.');
            }

            if ($role === 'company') {
                $data = [
                    'company_initials' => $typed,
                    'company_signature' => $relative . $signatureFile,
                    'company_signed_at' => $now,
                    'company_signed_ip' => $ip,
                ];
                if ($this->db->field_exists('company_initials_signature', $table)) {
                    $data['company_initials_signature'] = $relative . $initialsFile;
                }
            } else {
                $data = [
                    'subcontractor_initials' => $typed,
                    'subcontractor_signature' => $relative . $signatureFile,
                    'subcontractor_signed_at' => $now,
                    'subcontractor_signed_ip' => $ip,
                    'signed_date' => date('Y-m-d'),
                    'status' => 'signed',
                    'dateupdated' => $now,
                ];
                if ($this->db->field_exists('subcontractor_initials_signature', $table)) {
                    $data['subcontractor_initials_signature'] = $relative . $initialsFile;
                }
                foreach (['acceptance_firstname','acceptance_lastname','acceptance_email'] as $field) {
                    if ($this->db->field_exists($field, $table)) {
                        $data[$field] = trim((string) ($identity[$field] ?? ''));
                    }
                }
                if ($this->db->field_exists('acceptance_date', $table)) {
                    $data['acceptance_date'] = $now;
                }
                if ($this->db->field_exists('last_ip', $table)) {
                    $data['last_ip'] = $ip;
                }
            }
            $this->db->where('id', $contract_id)->update($table, $data);
            if ($this->db->trans_status() === false) {
                $error = $this->db->error();
                throw new RuntimeException('The signature could not be recorded' . (!empty($error['message']) ? ': ' . $error['message'] : '.'));
            }
            $this->db->trans_commit();
            return true;
        } catch (Throwable $e) {
            $this->db->trans_rollback();
            log_message('error', 'Smartsource contract signature storage failed [Contract ID: ' . $contract_id . ']: ' . $e->getMessage());
            throw $e;
        }
    }

    public function render_contract_content($contract)
    {
        $content = $contract->content ?? '';
        $companySignature = function_exists('smartsource_signature_img') ? smartsource_signature_img($contract->company_signature ?? '', 'Company Signature') : '';
        $subSignature = function_exists('smartsource_signature_img') ? smartsource_signature_img($contract->subcontractor_signature ?? '', 'Subcontractor Signature') : '';
        $stamp = '';
        if (!empty($contract->company_signed_at)) {
            $stamp .= '<p><small><strong>Company Signed:</strong> ' . html_escape($contract->company_signed_at) . ' | IP: ' . html_escape($contract->company_signed_ip) . '</small></p>';
        }
        if (!empty($contract->subcontractor_signed_at)) {
            $stamp .= '<p><small><strong>Subcontractor Signed:</strong> ' . html_escape($contract->subcontractor_signed_at) . ' | IP: ' . html_escape($contract->subcontractor_signed_ip) . '</small></p>';
        }

        $merge = [
            '{subcontractor_name}' => $contract->subcontractor_company ?? '',
            '{subcontractor_email}' => $contract->email ?? '',
            '{subcontractor_phone}' => $contract->phone ?? '',
            '{subcontractor_initials}' => $contract->subcontractor_initials ?? '',
            '{company_initials}' => $contract->company_initials ?? '',
            '{subcontractor_signature}' => $subSignature,
            '{company_signature}' => $companySignature,
            '{contract_signed_stamp}' => $stamp,
            '{contract_id}' => $contract->id ?? '',
            '{contract_subject}' => $contract->subject ?? '',
            '{contract_value}' => $contract->contract_value ?? '',
            '{project_name}' => $contract->project_name ?? '',
        ];

        return str_replace(array_keys($merge), array_values($merge), $content);
    }

    public function get_by_portal_token($token)
    {
        $token = trim((string) $token);
        if ($token === '') {
            return null;
        }
        return $this->db->where('portal_token', $token)
            ->where('portal_enabled', 1)
            ->get(db_prefix() . 'smartsource_subcontractors')
            ->row();
    }

    public function update_from_portal($token, $data)
    {
        $subcontractor = $this->get_by_portal_token($token);
        if (!$subcontractor) {
            return false;
        }
        $clean = $this->prepare_subcontractor_data($data);
        unset($clean['portal_token'], $clean['portal_enabled'], $clean['assigned'], $clean['staff_id']);
        $clean['dateupdated'] = date('Y-m-d H:i:s');
        $this->db->where('id', (int) $subcontractor->id)->update(db_prefix() . 'smartsource_subcontractors', $clean);
        return (int) $subcontractor->id;
    }


    public function find_existing_portal_subcontractor($data)
    {
        $email = strtolower(trim((string) ($data['email'] ?? '')));
        $phone = preg_replace('/[^0-9]/', '', (string) ($data['phone'] ?? ''));
        $rows = $this->db->select('id,email,phone')->get(db_prefix() . 'smartsource_subcontractors')->result();
        foreach ($rows as $row) {
            if ($email !== '' && strtolower(trim((string) $row->email)) === $email) {
                return $row;
            }
            $storedPhone = preg_replace('/[^0-9]/', '', (string) $row->phone);
            if ($phone !== '' && $storedPhone !== '' && $storedPhone === $phone) {
                return $row;
            }
        }
        return null;
    }

    public function ensure_portal_token($id)
    {
        $subcontractor = $this->get((int)$id);
        if (!$subcontractor) {
            return '';
        }

        if (!empty($subcontractor->portal_token)) {
            if ((int)$subcontractor->portal_enabled !== 1) {
                $this->db->where('id', (int)$id)->update(db_prefix() . 'smartsource_subcontractors', ['portal_enabled' => 1]);
            }
            return $subcontractor->portal_token;
        }

        $token = bin2hex(random_bytes(24));
        $this->db->where('id', (int)$id)->update(db_prefix() . 'smartsource_subcontractors', [
            'portal_token' => $token,
            'portal_enabled' => 1,
        ]);

        return $token;
    }

    public function get_or_create_portal_token($id)
    {
        $subcontractor = $this->get((int) $id);
        if (!$subcontractor) {
            return '';
        }
        if (!empty($subcontractor->portal_token)) {
            return $subcontractor->portal_token;
        }
        $token = bin2hex(random_bytes(24));
        $this->db->where('id', (int) $id)->update(db_prefix() . 'smartsource_subcontractors', [
            'portal_token' => $token,
            'portal_enabled' => 1,
        ]);
        return $token;
    }

    /**
     * Create or link a native Perfex staff account for a portal applicant.
     * Existing staff records are reused by email to prevent duplicates.
     */
    public function provision_staff_account($subcontractor_id, array $data)
    {
        $subcontractor_id = (int) $subcontractor_id;
        $email = strtolower(trim((string) ($data['email'] ?? '')));
        if ($subcontractor_id <= 0 || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return 0;
        }

        $staffTable = db_prefix() . 'staff';
        if (!$this->db->table_exists($staffTable)) {
            log_activity('Subcontractor staff provisioning skipped: staff table is unavailable.');
            return 0;
        }

        $existing = $this->db->where('LOWER(email)', $email)->get($staffTable)->row();
        $roleId = $this->get_subcontractor_role_id();
        if ($existing) {
            $updates = [];
            if ($this->db->field_exists('active', $staffTable)) {
                $updates['active'] = 1;
            }
            if ($roleId > 0 && $this->db->field_exists('role', $staffTable)) {
                $updates['role'] = $roleId;
            }
            if ($this->db->field_exists('is_subcontractor', $staffTable)) {
                $updates['is_subcontractor'] = 1;
            }
            if ($this->db->field_exists('smartsource_subcontractor_id', $staffTable)) {
                $updates['smartsource_subcontractor_id'] = $subcontractor_id;
            }
            if (!empty($updates)) {
                $this->db->where('staffid', (int) $existing->staffid)->update($staffTable, $updates);
            }
            $this->db->where('id', $subcontractor_id)->update(db_prefix() . 'smartsource_subcontractors', ['staff_id' => (int) $existing->staffid]);
            return (int) $existing->staffid;
        }

        $contactName = trim((string) ($data['contact_name'] ?? ''));
        if ($contactName === '') {
            $contactName = trim((string) ($data['company'] ?? 'Subcontractor'));
        }
        $parts = preg_split('/\s+/', $contactName, -1, PREG_SPLIT_NO_EMPTY);
        $firstname = $parts ? array_shift($parts) : 'Subcontractor';
        $lastname = $parts ? implode(' ', $parts) : trim((string) ($data['company'] ?? 'Account'));
        if ($lastname === '') {
            $lastname = 'Account';
        }

        $this->load->model('staff_model');
        $password = bin2hex(random_bytes(8)) . 'A!9';
        $staffData = [
            'firstname' => $firstname,
            'lastname' => $lastname,
            'email' => $email,
            'password' => $password,
            'role' => $roleId,
            'active' => 1,
            'admin' => 0,
            // The module sends its registered welcome template after the browser response.
            'send_welcome_email' => 0,
        ];

        try {
            $staffId = (int) $this->staff_model->add($staffData);
        } catch (Throwable $e) {
            log_activity('Subcontractor staff provisioning failed: ' . $e->getMessage());
            return 0;
        }

        if ($staffId > 0) {
            $staffUpdates = [];
            if ($this->db->field_exists('is_subcontractor', $staffTable)) {
                $staffUpdates['is_subcontractor'] = 1;
            }
            if ($this->db->field_exists('smartsource_subcontractor_id', $staffTable)) {
                $staffUpdates['smartsource_subcontractor_id'] = $subcontractor_id;
            }
            if (!empty($staffUpdates)) {
                $this->db->where('staffid', $staffId)->update($staffTable, $staffUpdates);
            }
            $this->db->where('id', $subcontractor_id)->update(db_prefix() . 'smartsource_subcontractors', ['staff_id' => $staffId]);
            log_activity('Subcontractor linked to staff account [Subcontractor ID: ' . $subcontractor_id . ', Staff ID: ' . $staffId . ']');
        }
        return $staffId;
    }

    private function get_subcontractor_role_id()
    {
        $rolesTable = db_prefix() . 'roles';
        if (!$this->db->table_exists($rolesTable)) {
            return 0;
        }
        $role = $this->db->where('LOWER(name)', 'sub-contractors')->get($rolesTable)->row();
        if (!$role) {
            log_activity('Subcontractor staff provisioning requires the existing role named Sub-Contractors. No role was created automatically.');
            return 0;
        }
        return (int) $role->roleid;
    }

    private function prepare_subcontractor_data($data)
    {
        $allowed = ['company', 'contact_name', 'email', 'phone', 'trade', 'license_number', 'dbpr_link', 'county_license_link', 'profile_image', 'portal_enabled', 'portal_token', 'staff_id', 'insurance_expiration', 'address', 'city', 'state', 'zip', 'status', 'category', 'notes', 'extra_answers', 'assigned'];
        $clean = $this->only_allowed($data, $allowed);
        if (function_exists('smartsource_normalize_url')) {
            $clean['dbpr_link'] = smartsource_normalize_url($clean['dbpr_link'] ?? '');
            $clean['county_license_link'] = smartsource_normalize_url($clean['county_license_link'] ?? '');
        }
        $clean['portal_enabled'] = !empty($clean['portal_enabled']) ? 1 : 0;
        return $clean;
    }


    public function ensure_contract_public_token($id)
    {
        $row = $this->db->select('id, public_token')->where('id', (int)$id)->get(db_prefix() . 'smartsource_subcontractor_contracts')->row();
        if (!$row) { return ''; }
        if (!empty($row->public_token)) { return $row->public_token; }
        $token = bin2hex(random_bytes(24));
        $this->db->where('id', (int)$id)->update(db_prefix() . 'smartsource_subcontractor_contracts', ['public_token' => $token]);
        return $token;
    }

    public function get_contract_by_token($token)
    {
        if ($token === '') { return null; }
        $this->db->select('c.*, s.company as subcontractor_company, s.contact_name, s.email, s.phone, p.name as project_name');
        $this->db->from(db_prefix() . 'smartsource_subcontractor_contracts c');
        $this->db->join(db_prefix() . 'smartsource_subcontractors s', 's.id = c.subcontractor_id', 'left');
        $this->db->join(db_prefix() . 'projects p', 'p.id = c.project_id', 'left');
        $this->db->where('c.public_token', $token);
        return $this->db->get()->row();
    }

    public function render_contract_cover($contract)
    {
        $cover = '';
        if (!empty($contract->template_id)) {
            $template = $this->get_templates($contract->template_id);
            if ($template && !empty($template->cover_html)) {
                $cover = $template->cover_html;
            }
        }
        if ($cover === '') {
            $logo = function_exists('get_option') && get_option('company_logo') ? base_url('uploads/company/' . get_option('company_logo')) : '';
            $cover = '<div class="ssc-cover-page compact-cover"><div class="ssc-cover-card">'
                . ($logo !== '' ? '<img class="ssc-cover-logo" src="' . html_escape($logo) . '" alt="Smart Choice Contractors USA">' : '')
                . '<h1>Smart Choice Contractors USA</h1><h2>Subcontractor Contract</h2><p class="ssc-cover-subject">{contract_subject}</p><p>Subcontractor: {subcontractor_name}</p><p>Project: {project_name}</p><p>Contract Value: ${contract_value}</p></div></div>';
        }
        $fake = clone $contract;
        $fake->content = $cover;
        return $this->render_contract_content($fake);
    }

    public function add_contract_comment($contract_id, $comment, $is_internal = 1, $customer_name = '', $customer_email = '')
    {
        $data = [
            'contract_id' => (int)$contract_id,
            'staffid' => function_exists('get_staff_user_id') ? get_staff_user_id() : 0,
            'comment' => $comment,
            'dateadded' => date('Y-m-d H:i:s'),
        ];
        $fields = $this->db->list_fields(db_prefix() . 'smartsource_subcontractor_contract_comments');
        if (in_array('is_internal', $fields, true)) { $data['is_internal'] = (int)$is_internal; }
        if (in_array('customer_name', $fields, true)) { $data['customer_name'] = $customer_name; }
        if (in_array('customer_email', $fields, true)) { $data['customer_email'] = $customer_email; }
        $this->db->insert(db_prefix() . 'smartsource_subcontractor_contract_comments', $data);
        return $this->db->insert_id();
    }

    public function get_contract_comments($contract_id, $include_internal = true)
    {
        if (!$this->db->table_exists(db_prefix() . 'smartsource_subcontractor_contract_comments')) { return []; }
        $fields = $this->db->list_fields(db_prefix() . 'smartsource_subcontractor_contract_comments');
        $this->db->select('cc.*, s.firstname, s.lastname');
        $this->db->from(db_prefix() . 'smartsource_subcontractor_contract_comments cc');
        $this->db->join(db_prefix() . 'staff s', 's.staffid = cc.staffid', 'left');
        $this->db->where('cc.contract_id', (int)$contract_id);
        if (!$include_internal && in_array('is_internal', $fields, true)) {
            $this->db->where('cc.is_internal', 0);
        }
        $this->db->order_by('cc.dateadded', 'DESC');
        return $this->db->get()->result_array();
    }

    public function mark_contract_emailed($id)
    {
        if (in_array('email_last_sent_at', $this->db->list_fields(db_prefix() . 'smartsource_subcontractor_contracts'), true)) {
            $this->db->where('id', (int)$id)->update(db_prefix() . 'smartsource_subcontractor_contracts', ['email_last_sent_at' => date('Y-m-d H:i:s')]);
        }
    }

    public function mark_contract_xml_generated($id)
    {
        if (in_array('xml_last_generated_at', $this->db->list_fields(db_prefix() . 'smartsource_subcontractor_contracts'), true)) {
            $this->db->where('id', (int)$id)->update(db_prefix() . 'smartsource_subcontractor_contracts', ['xml_last_generated_at' => date('Y-m-d H:i:s')]);
        }
    }

    public function contract_to_xml($contract)
    {
        $xml = new SimpleXMLElement('<SubcontractorContract/>');
        $xml->addChild('ContractId', (string)$contract->id);
        $xml->addChild('Subject', htmlspecialchars((string)$contract->subject));
        $xml->addChild('Status', htmlspecialchars((string)$contract->status));
        $xml->addChild('Subcontractor', htmlspecialchars((string)($contract->subcontractor_company ?? '')));
        $xml->addChild('Email', htmlspecialchars((string)($contract->email ?? '')));
        $xml->addChild('Phone', htmlspecialchars((string)($contract->phone ?? '')));
        $xml->addChild('Project', htmlspecialchars((string)($contract->project_name ?? '')));
        $xml->addChild('ContractType', htmlspecialchars((string)$contract->contract_type));
        $xml->addChild('ContractValue', (string)$contract->contract_value);
        $xml->addChild('StartDate', (string)$contract->start_date);
        $xml->addChild('EndDate', (string)$contract->end_date);
        $xml->addChild('SignedDate', (string)$contract->signed_date);
        $xml->addChild('PublicUrl', site_url('subcontractors/subcontractor_contract/index/' . rawurlencode($this->ensure_contract_public_token($contract->id))));
        return $xml->asXML();
    }


    public function update_contract_status($id, $status, $extra = [])
    {
        $data = array_merge(['status' => $status, 'dateupdated' => date('Y-m-d H:i:s')], is_array($extra) ? $extra : []);
        $this->db->where('id', (int) $id)->update(db_prefix() . 'smartsource_subcontractor_contracts', $data);
        log_activity('Smartsource Subcontractor Contract Status Updated [ID: ' . (int) $id . ', Status: ' . $status . ']');
        return true;
    }

    public function copy_contract($id)
    {
        $contract = $this->get_contract((int) $id);
        if (!$contract) {
            return false;
        }
        $data = [];
        foreach (['subject','subcontractor_id','project_id','clientid','contract_type','template_id','contract_value','start_date','end_date','description','content','hidden_from_customer','assigned'] as $field) {
            if (isset($contract->{$field})) {
                $data[$field] = $contract->{$field};
            }
        }
        $data['subject'] = trim((string) $data['subject']) . ' Copy';
        $data['status'] = 'draft';
        $data['signed_date'] = null;
        $data['company_initials'] = '';
        $data['subcontractor_initials'] = '';
        $data['company_signature'] = '';
        $data['subcontractor_signature'] = '';
        return $this->add_contract($data);
    }

    private function prepare_contract_data($data)
    {
        $allowed = ['subject', 'subcontractor_id', 'project_id', 'clientid', 'contract_type', 'template_id', 'contract_value', 'start_date', 'end_date', 'signed_date', 'status', 'description', 'content', 'hidden_from_customer', 'is_trash', 'assigned', 'company_initials', 'subcontractor_initials', 'company_signature', 'subcontractor_signature'];
        $clean = $this->only_allowed($data, $allowed);
        $clean['hidden_from_customer'] = !empty($data['hidden_from_customer']) ? 1 : 0;
        $clean['is_trash'] = !empty($data['is_trash']) ? 1 : 0;
        return $clean;
    }

    private function handle_custom_fields_safely($id, $relType)
    {
        if (!function_exists('handle_custom_fields_post')) {
            return;
        }

        $post = $this->input->post();
        if (!isset($post['custom_fields']) || !is_array($post['custom_fields'])) {
            return;
        }

        $customFields = $post['custom_fields'];
        if (!isset($customFields[$relType]) || !is_array($customFields[$relType])) {
            return;
        }

        handle_custom_fields_post($id, [$relType => $customFields[$relType]]);
    }

    private function only_allowed($data, $allowed)
    {
        $clean = [];
        foreach ($allowed as $key) {
            if (isset($data[$key])) {
                $clean[$key] = $data[$key];
            }
        }
        return $clean;
    }
}
