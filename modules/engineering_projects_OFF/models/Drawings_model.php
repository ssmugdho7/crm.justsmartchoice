<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Drawings_model extends App_Model
{

  public function __construct()
  {
    parent::__construct();
  }

  private function clean_data($data)
  {
    $allowed = ['engineering_project_id', 'project_id', 'customer_id', 'name', 'type', 'draf', 'final_doc', 'folder_path', 'datecreated'];
    return array_intersect_key((array)$data, array_flip($allowed));
  }

  /**
   * @param  integer (optional)
   * @return object
   * Get single drawing
   */
  public function get($id = '', $exclude_notified = false)
  {
    if (is_numeric($id)) {
      $this->db->where('id', $id);

      return $this->db->get(db_prefix() . 'eng_drawings')->row();
    }

    return $this->db->get(db_prefix() . 'eng_drawings')->result_array();
  }

  public function get_all_drawings($exclude_notified = true)
  {


    $drawings = $this->db->get(db_prefix() . 'eng_drawings')->result_array();
    return array_values($drawings);
  }

  /**
   * Add new drawing
   * @param mixed $data All $_POST dat
   * @return mixed
   */
  public function add($data)
  {
    $data = $this->clean_data($data);
    $this->db->insert(db_prefix() . 'eng_drawings', $data);
    $insert_id = $this->db->insert_id();
    if ($insert_id) {
      log_activity('New Engineering Record Added [ID:' . $insert_id . ']');

      return $insert_id;
    }

    return false;
  }

  /**
   * Update drawing
   * @param  mixed $data All $_POST data
   * @param  mixed $id   drawing id
   * @return boolean
   */
  public function update($data, $id)
  {
    $this->db->where('id', $id);
    $data = $this->clean_data($data);
    $this->db->update(db_prefix() . 'eng_drawings', $data);
    if ($this->db->affected_rows() > 0) {
      log_activity('Engineering Record Updated [ID:' . $id . ']');

      return true;
    }

    return false;
  }

  /**
   * Delete drawing
   * @param  mixed $id drawing id
   * @return boolean
   */
  public function delete($id)
  {
    $record = $this->get($id);
    foreach (['draf','final_doc'] as $field) { if ($record && !empty($record->{$field})) { $path = !empty($record->folder_path) ? $record->folder_path.$record->{$field} : get_upload_path_by_type_module('eng_drawing_files').$record->{$field}; if (is_file($path)) { @unlink($path); } } }
    $this->db->where('id', $id);
    $this->db->delete(db_prefix() . 'eng_drawings');
    if ($this->db->affected_rows() > 0) {
      log_activity('Engineering Record Deleted [ID:' . $id . ']');

      return true;
    }

    return false;
  }
}
