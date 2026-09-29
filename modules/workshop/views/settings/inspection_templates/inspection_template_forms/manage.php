<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<div id="wrapper">
    <div class="content">
        <div class="row">

            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">

                        <div class="row">
                            <div class="col-md-6">
                                <h4><?php echo html_entity_decode($inspection_template->code.' - '.$inspection_template->name); ?></h4>
                            </div>
                            <div class="col-md-6">

                                <a href="<?php echo admin_url('workshop/setting?group=inspection_templates'); ?>" class="btn btn-default pull-right display-block mright5">
                                    <?php echo _l('wshop_back'); ?>
                                </a>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                        <hr>


                        <div class="row">
                            <div class="col-md-3">
                                <div class="row">
                                    <div class="col-md-12">
                                        <?php if(has_permission('workshop_setting', '', 'create')){ ?>
                                            <a href="#" onclick="inspection_template_form_modal(0); return false;" class="btn btn-primary display-block mbot5">
                                                <?php echo _l('wshop_add_new_form'); ?>
                                            </a>
                                        <?php } ?>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12" id="form_tab">
                                        <!-- Nav tabs -->
                                        <ul class="nav navbar-pills navbar-pills-flat nav-tabs nav-stacked " id="sortable" role="tablist"  aria-orientation="vertical">
                                            <?php $this->load->view('settings/inspection_templates/inspection_template_forms/inspection_template_form_tab'); ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-9">

                                <!-- Tab panes -->
                                <div class="tab-content">
                                    <?php if(isset($inspection_template_forms) && count($inspection_template_forms) > 0){ ?>
                                        <?php foreach ($inspection_template_forms as $key => $inspection_template_form) { ?>
                                            <div class="tab-pane <?php if($key == 0){echo "active";} ?>" id="template_form_<?php echo html_entity_decode($inspection_template_form['id']) ?>" role="tabpanel" aria-labelledby="template_form_<?php echo html_entity_decode($inspection_template_form['id']) ?>-tab">
                                                <?php if(has_permission('workshop_setting', '', 'create')){ ?>
                                                    <a href="#" onclick="inspection_template_form_detail_modal(0, <?php echo html_entity_decode($inspection_template_form['id']) ?>); return false;" class="btn btn-info pull-right display-block">
                                                        <?php echo _l('wshop_new_form_detail'); ?>
                                                    </a>
                                                <?php } ?>

                                                <h4><?php echo html_entity_decode($inspection_template_form['name']); ?></h4>
                                                <p class="tw-flex tw-text-justify"><?php echo html_entity_decode($inspection_template_form['description']); ?></p>
                                                <div class="clearfix"></div><hr />
                                                
                                                <div id="form_detail_<?php echo html_entity_decode($inspection_template_form['id']); ?>">
                                                    
                                                </div>
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

<div id="modal_wrapper"></div>

<input type="hidden" name="inspection_template_id" value="<?php echo html_entity_decode($inspection_template_id) ?>">
<?php init_tail(); ?>

<?php 
require 'modules/workshop/assets/js/settings/inspection_templates/inspection_template_forms/manage_js.php';

?>
</body>
</html>
