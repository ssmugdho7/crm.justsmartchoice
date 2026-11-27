<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
                <?php if (true) { ?>
                <div class="fb-nav _buttons tw-mb-2 sm:tw-mb-4">
<a class="btn btn-default pull-left" href="<?php echo admin_url('feedbacks'); ?>"><?php echo _l('feedbacks'); ?></a>
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
                    <a href="<?php echo admin_url('feedbacks/templates'); ?>" class="btn btn-default pull-left mleft5"><i class="fa fa-layer-group"></i> <?php echo _l('fb_template_library'); ?></a>
                    <a href="<?php echo admin_url('feedbacks/reports'); ?>" class="btn btn-default pull-left mleft5"><i class="fa fa-chart-bar"></i> <?php echo _l('fb_reports'); ?></a>
                    <?php if (has_permission('feedbacks','','view')) { ?><a href="<?php echo admin_url('feedbacks/audiences'); ?>" class="btn btn-default pull-left mleft5"><i class="fa fa-users"></i> <?php echo _l('fb_audiences'); ?></a><?php } ?>
                    <?php if (has_permission('feedbacks','','create')) { ?><a href="<?php echo admin_url('feedbacks/sample_header'); ?>" class="btn btn-default pull-left mleft5"><i class="fa fa-file-csv"></i> <?php echo _l('fb_sample_header'); ?></a>
                    <button type="button" class="btn btn-default pull-left mleft5" data-toggle="modal" data-target="#fbImportModal"><i class="fa fa-upload"></i> <?php echo _l('fb_import_questionnaire'); ?></button><?php } ?>
                    <div class="clearfix"></div>
                </div>
                <?php } ?>
                <div class="modal fade" id="fbImportModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><form method="post" enctype="multipart/form-data" action="<?php echo admin_url('feedbacks/import_questionnaire'); ?>"><div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button><h4><?php echo _l('fb_import_questionnaire'); ?></h4></div><div class="modal-body"><?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?><input type="file" name="questionnaire_file" accept=".json,application/json" required><p class="text-muted mtop10"><?php echo _l('fb_import_json_note'); ?></p></div><div class="modal-footer"><button class="btn btn-primary" type="submit"><?php echo _l('import'); ?></button></div></form></div></div></div>
