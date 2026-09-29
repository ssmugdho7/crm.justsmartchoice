<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php
$title = $title ?? _l('add_new');
$id = isset($id) ? (int)$id : (isset($drawing->id) ? (int)$drawing->id : 0);
?>
<div id="wrapper" xmlns="http://www.w3.org/1999/html" xmlns="http://www.w3.org/1999/html">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin"><?php echo $title; ?></h4>
                        <hr class="hr-panel-heading" />
                        <?php echo form_open_multipart($this->uri->uri_string(), array('id' => 'drawing_form')); ?>
                        <?php echo form_hidden('id', $id); ?>
                        <div class="row">
                          <div class="col-md-4"><?php echo render_select('engineering_project_id', $engineering_projects ?? [], ['engg_proj_id','name'], _l('engineering_project'), isset($drawings) ? ($drawings->engineering_project_id ?? '') : '', ['data-live-search'=>true]); ?></div>
                          <div class="col-md-4"><?php echo render_select('project_id', $projects ?? [], ['id','name'], _l('crm_project'), isset($drawings) ? ($drawings->project_id ?? '') : '', ['data-live-search'=>true]); ?></div>
                          <div class="col-md-4"><?php echo render_select('customer_id', $customers ?? [], ['userid','company'], _l('customer'), isset($drawings) ? ($drawings->customer_id ?? '') : '', ['data-live-search'=>true]); ?></div>
                        </div>

                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <?php $name = (isset($drawings) ? $drawings->name : ''); ?>
                                    <?php echo render_input('name', 'drawing_name', $name); ?>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <?php $type = (isset($drawings) ? $drawings->type : ''); ?>
                                    <?php echo render_select('type', drawing_type(false,null),array('id','name'),'drawing_type',$type); ?>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <label for="draf" class="control-label"><?php echo _l('drawing_draf'); ?></label>
                                    <input type="file" name="draf" class="form-control">
                                    <br>
                                    <?php $draf = (isset($drawings) ? $drawings->draf : '');
                                    if($draf){ ?>
                                    <?php echo drawing_file($draf) ?>
                                    <?php } ?>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <label for="final_doc" class="control-label"><?php echo _l('drawing_final'); ?></label>
                                    <input type="file" name="final_doc" class="form-control">
                                    <br>
                                    <?php $final_doc = (isset($drawings) ? $drawings->final_doc : '');
                                    if($final_doc){ ?>
                                    <?php echo drawing_file($final_doc) ?>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <a href="<?php echo admin_url('engineering_projects/drawings'); ?>" type="button" class="btn btn-default"><?php echo _l('close'); ?></a>
                            <button type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
                            <?php echo form_close(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
    $(function () {
        var rules = {};
<?php
$req = get_option('engproj_required_fields');
$req = $req ? json_decode($req, true) : [];
$req_fields = (isset($req['drawings']) && is_array($req['drawings'])) ? $req['drawings'] : [];
foreach ($req_fields as $rf) { ?>
rules['<?php echo htmlspecialchars($rf, ENT_QUOTES, 'UTF-8'); ?>'] = 'required';
<?php } ?>
appValidateForm($('form'), rules);
    });
</script>
</body>
</html>
