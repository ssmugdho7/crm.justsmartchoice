<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content"><div class="row"><div class="col-md-12">
<?php if ($this->session->flashdata('stripe_hub_api_error')) { ?><div class="alert alert-warning"><?= e($this->session->flashdata('stripe_hub_api_error')); ?></div><?php } ?>
