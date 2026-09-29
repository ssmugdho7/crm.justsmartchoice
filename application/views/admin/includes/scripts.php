<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php include_once APPPATH . 'views/admin/includes/helpers_bottom.php'; ?>

<?php hooks()->do_action('before_js_scripts_render'); ?>

<?= app_compile_scripts();

/**
 * Global function for custom field of type hyperlink
 */
echo get_custom_fields_hyperlink_js_function(); ?>
<?php
/**
 * Check for any alerts stored in session
 */
app_js_alerts();
?>
<?php
/**
 * Check pusher real time notifications
 */
if (get_option('pusher_realtime_notifications') == 1) { ?>
<script type="text/javascript">
    $(function() {
        // Enable pusher logging - don't include this in production
        // Pusher.logToConsole = true;
        <?php $pusher_options = hooks()->apply_filters('pusher_options', [['disableStats' => true]]);
    if (! isset($pusher_options['cluster']) && get_option('pusher_cluster') != '') {
        $pusher_options['cluster'] = get_option('pusher_cluster');
    }
    ?>
        var
            pusher_options = <?= json_encode($pusher_options); ?> ;
        var pusher = new Pusher(
            "<?= get_option('pusher_app_key'); ?>",
            pusher_options);
        var channel = pusher.subscribe(
            'notifications-channel-<?= get_staff_user_id(); ?>'
        );
        channel.bind('notification', function(data) {
            fetch_notifications();
        });
    });
</script>
<?php } ?>
<?php app_admin_footer(); ?>
<script src="<?= base_url('assets/js/smart-choice-sales-inputs.js?v=403'); ?>"></script>
<script src="<?= base_url('assets/js/sc-phone-format.js?v=4.2.8'); ?>"></script>

<link rel="stylesheet" href="<?= base_url('assets/css/sc-crm-391.css?v=4.0.6'); ?>">
<script>window.scDefaultValidityMonths=<?= (int)(get_option('smart_choice_sales_default_validity_months') ?: 3); ?>;</script>
<script src="<?= base_url('assets/js/sc-crm-391.js?v=4.0.6'); ?>"></script>

<style>.sc-status-wrap{display:flex;gap:6px;align-items:center}.sc-status-wrap .bootstrap-select{flex:1}.sc-status-add{min-width:38px}</style>
<script>window.scStatusAddedLabel=<?= json_encode(_l('sc_status_added')); ?>;window.scStatusAddFailedLabel=<?= json_encode(_l('sc_status_add_failed')); ?>;window.scStatusAddLabel=<?= json_encode(_l('sc_add_status')); ?>;</script>
<div class="modal fade" id="sc-sales-status-modal" tabindex="-1"><div class="modal-dialog modal-sm"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button><h4 class="modal-title"><?= _l('sc_add_status'); ?></h4></div><div class="modal-body"><input type="hidden" id="sc_status_type"><div class="form-group"><label><?= _l('sc_status_name'); ?></label><input type="text" id="sc_status_name" class="form-control"></div><div class="form-group"><label><?= _l('sc_status_color'); ?></label><input type="color" id="sc_status_color" class="form-control" value="#169179"></div></div><div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal"><?= _l('close'); ?></button><button type="button" class="btn btn-success" id="sc-save-sales-status"><?= _l('save'); ?></button></div></div></div></div>




<?php $scAdminJs = trim((string)get_option('sc_custom_admin_js')); if ($scAdminJs !== '') { ?>
<script id="sc-custom-admin-js"><?= $scAdminJs; ?></script>
<?php } ?>
<script id="sc-staff-idle-monitor">
(function(w,d,$){
  var minutes=parseInt('<?= (int)(get_option('sc_staff_idle_timeout_minutes') ?: 10); ?>',10)||10;
  var timer=null,shown=false;
  function arm(){ if(timer){clearTimeout(timer);} if(shown){return;} timer=setTimeout(onIdle,minutes*60000); }
  function onIdle(){ shown=true; var data={minutes:minutes}; if(typeof csrfData!=='undefined'){data[csrfData.token_name]=csrfData.hash;} $.post(admin_url+'smart_choice_ui/log_idle',data); $('#sc-idle-warning-modal').modal('show'); }
  $(d).on('mousemove keydown click scroll touchstart','body',function(){ if(!shown){arm();} });
  $(d).on('hidden.bs.modal','#sc-idle-warning-modal',function(){shown=false;arm();});
  $(function(){arm();});
})(window,document,jQuery);
</script>
<div class="modal fade" id="sc-idle-warning-modal" tabindex="-1"><div class="modal-dialog modal-sm"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button><h4 class="modal-title"><i class="fa fa-clock"></i> <?= _l('sc_idle_warning_title'); ?></h4></div><div class="modal-body"><p><?= _l('sc_idle_warning_message', (int)(get_option('sc_staff_idle_timeout_minutes') ?: 10)); ?></p></div><div class="modal-footer"><button type="button" class="btn btn-primary" data-dismiss="modal"><?= _l('continue'); ?></button></div></div></div></div>

