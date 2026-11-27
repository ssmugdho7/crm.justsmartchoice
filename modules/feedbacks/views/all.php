<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
<?php $this->load->view('feedbacks/_navigation'); ?>
                <form method="post" action="<?php echo admin_url('feedbacks/bulk_delete'); ?>" id="feedbacks-bulk-form">
                <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
                <div class="panel_s">
                    <div class="panel-body panel-table-full">
                        <?php if (has_permission('feedbacks', '', 'delete')) { ?><button id="fb-bulk-delete" type="submit" class="btn btn-danger btn-sm" onclick="return confirm('<?php echo _l('fb_confirm_bulk_delete'); ?>')"><i class="fa fa-trash"></i> <?php echo _l('fb_delete_selected'); ?></button><?php } ?>
                        <?php render_datatable([
                            '<input type="checkbox" id="feedbacks-select-all" aria-label="Select all">',
                            _l('id'),
                            _l('feedback_dt_name'),
                            _l('feedback_dt_total_questions'),
                            _l('feedback_dt_total_participants'),
                            _l('feedback_dt_date_created'),
                            _l('fb_created_by'),
                            _l('feedback_dt_active'),
                            _l('options'),
                            ], 'feedbacks'); ?>
                    </div>
                </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
$(function() {
    var selectedFeedbackIds = {};
    var feedbackTable = initDataTable('.table-feedbacks', window.location.href, [0,8], [0,8], {}, [1,'asc']);
    var wrapper = $('.table-feedbacks').closest('.dataTables_wrapper');
    var del = $('#fb-bulk-delete');
    var reload = wrapper.find('.btn-dt-reload').first();
    if (reload.length) del.insertBefore(reload); else wrapper.find('.dt-buttons').append(del);
    var filter = wrapper.find('.dataTables_filter');
    var input = filter.find('input[type=search]').first().detach();
    filter.empty().append($('<div class="fb-search-single"><i class="fa fa-search" aria-hidden="true"></i></div>').append(input));
    input.attr('aria-label', 'Search').attr('placeholder', input.attr('placeholder') || 'Search...');

    function syncFeedbackChecks(){ $('.feedback-row-select').each(function(){ this.checked = !!selectedFeedbackIds[String($(this).data('feedback-id'))]; }); }
    $('#feedbacks-select-all').on('change', function(){ $('.feedback-row-select').each(function(){ var id=String($(this).data('feedback-id')); if ($('#feedbacks-select-all').is(':checked')) selectedFeedbackIds[id]=true; else delete selectedFeedbackIds[id]; }); syncFeedbackChecks(); });
    $(document).on('change', '.feedback-row-select', function(){ var id=String($(this).data('feedback-id')); if(this.checked) selectedFeedbackIds[id]=true; else delete selectedFeedbackIds[id]; });
    $('.table-feedbacks').on('draw.dt', syncFeedbackChecks);
    $('#feedbacks-bulk-form').on('submit', function(e){ $('.feedback-bulk-hidden').remove(); Object.keys(selectedFeedbackIds).forEach(function(id){ $('<input>',{type:'hidden',name:'ids[]',value:id,class:'feedback-bulk-hidden'}).appendTo('#feedbacks-bulk-form'); }); if(Object.keys(selectedFeedbackIds).length===0){ e.preventDefault(); alert('<?php echo _l('fb_select_records'); ?>'); } });
});
</script>
</body>

</html>