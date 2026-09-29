<div class="modal fade" id="mail_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg">
        <?php echo form_open_multipart(admin_url('workshop/repair_job_send_mail_client'), array('id' => 'mail_client-form')); ?>
        <div class="modal-content width-100">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">
                    <span><?php echo _l('wshop_send_mail'); ?></span>
                </h4>
            </div>
            <div class="modal-body">
                <div class="row">
                	<div class="col-md-12">
                		<?php $attr = [];
                        $attr = ['disabled' => "true"];
                        echo render_input('client_id', 'client', get_company_name($repair_job->client_id), 'text', $attr);

                        echo form_hidden('repair_job_id', $repair_job->id);
                        ?>
                    </div>
                    <div class="col-md-12">
                      <?php echo render_input('email', 'email', $repair_job->contact_email); ?>
                  </div>

                  <div class="col-md-12">
                      <?php echo render_input('subject', 'subject'); ?>
                  </div>

                  <div class="col-md-12">
                      <?php echo render_textarea('content', 'content', '', array(), array(), '', 'tinymce') ?>
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