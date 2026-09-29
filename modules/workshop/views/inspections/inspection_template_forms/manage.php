<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<div id="wrapper">
    <div class="content">
        <div class="row">

            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <input type="hidden" name="isedit">

                        <div class="row">
                            <div class="col-md-6">
                                <h4><?php echo html_entity_decode( format_inspection_number($inspection->id).' - '.$inspection->inspection_template_name); ?></h4>
                            </div>
                            <div class="col-md-6">
                                <a href="<?php echo admin_url('workshop/inspection_detail/'.$inspection->id.'?tab=detail'); ?>" class="btn btn-default pull-right display-block mright5">
                                    <?php echo _l('wshop_back'); ?>
                                </a>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                        <hr>
                        <input type="hidden" name="inspection_id" value="<?php echo html_entity_decode($inspection->id); ?>">

                        <div class="row">
                            <div class="col-md-3">
                                <div class="row">
                                    <div class="col-md-12">
                                        
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12" id="form_tab">
                                        <!-- Nav tabs -->
                                        <ul class="nav navbar-pills navbar-pills-flat nav-tabs nav-stacked " id="sortable" role="tablist"  aria-orientation="vertical">
                                            <?php $this->load->view('inspections/inspection_template_forms/inspection_template_form_tab'); ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-9">

                                <!-- Tab panes -->
                                <div class="tab-content">
                                    <?php if(isset($inspection_forms) && count($inspection_forms) > 0){ ?>
                                        <?php foreach ($inspection_forms as $key => $inspection_form) { ?>
                                            <div class="tab-pane <?php if($key == 0){echo "active";} ?>" id="template_form_<?php echo html_entity_decode($inspection_form['id']) ?>" role="tabpanel" aria-labelledby="template_form_<?php echo html_entity_decode($inspection_form['id']) ?>-tab">

                                         <?php echo form_open_multipart(admin_url('workshop/add_edit_inspection_form/'.$inspection->id), array('class' => 'add_edit_inspection_form', 'autocomplete'=>'off')); ?>
                                                <h4><?php echo html_entity_decode($inspection_form['name']); ?></h4>
                                                <p class="tw-flex tw-text-justify"><?php echo html_entity_decode($inspection_form['description']); ?></p>
                                                <div class="clearfix"></div><hr />
                                                
                                                <div id="form_detail_<?php echo html_entity_decode($inspection_form['id']); ?>">
                                                    
                                                </div>

                                                <!-- add labour product -->
                                                <!-- get via ajax -->
                                                <!-- add part -->

                                            <div class="modal-footer btn-bottom-toolbar">
                                                
                                                <a href="<?php echo admin_url('workshop/inspection_detail/'.$inspection->id.'?tab=detail'); ?>" class="btn btn-default ">
                                                    <?php echo _l('close'); ?>
                                                </a>
                                                <button type="submit" class="btn btn-info inspection_submit_button"><?php echo _l('submit'); ?></button>
                                            </div>
                                            <?php echo form_close(); ?>

                                            </div>


                                        <?php } ?>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>



                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<input type="hidden" name="inspection_form_detail_id">

<div id="modal_wrapper"></div>
<?php $this->load->view('repair_jobs/modals/labour_product_modal'); ?>
<?php $this->load->view('repair_jobs/modals/part_modal'); ?>

<?php init_tail(); ?>

<?php 
require 'modules/workshop/assets/js/inspections/inspection_template_forms/manage_js.php';
require 'modules/workshop/assets/js/inspections/inspection_template_forms/labour_part_js.php';
require('modules/workshop/assets/js/inspections/modals/part_modal_js.php');
require('modules/workshop/assets/js/inspections/modals/labour_product_modal_js.php');

?>
</body>
</html>
