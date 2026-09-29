<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php $this->load->view('dashboard', isset($data) && is_array($data) ? $data : []); ?>
