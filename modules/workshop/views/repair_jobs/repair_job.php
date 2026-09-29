<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <?php
            $id = '';
            if (isset($repair_job)) {
                $id = $repair_job->id;
            }
            echo form_open(admin_url('workshop/add_edit_repair_job/'.$id), ['id' => 'add_edit_repair_job', 'class' => '_repair_form repair_job-form']);
            if (isset($repair_job)) {
                echo form_hidden('isedit');
            }
            ?>
            <div class="col-md-12">
                <h4
                class="tw-mt-0 tw-font-semibold tw-text-lg tw-text-neutral-700 tw-flex tw-items-center tw-space-x-2">
                <span>
                    <?php echo e( isset($repair_job) ? format_repair_job_number($repair_job->id) : _l('create_new_repair_job')); ?>
                </span>
                <?php echo isset($repair_job) ? render_repair_job_status_html($repair_job->id, '', $repair_job->status, false) : ''; ?>
            </h4>
            <?php $this->load->view('repair_jobs/repair_job_template'); ?>
            <?php $this->load->view('repair_jobs/modals/labour_product_modal'); ?>
            <?php $this->load->view('repair_jobs/modals/part_modal'); ?>

        </div>
        <?php echo form_close(); ?>
    </div>
</div>
</div>
</div>
<?php init_tail(); ?>
<?php 
require('modules/workshop/assets/js/repair_jobs/repair_job_js.php');
require('modules/workshop/assets/js/repair_jobs/modals/part_modal_js.php');
require('modules/workshop/assets/js/repair_jobs/modals/labour_product_modal_js.php');
?>
</body>

</html>
