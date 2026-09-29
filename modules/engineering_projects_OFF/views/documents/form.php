<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php
// PHP 8+ safe defaults to avoid undefined variable warnings
$title = $title ?? _l('add_new_document');
$id    = isset($id) ? (int)$id : (isset($document->id) ? (int)$document->id : 0);
?>
<div id="wrapper" xmlns="http://www.w3.org/1999/html" xmlns="http://www.w3.org/1999/html">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin"><?php echo $title; ?></h4>
                        <hr class="hr-panel-heading" />
                        <?php echo form_open_multipart($this->uri->uri_string(), array('id' => 'document_form')); ?>
                        <?php echo form_hidden('id', (int)$id); ?>
                        <div class="modal-body">

                            <div class="row">
                                <div class="col-md-4"><?php echo render_select('engineering_project_id', $engineering_projects ?? [], ['engg_proj_id','name'], _l('engineering_project'), isset($document) ? ($document->engineering_project_id ?? '') : '', ['data-live-search'=>true]); ?></div>
                                <div class="col-md-4"><?php echo render_select('project_id', $projects ?? [], ['id','name'], _l('crm_project'), isset($document) ? ($document->project_id ?? '') : '', ['data-live-search'=>true]); ?></div>
                                <div class="col-md-4"><?php echo render_select('customer_id', $customers ?? [], ['userid','company'], _l('customer'), isset($document) ? ($document->customer_id ?? '') : '', ['data-live-search'=>true]); ?></div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <?php $name = (isset($document) ? $document->name : ''); ?>
                                    <?php echo render_input('name', 'document_name', $name); ?>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <label for="noc" class="control-label"><?php echo _l('document_noc'); ?></label>
                                    <input type="file" name="noc" class="form-control">
                                    <br>
                                    <?php $noc = (isset($document) ? $document->noc : '');
                                    if ($noc) { ?>
                                        <?php echo document_file($noc) ?>
                                    <?php } ?>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <label for="eng_letter" class="control-label"><?php echo _l('document_eng_letter'); ?></label>
                                    <input type="file" name="eng_letter" class="form-control">
                                    <br>
                                    <?php $eng_letter = (isset($document) ? $document->eng_letter : '');
                                    if ($eng_letter) { ?>
                                        <?php echo document_file($eng_letter) ?>
                                    <?php } ?>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <label for="site_insp" class="control-label"><?php echo _l('document_site_insp'); ?></label>
                                    <input type="file" name="site_insp" class="form-control">
                                    <br>
                                    <?php $site_insp = (isset($document) ? $document->site_insp : '');
                                    if ($site_insp) { ?>
                                    <?php echo document_file($site_insp) ?>
                                    <?php } ?>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <label for="permit" class="control-label"><?php echo _l('document_permit'); ?></label>
                                    <input type="file" name="permit" class="form-control">
                                    <br>
                                    <?php $permit = (isset($document) ? $document->permit : '');
                                    if ($permit) { ?>
                                        <?php echo document_file($permit) ?>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <a href="<?php echo admin_url('engineering_projects/documents'); ?>" type="button" class="btn btn-default"><?php echo _l('close'); ?></a>
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
        $(function() {
            var rules = {};
<?php if (!empty($required_fields)) { foreach ($required_fields as $rf) { ?>
rules['<?php echo htmlspecialchars($rf, ENT_QUOTES, 'UTF-8'); ?>'] = 'required';
<?php } } ?>
appValidateForm($('form'), rules);
        });
    <?php $__doc_id = (int)$id; ?>
</script>
    </body>

    </html>