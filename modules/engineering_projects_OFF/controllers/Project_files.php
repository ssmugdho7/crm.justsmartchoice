<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Project_files extends AdminController {

  public function __construct() {
    parent::__construct();
    //$this->load->model('project_files_model');
  }

  public function index() {
    if (!has_permission('project_files', '', 'view')) {
      access_denied('project_files');
    }
    $data['title'] = _l('project_files_tracking');
    $this->load->view('project_files/files', $data);
  }

  public function upload_attachment() {
    handle_project_files_attachments_upload();
  }



}
