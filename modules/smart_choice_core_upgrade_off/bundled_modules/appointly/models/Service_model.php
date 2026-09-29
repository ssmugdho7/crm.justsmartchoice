<?php defined('BASEPATH') or exit('No direct script access allowed');

class Service_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get service by ID
     */
    public function get($id = '')
    {
        if ($id) {
            $this->db->where('id', $id);
            $service = $this->db->get(db_prefix() . 'appointly_services')->row();

            if ($service) {
                // Get staff members from service_staff table
                $this->db->select('ss.staff_id, ss.is_primary, s.firstname, s.lastname, s.email');
                $this->db->from(db_prefix() . 'appointly_service_staff ss');
                $this->db->join(db_prefix() . 'staff s', 's.staffid = ss.staff_id', 'left');
                $this->db->where('ss.service_id', $id);
                $this->db->where('ss.is_provider', 1);
                $staff_query = $this->db->get();

                $staff_members = [];
                $primary_provider = null;

                if ($staff_query && $staff_query->num_rows() > 0) {
                    foreach ($staff_query->result_array() as $staff) {
                        $staff_members[] = [
                            'staff_id' => $staff['staff_id'],
                            'is_primary' => $staff['is_primary'],
                            'full_name' => $staff['firstname'] . ' ' . $staff['lastname'],
                            'email' => $staff['email']
                        ];

                        if ($staff['is_primary']) {
                            $primary_provider = $staff['staff_id'];
                        }
                    }
                }

                $service->staff_members = $staff_members;
                $service->primary_provider = $primary_provider;
            }

            return $service;
        }

        return $this->db->get(db_prefix() . 'appointly_services')->result_array();
    }

    /**
     * Get all active services
     */
    public function get_active_services()
    {
        $this->db->where('active', 1);
        return $this->db->get(db_prefix() . 'appointly_services')->result_array();
    }

    /**
     * Add new service
     */
    public function add($data)
    {
        // Extract providers data
        $providers = [];
        if (isset($data['providers'])) {
            $providers = $data['providers'];
            unset($data['providers']);
        }

        // Extract primary provider
        $primary_provider = isset($data['primary_provider']) ? $data['primary_provider'] : null;
        unset($data['primary_provider']);

        // Set default status if not provided
        if (!isset($data['active'])) {
            $data['active'] = 1;
        }

        // Ensure only valid database fields are included
        $valid_fields = ['name', 'description', 'duration', 'price', 'color', 'active', 'buffer_before', 'buffer_after'];
        $filtered_data = [];
        foreach ($valid_fields as $field) {
            if (isset($data[$field])) {
                $filtered_data[$field] = $data[$field];
            }
        }

        // Insert service record
        $this->db->insert(db_prefix() . 'appointly_services', $filtered_data);
        $insert_id = $this->db->insert_id();

        if ($insert_id) {
            // Update service providers
            if (!empty($providers)) {
                $this->update_service_providers($insert_id, $providers, $primary_provider);
            }

            return $insert_id;
        }

        return false;
    }

    /**
     * Update service
     */
    public function update($id, $data)
    {
        // Extract providers data
        $providers = [];
        if (isset($data['providers'])) {
            $providers = $data['providers'];
            unset($data['providers']);
        }

        // Extract primary provider
        $primary_provider = isset($data['primary_provider']) ? $data['primary_provider'] : null;
        unset($data['primary_provider']);

        // Ensure only valid database fields are included
        $valid_fields = ['name', 'description', 'duration', 'price', 'color', 'active', 'buffer_before', 'buffer_after'];
        $filtered_data = [];
        foreach ($valid_fields as $field) {
            if (isset($data[$field])) {
                $filtered_data[$field] = $data[$field];
            }
        }

        $this->db->where('id', $id);
        $result = $this->db->update(db_prefix() . 'appointly_services', $filtered_data);

        // Update service providers
        if (!empty($providers)) {
            $this->update_service_providers($id, $providers, $primary_provider);
        }

        if ($result) {
            log_message('info', 'Service Updated [ID: ' . $id . ']');
            return true;
        }

        return false;
    }

    /**
     * Update service providers
     */
    public function update_service_providers($service_id, $providers, $primary_provider = null)
    {
        // Clear existing assignments
        $this->db->where('service_id', $service_id);
        $this->db->delete(db_prefix() . 'appointly_service_staff');

        // Add new assignments
        if (!empty($providers)) {
            // Ensure providers is an array
            if (!is_array($providers)) {
                $providers = [$providers];
            }

            $staff_data = [];
            foreach ($providers as $staff_id) {
                $is_primary = ($primary_provider && $staff_id == $primary_provider) ? 1 : 0;

                $staff_data[] = [
                    'service_id' => $service_id,
                    'staff_id' => $staff_id,
                    'is_provider' => 1,
                    'is_primary' => $is_primary
                ];
            }

            if (!empty($staff_data)) {
                $this->db->insert_batch(db_prefix() . 'appointly_service_staff', $staff_data);
            }
        }

        return true;
    }

    /**
     * Delete service
     */
    public function delete($id)
    {
        // Check if service is in use
        if ($this->is_service_in_use($id)) {
            return [
                'success' => false,
                'message' => _l('service_delete_error_active')
            ];
        }

        // Delete service staff assignments
        $this->db->where('service_id', $id);
        $this->db->delete(db_prefix() . 'appointly_service_staff');

        // Delete service appointment connections
        $this->db->where('service_id', $id);
        $this->db->delete(db_prefix() . 'appointly_appointment_services');

        // Delete service
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'appointly_services');

        if ($this->db->affected_rows() > 0) {
            log_message('info', 'Service Deleted [ID: ' . $id . ']');
            return [
                'success' => true,
                'message' => _l('service_deleted_success')
            ];
        }

        return [
            'success' => false,
            'message' => _l('service_delete_error')
        ];
    }

    /**
     * Change service status
     */
    public function change_status($id, $status)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'appointly_services', ['active' => $status]);

        return $this->db->affected_rows() > 0;
    }

    /**
     * Check if service is in use
     */
    public function is_service_in_use($service_id)
    {
        $this->db->select('a.id');
        $this->db->from(db_prefix() . 'appointly_appointments a');
        $this->db->join(db_prefix() . 'appointly_appointment_services aps', 'a.id = aps.appointment_id', 'inner');
        $this->db->where('aps.service_id', $service_id);
        $this->db->where('a.status !=', 'cancelled');
        $this->db->where('a.status !=', 'completed');

        return $this->db->get()->num_rows() > 0;
    }

    /**
     * Check if staff member is assigned as a provider for a service
     * @param int $service_id
     * @param int $staff_id
     * @return bool
     */
    public function is_provider_assigned($service_id, $staff_id)
    {
        $this->db->where('service_id', $service_id);
        $this->db->where('staff_id', $staff_id);
        $this->db->where('is_provider', 1);

        return $this->db->get(db_prefix() . 'appointly_service_staff')->num_rows() > 0;
    }

    /**
     * Get service name
     * @param int $service_id
     * @return string
     */
    public function get_service_name($service_id)
    {
        $this->db->where('id', $service_id);
        $this->db->where('active', 1);
        return $this->db->get(db_prefix() . 'appointly_services')->row()->name;
    }
}
