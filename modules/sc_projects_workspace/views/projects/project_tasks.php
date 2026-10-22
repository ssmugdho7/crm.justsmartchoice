<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php $this->load->view('../../modules/sc_projects_workspace/views/projects/punchlist'); ?>
<?php // Preserve the complete native task table, filters, timers, priorities and bulk actions.
include APPPATH . 'views/admin/projects/project_tasks.php'; ?>
