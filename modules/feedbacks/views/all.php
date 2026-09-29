<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <?php if (has_permission('feedbacks', '', 'create') || has_permission('feedbacks', '', 'view')) { ?>
                <div class="_buttons tw-mb-2 sm:tw-mb-4">
                    <?php if (has_permission('feedbacks', '', 'create')) { ?>
                    <a href="<?php echo admin_url('feedbacks/feedback'); ?>"
                        class="btn btn-primary pull-left display-block">
                        <i class="fa-regular fa-plus tw-mr-1"></i>
                        <?php echo _l('new_feedback'); ?>
                    </a>
                    <?php } ?>
                    <?php if (has_permission('feedbacks', '', 'view')) { ?>
                    <a href="<?php echo admin_url('feedbacks/mail_lists'); ?>"
                        class="btn btn-default pull-left mleft5 display-block">
                        <i class="fa-solid fa-envelopes-bulk tw-mr-1"></i>
                        <?php echo _l('mail_lists'); ?>
                    </a>
                    <?php } ?>
                    <div class="clearfix"></div>
                </div>
                <?php } ?>
                <div class="panel_s">
                    <div class="panel-body panel-table-full">
                        <?php render_datatable([
                            _l('id'),
                            _l('feedback_dt_name'),
                            _l('feedback_dt_total_questions'),
                            _l('feedback_dt_total_participants'),
                            _l('feedback_dt_date_created'),
                            _l('feedback_dt_active'),
                            ], 'feedbacks'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
$(function() {
    initDataTable('.table-feedbacks', window.location.href);
});
</script>
</body>

</html>