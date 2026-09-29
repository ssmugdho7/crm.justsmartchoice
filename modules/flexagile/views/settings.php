<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="tw-flex tw-justify-between tw-items-center tw-mb-2 sm:tw-mb-4">
                    <h4 class="tw-my-0 tw-font-semibold tw-text-lg tw-self-end">
                        <?php echo $title; ?>
                    </h4>
                    <div>
                        <a href="<?php echo admin_url('flexagile'); ?>" class="btn btn-secondary mright5">
                            <?php echo flexagile_lang('back-to-sprints'); ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 animated fadeIn">
                <?php echo form_open($this->uri->uri_string(), ['id' => 'flexagile_settings']); ?>
                <div class="panel_s">
                    <div class="panel-body">

                        <div class="form-group">
                            <?php echo render_yes_no_option('flexagile_auto_add_new_task_to_active_sprint', 'flexagile_auto_add_new_task_to_active_Sprint'); ?>
                            <p class="help-block"><?php echo flexagile_lang('auto_add_new_task_to_active_sprint_desc'); ?></p>
                        </div>
                        <div class="form-group">
                            <?php echo render_yes_no_option('flexagile_add_billable_tasks_only_to_sprint_invoice', 'flexagile_add_billable_tasks_only_to_sprint_invoice'); ?>
                            <p class="help-block"><?php echo flexagile_lang('add_billable_tasks_only_to_sprint_invoice_desc'); ?></p>
                        </div>
                    </div>
                    <div class="panel-footer text-right">
                        <button type="submit" class="btn btn-primary">
                            <?php echo flexagile_lang('save-changes'); ?>
                        </button>
                    </div>
                </div>
                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
</body>

</html>
