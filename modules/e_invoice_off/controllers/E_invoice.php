<?php
defined('BASEPATH') or exit('No direct script access allowed');
class E_invoice extends AdminController { public function index() { $data['title']='E-Invoice'; $this->load->view('e_invoice/dashboard',$data); } }
