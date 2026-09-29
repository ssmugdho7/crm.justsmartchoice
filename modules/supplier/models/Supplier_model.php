<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Supplier_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->ensure_schema();
    }

    public function ensure_schema()
    {
        if (!$this->db->table_exists(db_prefix() . 'suppliers')) {
            $this->db->query('CREATE TABLE `' . db_prefix() . "suppliers` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `supplier_name` VARCHAR(191) NOT NULL,
                `website` VARCHAR(255) NULL,
                `phone` VARCHAR(80) NULL,
                `email` VARCHAR(191) NULL,
                `trade` VARCHAR(120) NULL,
                `supplier_type` VARCHAR(120) NULL,
                `registration_url` VARCHAR(255) NULL,
                `short_description` VARCHAR(255) NULL,
                `notes` TEXT NULL,
                `location_id` INT(11) DEFAULT 0,
                `only_me` TINYINT(1) DEFAULT 0,
                `staff_id` INT(11) DEFAULT 0,
                `created_date` DATETIME NULL,
                PRIMARY KEY (`id`),
                KEY `location_id` (`location_id`),
                KEY `staff_id` (`staff_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=" . $this->db->char_set . ';');
        }

        if (!$this->db->table_exists(db_prefix() . 'supplier_locations')) {
            $this->db->query('CREATE TABLE `' . db_prefix() . "supplier_locations` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `location_name` VARCHAR(191) NOT NULL,
                `description` TEXT NULL,
                `ordering` INT(11) DEFAULT 0,
                `staff_id` INT(11) DEFAULT 0,
                `created_date` DATETIME NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=" . $this->db->char_set . ';');
        }

        if (!$this->db->table_exists(db_prefix() . 'supplier_documents')) {
            $this->db->query('CREATE TABLE `' . db_prefix() . "supplier_documents` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `supplier_id` INT(11) NOT NULL,
                `file_name` VARCHAR(191) NOT NULL,
                `original_name` VARCHAR(191) NOT NULL,
                `file_path` VARCHAR(255) NOT NULL,
                `file_type` VARCHAR(120) NULL,
                `file_size` INT(11) DEFAULT 0,
                `description` VARCHAR(255) NULL,
                `uploaded_by` INT(11) DEFAULT 0,
                `dateadded` DATETIME NULL,
                PRIMARY KEY (`id`),
                KEY `supplier_id` (`supplier_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=" . $this->db->char_set . ';');
        }

        $this->ensure_supplier_columns();
    }

    private function ensure_supplier_columns()
    {
        if (!$this->db->table_exists(db_prefix() . 'suppliers')) {
            return;
        }

        $columns = [
            'website' => 'VARCHAR(255) NULL',
            'phone' => 'VARCHAR(80) NULL',
            'email' => 'VARCHAR(191) NULL',
            'trade' => 'VARCHAR(120) NULL',
            'supplier_type' => 'VARCHAR(120) NULL',
            'registration_url' => 'VARCHAR(255) NULL',
            'short_description' => 'VARCHAR(255) NULL',
            'notes' => 'TEXT NULL',
            'location_id' => 'INT(11) DEFAULT 0',
            'only_me' => 'TINYINT(1) DEFAULT 0',
            'staff_id' => 'INT(11) DEFAULT 0',
            'created_date' => 'DATETIME NULL',
        ];

        foreach ($columns as $column => $definition) {
            if (!$this->db->field_exists($column, db_prefix() . 'suppliers')) {
                $this->db->query('ALTER TABLE `' . db_prefix() . 'suppliers` ADD `' . $column . '` ' . $definition);
            }
        }
    }

    public function normalize_url($url)
    {
        $url = trim((string)$url);
        if ($url === '') {
            return '';
        }
        if (!preg_match('~^https?://~i', $url)) {
            $url = 'https://' . $url;
        }
        return $url;
    }

    public function clean_phone($phone)
    {
        return trim((string)$phone);
    }

    public function get($id = '')
    {
        if (is_numeric($id)) {
            return $this->db->where('id', (int)$id)->get(db_prefix() . 'suppliers')->row();
        }
        return $this->db->order_by('supplier_name', 'asc')->get(db_prefix() . 'suppliers')->result_array();
    }

    private function clean_data($data)
    {
        unset($data['id'], $data['tags'], $data['supplier_document_description']);
        $allowed = ['supplier_name', 'website', 'phone', 'email', 'trade', 'supplier_type', 'registration_url', 'short_description', 'notes', 'location_id', 'only_me'];
        $clean = [];
        foreach ($allowed as $field) {
            if (isset($data[$field])) {
                $clean[$field] = $data[$field];
            }
        }
        $clean['website'] = $this->normalize_url($clean['website'] ?? '');
        $clean['registration_url'] = $this->normalize_url($clean['registration_url'] ?? '');
        $clean['phone'] = $this->clean_phone($clean['phone'] ?? '');
        $clean['only_me'] = isset($clean['only_me']) ? (int)$clean['only_me'] : 0;
        $clean['location_id'] = isset($clean['location_id']) ? (int)$clean['location_id'] : 0;
        $clean['short_description'] = mb_substr(trim(strip_tags((string)($clean['short_description'] ?? ''))), 0, 220);
        return $clean;
    }

    public function add($data)
    {
        $tags = $data['tags'] ?? '';
        $clean = $this->clean_data($data);
        $clean['staff_id'] = get_staff_user_id();
        $clean['created_date'] = date('Y-m-d H:i:s');
        $this->db->insert(db_prefix() . 'suppliers', $clean);
        $id = $this->db->insert_id();
        if ($id) {
            handle_tags_save($tags, $id, 'supplier');
        }
        return $id ?: false;
    }

    public function update($data, $id)
    {
        $tags = $data['tags'] ?? '';
        $clean = $this->clean_data($data);
        $this->db->where('id', (int)$id)->update(db_prefix() . 'suppliers', $clean);
        handle_tags_save($tags, (int)$id, 'supplier');
        return $this->db->affected_rows() >= 0;
    }

    public function delete($id)
    {
        $this->db->where('id', (int)$id)->delete(db_prefix() . 'suppliers');
        return $this->db->affected_rows() > 0;
    }

    public function get_locations($id = '')
    {
        if (is_numeric($id)) {
            return $this->db->where('id', (int)$id)->get(db_prefix() . 'supplier_locations')->row();
        }
        return $this->db->order_by('ordering', 'asc')->get(db_prefix() . 'supplier_locations')->result_array();
    }

    public function add_location($data)
    {
        $data['staff_id'] = get_staff_user_id();
        $data['created_date'] = date('Y-m-d H:i:s');
        $this->db->insert(db_prefix() . 'supplier_locations', $data);
        return $this->db->insert_id() ?: false;
    }

    public function update_location($data, $id)
    {
        $this->db->where('id', (int)$id)->update(db_prefix() . 'supplier_locations', $data);
        return $this->db->affected_rows() >= 0;
    }

    public function delete_location($id)
    {
        $this->db->where('id', (int)$id)->delete(db_prefix() . 'supplier_locations');
        return $this->db->affected_rows() > 0;
    }

    public function get_trades()
    {
        return ['General Supplier', 'Building Materials', 'Electrical', 'Plumbing', 'HVAC', 'Roofing', 'Painting', 'Drywall', 'Framing', 'Concrete', 'Flooring', 'Tile', 'Cabinets', 'Windows And Doors', 'Insulation', 'Landscaping', 'Fencing', 'Engineering', 'Permit Service', 'Equipment Rental', 'Tools', 'Other'];
    }

    public function get_documents($supplier_id)
    {
        return $this->db->where('supplier_id', (int)$supplier_id)->order_by('dateadded', 'desc')->get(db_prefix() . 'supplier_documents')->result_array();
    }

    public function folder_name($name)
    {
        $name = preg_replace('/[^A-Za-z0-9\-_ ]/', '', (string)$name);
        $name = trim(preg_replace('/\s+/', '_', $name));
        return $name !== '' ? $name : 'Supplier_' . time();
    }

    public function add_document($supplier_id, $file, $description = '')
    {
        $supplier = $this->get($supplier_id);
        if (!$supplier || empty($file['name'])) {
            return false;
        }
        $folder = FCPATH . 'uploads/suppliers/' . $this->folder_name($supplier->supplier_name) . '/';
        if (!is_dir($folder)) {
            @mkdir($folder, 0755, true);
        }
        $original = $file['name'];
        $ext = strtolower(pathinfo($original, PATHINFO_EXTENSION));
        $safe = preg_replace('/[^A-Za-z0-9\-_]/', '_', pathinfo($original, PATHINFO_FILENAME));
        $name = $safe . '_' . date('Ymd_His') . ($ext ? '.' . $ext : '');
        $target = $folder . $name;
        if (!@move_uploaded_file($file['tmp_name'], $target)) {
            return false;
        }
        $rel = 'uploads/suppliers/' . $this->folder_name($supplier->supplier_name) . '/' . $name;
        $this->db->insert(db_prefix() . 'supplier_documents', [
            'supplier_id'   => (int)$supplier_id,
            'file_name'     => $name,
            'original_name' => $original,
            'file_path'     => $rel,
            'file_type'     => $file['type'] ?? '',
            'file_size'     => (int)($file['size'] ?? 0),
            'description'   => $description,
            'uploaded_by'   => get_staff_user_id(),
            'dateadded'     => date('Y-m-d H:i:s'),
        ]);
        return $this->db->insert_id();
    }


    public function update_email($id, $email)
    {
        $email = trim((string)$email);
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }
        $this->db->where('id', (int)$id)->update(db_prefix() . 'suppliers', ['email' => $email]);
        return $this->db->affected_rows() >= 0;
    }

    public function bulk_delete($ids)
    {
        $ids = array_filter(array_map('intval', (array)$ids));
        if (!$ids) {
            return 0;
        }
        $this->db->where_in('id', $ids)->delete(db_prefix() . 'suppliers');
        return $this->db->affected_rows();
    }

    public function export_rows($ids = [])
    {
        $ids = array_filter(array_map('intval', (array)$ids));
        if ($ids) {
            $this->db->where_in('id', $ids);
        }
        return $this->db->order_by('supplier_name', 'asc')->get(db_prefix() . 'suppliers')->result_array();
    }

    public function import_supplier_record($data)
    {
        $clean = $this->clean_data($data);
        $name = trim((string)($clean['supplier_name'] ?? ''));
        if ($name === '') {
            return false;
        }

        $website = $this->normalize_url($clean['website'] ?? '');
        $this->db->group_start();
        $this->db->where('LOWER(supplier_name)', strtolower($name));
        if ($website !== '') {
            $this->db->or_where('website', $website);
        }
        $this->db->group_end();
        $existing = $this->db->get(db_prefix() . 'suppliers')->row();

        if ($existing) {
            $this->db->where('id', (int)$existing->id)->update(db_prefix() . 'suppliers', $clean);
            return (int)$existing->id;
        }

        return $this->add($clean);
    }

    public function count_suppliers()
    {
        return (int)$this->db->count_all(db_prefix() . 'suppliers');
    }

}
