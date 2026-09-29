<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$isGridView = 0;
if ($this->session->has_userdata('dmn_grid_view') && $this->session->userdata('dmn_grid_view') == 'true') {
    $isGridView = 1;
}
?>
<?php init_head(); ?>
<link rel="stylesheet" href="<?= base_url('modules/dmn/assets/css/manage-dmn.css');?>">
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="_filters _hidden_inputs hidden">
                        <?php
                        echo form_hidden('my_dmn');
                        foreach($staffs as $staff){
                            echo form_hidden('staffid_'.$staff['staffid']);
                        }
                        foreach($groups as $group){
                            echo form_hidden('dmn_group_id_'.$group['id']);
                         }
                        ?>
                    </div>
                    <div class="panel-body">
                        <div class="row">
                            <div class="_buttons col-md-9 col-xs-7">
                                <div class="btn-group">
                                    <?php if(has_permission('dmn','','create')){ ?>
                                        <a href="<?=admin_url('dmn/dmn_detail');?>" class="text-center-c btn btn-info pull-left display-block mright5"><i class="fa fa-code-fork"></i> <?php echo _l('dmn_create_new'); ?></a>
                                    <?php } ?>
                                </div>
                                <div class="visible-xs">
                                    <div class="clearfix"></div>
                                </div>
                            </div>
                            <div class="col-md-3 col-xs-5 text-right">
                                <?php echo icon_btn('dmn/switch_grid/'.$switch_grid,$switch_view_icon); ?>
                            </div>
                        </div>       
                        <div class="clearfix"></div>
                        <hr class="hr-panel-heading" />
                        <div class="clearfix mtop20"></div>
                        <div class="row" id="dmn-table">
                            <?php if($isGridView ==0){ ?>
                                <div class="col-md-12">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <p class="bold"><?php echo _l('filter_by'); ?></p>
                                        </div>
                                        <?php if(has_permission('dmn','','view')){ ?>
                                            <div class="col-md-3 dmn-filter-column">
                                                <?php echo render_select('view_assigned',$staffs,array('staffid',array('firstname','lastname')),'','',array('data-width'=>'100%','data-none-selected-text'=>_l('dmn_staff')),array(),'no-mbot'); ?>
                                            </div>
                                        <?php } ?>
                                        <div class="col-md-3 dmn-filter-column ma_t">
                                            <?php echo render_select('view_project',$projects,array('id',array('name')),'','',array('data-width'=>'100%','data-none-selected-text'=>_l('project_group')),array(),'no-mbot'); ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="clearfix"></div>
                                <hr class="hr-panel-heading" />
                            <?php } ?>
                            <div class="col-md-12">
                                <?php if($this->session->has_userdata('dmn_grid_view') && $this->session->userdata('dmn_grid_view') == 'true') { ?>
                                    <div class="grid-tab" id="grid-tab">
                                        <div class="row">
                                            <div id="dmn-grid-view" class="container-fluid">
                                            </div>
                                        </div>
                                    </div>
                                <?php } else { ?>
                                    <?php 
                                    render_datatable(array(
                                        _l('dmn_title'),
                                        _l('dmn_desc'),
                                        _l('dmn_staff'),
                                        _l('project_group'),
                                        _l('dmn_created_at')
                                    ),'dmn', array('customizable-table'),
                                    array(
                                      'id'=>'table-dmn',
                                      'data-last-order-identifier'=>'dmn',
                                      'data-default-order'=>get_table_last_order('dmn'),
                                  )); ?>
                              <?php } ?>
                          </div>
                        </div>
                   </div>
               </div>
           </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
    "use strict";
    function validate_dmn_form(){
        appValidateForm($('#dmn-form'), {
            title: 'required',
            dmn_group_id: 'required',
            description : 'required',
        });
        $('#dmn-form').submit();
    }
    var _lnth = 12;
    $(function(){
        var TblServerParams = {
            "assigned": "[name='view_assigned']",
            "project": "[name='view_project']",
        };
        if(<?php echo $isGridView ?> == 0) {
            var tAPI = initDataTable('.table-dmn', admin_url+'dmn/table', [2, 3], [2, 3], TblServerParams,[4, 'desc']);
            $.each(TblServerParams, function(i, obj) {
                $('select' + obj).on('change', function() {
                    $('table.table-dmn').DataTable().ajax.reload()
                    .columns.adjust()
                    .responsive.recalc();
                });
            });
        }
        else{
            loadGridView();
            $(document).off().on('click','a.paginate',function(e){
                e.preventDefault();
                var pageno = $(this).data('ci-pagination-page');
                var formData = {
                    search: $("input#search").val(),
                    start: (pageno-1),
                    length: _lnth,
                    draw: 1
                }
                gridViewDataCall(formData, function (resposne) {
                    $('div#grid-tab').html(resposne);
                })
            });
       }
    });
</script>
</body>
</html>
