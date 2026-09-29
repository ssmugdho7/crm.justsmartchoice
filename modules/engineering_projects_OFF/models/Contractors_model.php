<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Contractors_model extends App_Model {

  public function __construct() {
    parent::__construct();
  }

  private function clean_data($data)
  {
    $allowed = ['contractor','email','phone','address','active'];
    return array_intersect_key($data, array_flip($allowed));
  }

  /**
   * @param  integer (optional)
   * @return object
   * Get single contractor
   */
  public function get($id = '', $exclude_notified = false) {
    if (is_numeric($id)) {
      $this->db->where('id', $id);

      return $this->db->get(db_prefix() . 'eng_contractors')->row();
    }

    return $this->db->get(db_prefix() . 'eng_contractors')->result_array();
  }

  public function get_all_contractors($exclude_notified = true) {


    $contractors = $this->db->get(db_prefix() . 'eng_contractors')->result_array();
    return array_values($contractors);
  }

  public function duplicate_contact($data, $id = null)
  {
    $email = isset($data['email']) ? trim((string)$data['email']) : '';
    $phone = isset($data['phone']) ? preg_replace('/\D+/', '', (string)$data['phone']) : '';
    if ($email !== '') {
      $this->db->where('LOWER(email)', strtolower($email));
      if ($id) { $this->db->where('id !=', (int)$id); }
      if ($this->db->count_all_results(db_prefix() . 'eng_contractors') > 0) {
        return _l('contractor_duplicate_email');
      }
    }
    if ($phone !== '') {
      $rows = $this->db->select('id, phone')->get(db_prefix() . 'eng_contractors')->result_array();
      foreach ($rows as $row) {
        if ($id && (int)$row['id'] === (int)$id) { continue; }
        if (preg_replace('/\D+/', '', (string)$row['phone']) === $phone) {
          return _l('contractor_duplicate_phone');
        }
      }
    }
    return false;
  }

  /**
   * Add new contractor
   * @param mixed $data All $_POST dat
   * @return mixed
   */
  public function add($data) {
    $data = $this->clean_data($data);
    $this->db->insert(db_prefix() . 'eng_contractors', $data);
    $insert_id = $this->db->insert_id();
    if ($insert_id) {
      log_activity('New Measurement Added [ID:' . $insert_id . ']');

      return $insert_id;
    }

    return false;
  }

  /**
   * Update contractor
   * @param  mixed $data All $_POST data
   * @param  mixed $id   contractor id
   * @return boolean
   */
  public function update($data, $id) {
    $this->db->where('id', $id);
    $data = $this->clean_data($data);
    $this->db->update(db_prefix() . 'eng_contractors', $data);
    if ($this->db->affected_rows() > 0) {
      log_activity('Measurement Updated [ID:' . $id . ']');

      return true;
    }

    return false;
  }

  /**
   * Delete contractor
   * @param  mixed $id contractor id
   * @return boolean
   */
  public function delete($id) {
    $this->db->where('id', $id);
    $this->db->delete(db_prefix() . 'eng_contractors');
    if ($this->db->affected_rows() > 0) {
      log_activity('Measurement Deleted [ID:' . $id . ']');

      return true;
    }

    return false;
  }
}
