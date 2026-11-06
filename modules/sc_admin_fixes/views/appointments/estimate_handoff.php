<?php defined('BASEPATH') or exit('No direct script access allowed');
$handoff=$appointment_prefill;unset($appointment_prefill);
include APPPATH.'views/admin/estimates/estimate.php';
$this->load->view('appointly/smart_choice/estimate_prefill',['prefill'=>$handoff]);
