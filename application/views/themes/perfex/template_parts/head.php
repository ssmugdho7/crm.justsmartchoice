<?php

defined('BASEPATH') or exit('No direct script access allowed');

// Smart Choice compatibility shim for modules still loading the old client theme path:
// themes/perfex/template_parts/head.php
$this->load->view('themes/perfex/head');
