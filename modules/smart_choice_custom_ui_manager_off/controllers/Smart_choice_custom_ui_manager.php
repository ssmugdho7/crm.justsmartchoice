<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Smart_choice_custom_ui_manager extends AdminController { public function index() { $data['title']='Smart Choice Custom UI Manager'; $this->load->view('smart_choice_custom_ui_manager/dashboard',$data); } }