<script id="sc-two-language-ui">$(function(){ $('select[name="default_language"]').each(function(){var $s=$(this);$s.find('option[value=""]').remove();if(!$s.val()){$s.val('english');}try{$s.selectpicker('refresh');}catch(e){}}); });</script>

<div class="modal fade" id="sc-world-clock-manager-modal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
<div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button><h4 class="modal-title"><i class="fa fa-earth-americas"></i> <?= _l('sc_manage_world_clocks'); ?></h4></div>
<div class="modal-body"><div id="sc-world-clock-editor"></div><button type="button" class="btn btn-default btn-sm" id="sc-add-world-clock"><i class="fa fa-plus"></i> <?= _l('add'); ?></button>
<p class="text-muted mtop10 mbot0"><?= _l('sc_world_clock_iana_help'); ?></p></div>
<div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal"><?= _l('close'); ?></button><button type="button" class="btn btn-primary" id="sc-save-world-clocks"><i class="fa fa-save"></i> <?= _l('save'); ?></button></div>
</div></div></div>
<script id="sc-world-clock-manager-script">
(function(w,d,$){
 var zones=<?= json_encode(timezone_identifiers_list()); ?>;
 function current(){try{return JSON.parse('<?= addslashes((string)get_option('sc_world_clocks')); ?>')||[];}catch(e){return[];}}
 function row(item){item=item||{label:'',zone:'America/New_York'};var opts=zones.map(function(z){return '<option value="'+$('<div>').text(z).html()+'"'+(z===item.zone?' selected':'')+'>'+z+'</option>';}).join('');return '<div class="row sc-clock-editor-row mbot10"><div class="col-sm-4"><input type="text" class="form-control sc-clock-label" maxlength="80" placeholder="<?= e(_l('sc_city_country')); ?>" value="'+$('<div>').text(item.label||'').html()+'"></div><div class="col-sm-7"><select class="form-control sc-clock-zone">'+opts+'</select></div><div class="col-sm-1"><button type="button" class="btn btn-danger btn-sm sc-remove-clock"><i class="fa fa-times"></i></button></div></div>';}
 function openEditor(){var data=current();if(!data.length){data=[{label:'Florida (Eastern)',zone:'America/New_York'}];}var h='';$.each(data,function(_,x){h+=row(x);});$('#sc-world-clock-editor').html(h);$('#sc-world-clock-manager-modal').modal('show');}
 $(d).off('click.scClockManage','#sc-manage-world-clocks').on('click.scClockManage','#sc-manage-world-clocks',function(e){e.preventDefault();e.stopPropagation();openEditor();});
 $(d).on('click','#sc-add-world-clock',function(){if($('#sc-world-clock-editor .sc-clock-editor-row').length<20){$('#sc-world-clock-editor').append(row());}});
 $(d).on('click','.sc-remove-clock',function(){$(this).closest('.sc-clock-editor-row').remove();});
 $(d).on('click','#sc-save-world-clocks',function(){var list=[];$('#sc-world-clock-editor .sc-clock-editor-row').each(function(){var label=$.trim($(this).find('.sc-clock-label').val()),zone=$(this).find('.sc-clock-zone').val();if(label&&zone){list.push({label:label,zone:zone});}});var data={clocks:JSON.stringify(list)};if(typeof csrfData!=='undefined'){data[csrfData.token_name]=csrfData.hash;}$.post(admin_url+'smart_choice_ui/save_world_clocks',data).done(function(resp){if(typeof resp==='string'){try{resp=JSON.parse(resp);}catch(e){resp={success:false};}}if(resp&&resp.success){alert_float('success','<?= e(_l('settings_updated')); ?>');$('#sc-world-clock-manager-modal').modal('hide');window.location.reload();}else{alert_float('danger','<?= e(_l('problem_updating')); ?>');}});});
})(window,document,jQuery);
</script>
