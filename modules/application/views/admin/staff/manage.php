<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <?php if (staff_can('create',  'staff')) { ?>
                <div class="tw-mb-2">
                    <a href="<?php echo admin_url('staff/member'); ?>" class="btn btn-primary">
                        <i class="fa-regular fa-plus tw-mr-1"></i>
                        <?php echo _l('new_staff'); ?>
                    </a>
                </div>
                <?php } ?>
                <div class="panel_s">
                    <div class="panel-body panel-table-full">
                        <?php
                        $table_data = [
                            _l('staff_dt_name'),
                            _l('staff_dt_email'),
                            _l('role'),
                            _l('staff_dt_last_Login'),
                            _l('staff_dt_active'),
                        ];
                        $custom_fields = get_custom_fields('staff', ['show_on_table' => 1]);
                        foreach ($custom_fields as $field) {
                            array_push($table_data, [
                                'name'     => $field['name'],
                                'th_attrs' => ['data-type' => $field['type'], 'data-custom-field' => 1],
                            ]);
                        }
                        render_datatable($table_data, 'staff');
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="delete_staff" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <?php echo form_open(admin_url('staff/delete', ['delete_staff_form'])); ?>
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?php echo _l('delete_staff'); ?></h4>
            </div>
            <div class="modal-body">
                <div class="delete_id">
                    <?php echo form_hidden('id'); ?>
                </div>
                <p><?php echo _l('delete_staff_info'); ?></p>
                <?php
                echo render_select('transfer_data_to', $staff_members, ['staffid', ['firstname', 'lastname']], 'staff_member', get_staff_user_id(), [], [], '', '', false);
                ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button type="submit" class="btn btn-danger _delete"><?php echo _l('confirm'); ?></button>
            </div>
        </div><!-- /.modal-content -->
        <?php echo form_close(); ?>
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<?php init_tail(); ?>
<script>
$(function() {
    initDataTable('.table-staff', window.location.href);
    $('body').addClass('smart-choice-staff-table-fix-v355');
    smartChoiceStaffToolbar();

        setTimeout(function(){
            var $wrap = $('.table-staff').closest('.dataTables_wrapper');
            $wrap.find('.dt-buttons .buttons-excel, .dt-buttons a:contains("Excel"), .dt-buttons button:contains("Excel")').remove();
        }, 900);
    $('.table-staff').on('draw.dt', function(){ smartChoiceStaffCheckboxes(); });
});


function smartChoiceStaffToolbar() {
    setTimeout(function(){
        var $wrap = $('.table-staff').closest('.dataTables_wrapper');
        var $target = $wrap.find('.dt-buttons').first();
        if (!$target.length) { $target = $wrap.find('.dataTables_length').first(); }
        if (!$target.length || $('#smart_choice_staff_toolbar').length) { return; }
        var html = '<span id="smart_choice_staff_toolbar" class="btn-group mleft5">'
            + '<button type="button" class="btn btn-default btn-sm" onclick="smartChoiceStaffImport();"><i class="fa fa-upload"></i> Import</button>'
            + '<a class="btn btn-default btn-sm" href="'+admin_url+'staff/sample_header"><i class="fa fa-download"></i> Sample Header</a>'
            + '<button type="button" class="btn btn-danger btn-sm" onclick="smartChoiceStaffMassDelete();"><i class="fa fa-trash"></i> Mass Delete</button>'
            + '</span>';
        $target.after(html);
        smartChoiceStaffCheckboxes();
    }, 700);
}
function smartChoiceStaffCheckboxes(){
    $('.table-staff tbody tr').each(function(){
        var $tr=$(this); if($tr.find('.smart-choice-staff-check').length){return;}
        var id='';
        var href=$tr.find('a[href*="/admin/staff/member/"]').first().attr('href') || '';
        var m=href.match(/\/staff\/member\/(\d+)/); if(m){id=m[1];}
        if(!id){return;}
        var $td=$tr.children('td').first();
        $td.prepend('<input type="checkbox" class="smart-choice-staff-check" value="'+id+'" style="margin-right:4px;vertical-align:middle;">');
    });
}
function smartChoiceStaffMassDelete(){
    var ids=[]; $('.smart-choice-staff-check:checked').each(function(){ids.push($(this).val());});
    if(!ids.length){ alert_float('warning','Select at least one staff member.'); return; }
    if(!confirm('Delete selected staff members?')){return;}
    $.post(admin_url+'staff/mass_delete',{ids:ids}).done(function(r){
        try{r=JSON.parse(r);}catch(e){}
        if(r.success){ alert_float('success','Selected staff deleted.'); $('.table-staff').DataTable().ajax.reload(null,false); }
        else{ alert_float('warning', r.message || 'Nothing was deleted.'); }
    });
}
function smartChoiceStaffImport(){
    if(!$('#smart_choice_staff_import_modal').length){
        $('body').append('<div class="modal fade" id="smart_choice_staff_import_modal"><div class="modal-dialog"><div class="modal-content"><form action="'+admin_url+'staff/import_csv" method="post" enctype="multipart/form-data"><div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button><h4 class="modal-title">Import Staff</h4></div><div class="modal-body"><input type="file" name="staff_import_file" class="form-control" accept=".csv" required><p class="text-muted mtop10">Use the Sample Header file before importing.</p></div><div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Close</button><button type="submit" class="btn btn-primary">Import</button></div></form></div></div></div>');
    }
    $('#smart_choice_staff_import_modal').modal('show');
}

function delete_staff_member(id) {
    $('#delete_staff').modal('show');
    $('#transfer_data_to').find('option').prop('disabled', false);
    $('#transfer_data_to').find('option[value="' + id + '"]').prop('disabled', true);
    $('#delete_staff .delete_id input').val(id);
    $('#transfer_data_to').selectpicker('refresh');
}
</script>
</body>

</html>