<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Roles_model extends App_Model
{
    /**
     * Add new employee role
     * @param mixed $data
     */
    public function add($data)
    {
        unset($data['permissions_submitted']);
        $permissions = [];
        if (isset($data['permissions'])) {
            $permissions = $data['permissions'];
        }

        $permissions = normalize_staff_permission_input($permissions);
        if ($permissions === null) { return false; }
        $data['permissions'] = serialize($permissions);

        $this->db->insert(db_prefix() . 'roles', $data);
        $insert_id = $this->db->insert_id();

        if ($insert_id) {
            log_activity('New Role Added [ID: ' . $insert_id . '.' . $data['name'] . ']');

            return $insert_id;
        }

        return false;
    }

    /**
     * Update employee role
     * @param  array $data role data
     * @param  mixed $id   role id
     * @return boolean
     */
    public function update($data, $id)
    {
        $existing = $this->get($id);
        if (!$existing) { return false; }
        $affectedRows = 0;
        $submitted = array_key_exists('permissions', $data) || !empty($data['permissions_submitted']);
        unset($data['permissions_submitted']);
        $permissions = $submitted ? [] : $existing->permissions;
        if (isset($data['permissions'])) {
            $permissions = $data['permissions'];
        }

        $permissions = normalize_staff_permission_input($permissions);
        if ($permissions === null) { return false; }
        $data['permissions'] = serialize($permissions);

        $update_staff_permissions = false;
        if (isset($data['update_staff_permissions'])) {
            $update_staff_permissions = in_array($data['update_staff_permissions'], ['on', '1', 1, true], true);
            unset($data['update_staff_permissions']);
        }

        $debug = $this->db->db_debug;
        $this->db->db_debug = false;
        $staff = [];
        $this->db->trans_start();
        try {
            $this->db->where('roleid', $id);
            $this->db->update(db_prefix() . 'roles', $data);

            if ($this->db->affected_rows() > 0) {
                $affectedRows++;
            }

            $staff = [];
            if ($update_staff_permissions == true) {
                $this->load->model('staff_model');

                $staff = $this->staff_model->get('', [
                    'role' => $id,
                ]);

                usort($staff, static function ($a, $b) { return (int) $a['staffid'] <=> (int) $b['staffid']; });
                foreach ($staff as $member) {
                    if ($this->staff_model->update_permissions($permissions, $member['staffid'])) {
                        $affectedRows++;
                    } else {
                        $this->db->trans_rollback();
                        $this->db->db_debug = $debug;
                        foreach ($staff as $affected) { $this->staff_model->clear_permission_cache($affected['staffid']); }
                        $this->app_object_cache->delete('role-' . $id);
                        return false;
                    }
                }
            }

            $success = $this->db->trans_complete();
        } catch (Throwable $e) {
            $this->db->trans_rollback();
            $success = false;
            log_message('error', 'Role permission application failed; transaction rolled back.');
        }
        $this->db->db_debug = $debug;
        $this->app_object_cache->delete('role-' . $id);
        foreach ($staff as $affected) { $this->staff_model->clear_permission_cache($affected['staffid']); }
        if (!$success) { return false; }

        if ($affectedRows > 0) {
            log_activity('Role Updated [ID: ' . $id . ', Name: ' . $data['name'] . ']');

            return true;
        }

        return false;
    }

    /**
     * Get employee role by id
     * @param  mixed $id Optional role id
     * @return mixed     array if not id passed else object
     */
    public function get($id = '')
    {
        if ($id !== '' && (!is_scalar($id) || !ctype_digit((string) $id) || (int) $id <= 0)) { return null; }
        if (is_numeric($id)) {

            $role = $this->app_object_cache->get('role-' . $id);

            if ($role) {
                return $role;
            }

            $this->db->where('roleid', $id);

            $role              = $this->db->get(db_prefix() . 'roles')->row();
            if (!$role) { return null; }
            $decoded = !empty($role->permissions) ? @unserialize($role->permissions, ['allowed_classes' => false]) : [];
            $role->permissions = normalize_staff_permission_input($decoded) ?? [];

            $this->app_object_cache->add('role-' . $id, $role);

            return $role;
        }

        return $this->db->get(db_prefix() . 'roles')->result_array();
    }

    /**
     * Delete employee role
     * @param  mixed $id role id
     * @return mixed
     */
    public function delete($id)
    {
        $current = $this->get($id);

        // Check first if role is used in table
        if (is_reference_in_table('role', db_prefix() . 'staff', $id)) {
            return [
                'referenced' => true,
            ];
        }

        $affectedRows = 0;
        $this->db->where('roleid', $id);
        $this->db->delete(db_prefix() . 'roles');

        if ($this->db->affected_rows() > 0) {
            $affectedRows++;
        }

        if ($affectedRows > 0) {
            $this->app_object_cache->delete('role-' . $id);
            log_activity('Role Deleted [ID: ' . $id);

            return true;
        }

        return false;
    }

    public function get_contact_permissions($id)
    {
        $this->db->where('userid', $id);

        return $this->db->get(db_prefix() . 'contact_permissions')->result_array();
    }

    public function get_role_staff($role_id)
    {
        $this->db->where('role', $role_id);

        return $this->db->get(db_prefix() . 'staff')->result_array();
    }
}
