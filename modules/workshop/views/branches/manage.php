<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php  init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h4><?php echo _l('wshop_branches'); ?></h4>
                            </div>
                            <?php if(has_permission('workshop_branch', '', 'create')){ ?>
                                <div class="col-md-6">
                                    <a href="#" onclick="branch_modal(0); return false;" class="btn btn-info pull-right display-block">
                                        <?php echo _l('wshop_new'); ?>
                                    </a>
                                </div>
                            <?php } ?>
                        </div>
                        <div class="clearfix"></div>
                        <hr>
                        <?php 
                        render_datatable(
                            array(
                                _l('id'),
                                _l('wshop_name'),
                                _l('wshop_branch_email'),
                                _l('wshop_address'),
                                _l('wshop_branch_phone'),
                                _l('wshop_status'),
                                _l('options'),
                            ),'branch_table'
                        );
                        ?>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<div id="modal_wrapper"></div>

<div class="modal fade" id="mail_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog  modal-lg">
        <?php echo form_open_multipart(admin_url('workshop/send_mail_to_branch'), array('id' => 'mail_branch-form')); ?>
        <div class="modal-content width-100">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">
                    <span><?php echo _l('send_mail'); ?></span>
                </h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <?php $attr = [];
                        $attr = ['disabled' => "true"];
                        echo render_input('branch_name', 'wshop_name', '', 'text', $attr);

                        echo form_hidden('branch_id', '');
                        ?>
                    </div>
                    <div class="col-md-12">
                        <?php echo render_input('branch_email', 'wshop_branch_email', ''); ?>
                    </div>

                    <div class="col-md-12">
                        <?php echo render_input('email_subject', 'subject'); ?>
                    </div>

                    <div class="col-md-12">
                        <?php echo render_textarea('email_content', 'content', '', array(), array(), '', 'tinymce') ?>
                    </div>
                    <div id="type_care">

                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type=""class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button id="sm_btn" type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
            </div>
        </div><!-- /.modal-content -->
        <?php echo form_close(); ?>
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<?php init_tail(); ?>
<?php 
require('modules/workshop/assets/js/branches/manage_js.php');
?>
</body>
</html>
