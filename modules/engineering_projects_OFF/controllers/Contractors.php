<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Contractors extends AdminController {

  public function __construct() {
    parent::__construct();
    $this->load->model('engineering_projects/contractors_model');
  }

  public function index() {
    if (!has_permission('contractors', '', 'view')) {
      access_denied('contractors');
    }
    if ($this->input->is_ajax_request()) {
      $this->app->get_table_data(module_views_path('engineering_projects', 'contractors/table'));
    }
    $data['title'] = _l('contractors_tracking');
    $this->load->view('contractors/manage', $data);
  }

  public function contractor() {
    if (!has_permission('contractors', '', 'view')) {
      access_denied('contractors');
    }

    if ($this->input->post()) {
      $id = $this->input->post('id');
      if ($id == '') {
        if (!has_permission('contractors', '', 'create')) {
          access_denied('contractors');
        }
        $data = $this->input->post(null, false);
        $duplicate = $this->contractors_model->duplicate_contact($data);
        if ($duplicate) { echo json_encode(['success'=>false,'message'=>$duplicate]); return; }
        $success = $this->contractors_model->add($data);
        $message = '';
        if ($success > 0) {
          $message = _l('added_successfully', _l('contractor'));
        }
        echo json_encode([
            'success' => $success,
            'message' => $message,
        ]);
      } else {
        if (!has_permission('contractors', '', 'edit')) {
          access_denied('contractors');
        }
        $data = $this->input->post(null, false);
        $duplicate = $this->contractors_model->duplicate_contact($data, $id);
        if ($duplicate) { echo json_encode(['success'=>false,'message'=>$duplicate]); return; }
        $success = $this->contractors_model->update($data, $id);
        $message = '';
        if ($success > 0) {
          $message = _l('updated_successfully', _l('contractor'));
        }
        echo json_encode([
            'success' => $success,
            'message' => $message,
        ]);
      }
    }
  }

  public function delete($id) {
    if (!has_permission('contractors', '', 'delete')) {
      access_denied('contractors');
    }
    if (!$id) {
      redirect(admin_url('engineering_projects/contractors'));
    }
    $response = $this->contractors_model->delete($id);
    if ($response == true) {
      set_alert('success', _l('deleted', _l('contractor')));
    } else {
      set_alert('warning', _l('problem_deleting', _l('contractor_lowercase')));
    }
    redirect(admin_url('engineering_projects/contractors'));
  }

  public function contractor_exists() {
    if ($this->input->post()) {
      $id = $this->input->post('id');
      if ($id != '') {
        $this->db->where('id', $id);
        $_current_contractor = $this->db->get(db_prefix() . 'eng_contractors')->row();
        if ($_current_contractor->ingredient_name == $this->input->post('contractor')) {
          echo json_encode(true);
          die();
        }
      }
      $this->db->where('contractor', $this->input->post('contractor'));
      $total_rows = $this->db->count_all_results(db_prefix() . 'eng_contractors');
      if ($total_rows > 0) {
        echo json_encode(false);
      } else {
        echo json_encode(true);
      }
      die();
    }
  }
}
