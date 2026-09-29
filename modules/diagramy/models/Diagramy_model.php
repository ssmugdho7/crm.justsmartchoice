<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Diagramy_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get_data_by_rel_id($table, $where)
    {
        $query=$this->db->get_where(db_prefix().$table, $where);

        return $query->result_array();
    }

    public function get_staff_counts($staffid)
    {
        $count = 0;

        $sql = 'SELECT count(`staffid`) as total_count
                from '.db_prefix()."diagramy where staffid= '".$staffid."' ";
        $query = $this->db->query($sql);
        $row   = $query->row();
        if (isset($row)) {
            $count = $row->total_count;
        }

        return $count;
    }

    /**
     * Get groups.
     *
     * @param mixed $id group id (Optional)
     *
     * @return mixed object or array
     */
    public function get_groups($id = '')
    {
        if (is_numeric($id)) {
            $this->db->where('id', $id);

            return $this->db->get(db_prefix().'diagramy_groups')->row();
        }
        $this->db->order_by('name', 'asc');

        return $this->db->get(db_prefix().'diagramy_groups')->result_array();
    }

    /**
     * Add new group.
     *
     * @param mixed $data All $_POST data
     *
     * @return bool
     */
    public function add_group($data)
    {
        $data['description'] = nl2br($data['description']);
        $this->db->insert(db_prefix().'diagramy_groups', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            log_activity('diagramy Group Added [ID: '.$insert_id.']');

            return $insert_id;
        }

        return false;
    }

    /**
     * Update group.
     *
     * @param mixed $data All $_POST data
     * @param mixed $id   group id to update
     *
     * @return bool
     */
    public function update_group($data, $id)
    {
        $data['description'] = nl2br($data['description']);
        $this->db->where('id', $id);
        $this->db->update(db_prefix().'diagramy_groups', $data);
        if ($this->db->affected_rows() > 0) {
            log_activity('diagramy Group Updated [ID: '.$id.']');

            return true;
        }

        return false;
    }

    /**
     * @param  int ID
     * @param mixed $id
     *
     * @return mixed
     *               Delete type from database, if used return array with key referenced
     */
    public function delete_group($id)
    {
        if (is_reference_in_table('diagramy_group_id', db_prefix().'diagramy', $id)) {
            return [
                'referenced' => true,
            ];
        }
        $this->db->where('id', $id);
        $this->db->delete(db_prefix().'diagramy_groups');
        if ($this->db->affected_rows() > 0) {
            log_activity('Group Deleted ['.$id.']');

            return true;
        }

        return false;
    }

    /**
     * @param  int (optional)
     * @param mixed $id
     *
     * @return object
     *                Get single
     */
    public function get($id = '')
    {
        if (is_numeric($id)) {
            $this->db->where('id', $id);

            return $this->db->get(db_prefix().'diagramy')->row();
        }

        return $this->db->get(db_prefix().'diagramy')->result_array();
    }

    /**
     * Normalize diagram data before saving.
     * Keeps diagrams.net XML and PNG payloads intact under PHP 8.x/8.5.
     * The original module stored the whole XML-PNG in a TEXT field, which can truncate
     * larger diagrams and make sketches/colors/shapes disappear after save.
     */
    private function prepare_diagram_data($data)
    {
        if (!is_array($data)) {
            $data = [];
        }

        if (isset($data['diagramy_content'])) {
            $data['diagramy_content'] = str_replace('[removed]', 'data:image/png;base64,', (string) $data['diagramy_content']);
        } else {
            $data['diagramy_content'] = '';
        }

        if (isset($data['diagramy_xml'])) {
            $data['diagramy_xml'] = (string) $data['diagramy_xml'];
        }

        $data['staffid'] = empty($data['staffid']) ? 0 : (int) $data['staffid'];
        $data['diagramy_group_id'] = empty($data['diagramy_group_id']) ? 0 : (int) $data['diagramy_group_id'];
        $data['rel_id'] = empty($data['rel_id']) ? 0 : (int) $data['rel_id'];
        $data['related_to'] = isset($data['related_to']) ? (string) $data['related_to'] : '';
        $data['title'] = isset($data['title']) ? trim((string) $data['title']) : '';
        $data['description'] = isset($data['description']) ? (string) $data['description'] : '';
        $data['diagramy_slug'] = url_title($data['title'], '-', true) . '-' . time();

        return $data;
    }

    /**
     * Add new diagram.
     *
     * @param mixed $data All $_POST data
     *
     * @return mixed
     */
    public function add($data)
    {
        $data = $this->prepare_diagram_data($data);
        $data['dateadded'] = date('Y-m-d H:i:s');
        $this->db->insert(db_prefix().'diagramy', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            log_activity('Diagramy Added [ID:'.$insert_id.']');

            return $insert_id;
        }

        return false;
    }

    /**
     * Update diagram.
     *
     * @param mixed $data All $_POST data
     * @param mixed $id   id
     *
     * @return bool
     */
    public function update($data, $id)
    {
        $data = $this->prepare_diagram_data($data);
        $data['dateaupdated'] = date('Y-m-d H:i:s');

        // Preserve original slug on update so public links do not break.
        unset($data['diagramy_slug']);

        $this->db->where('id', $id);
        $this->db->update(db_prefix().'diagramy', $data);
        if ($this->db->affected_rows() >= 0) {
            log_activity('Diagramy Updated [ID:'.$id.']');

            return true;
        }

        return false;
    }

    /**
     * Delete.
     *
     * @param mixed $id id
     *
     * @return bool
     */
    public function delete($id)
    {
        $this->db->where('id', $id);
        $this->db->delete(db_prefix().'diagramy');
        if ($this->db->affected_rows() > 0) {
            log_activity('diagramy Deleted [ID:'.$id.']');

            return true;
        }

        return false;
    }

    public function get_diagramy_data_byslug($slug)
    {
        $this->db->where('diagramy_slug', $slug);

        return $result = $this->db->get(db_prefix().'diagramy')->row();
    }

    public function get_all_projects()
    {
        $p = db_prefix();
        $this->db->select($p.'diagramy.title,'.$p.'diagramy.description,'.$p.'diagramy_groups.name,'.$p.'diagramy.dateadded,'.$p.'diagramy.diagramy_slug,'.$p.'diagramy.diagramy_group_id,'.$p.'staff.firstname,'.$p.'staff.lastname,'.$p.'diagramy.staffid,'.$p.'diagramy.id,'.$p.'diagramy.diagramy_content');

        $this->db->from($p.'diagramy');
        $this->db->join($p.'diagramy_groups', $p.'diagramy.diagramy_group_id = '.$p.'diagramy_groups.id', 'left');
        $this->db->join($p.'staff', $p.'diagramy.staffid='.$p.'staff.staffid', 'left');
        $this->db->group_by($p.'diagramy.id');

        return $result = $this->db->get()->result_array();
    }
}
