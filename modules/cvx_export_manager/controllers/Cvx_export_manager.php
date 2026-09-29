<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Cvx_export_manager extends AdminController { public function index() { $data['title']='CSV Export Manager'; $this->load->view('cvx_export_manager/dashboard',$data); } }
