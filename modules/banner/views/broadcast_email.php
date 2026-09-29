<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="clearfix"></div>
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="tw-mt-0 tw-font-semibold tw-text-lg tw-flex tw-items-center">
                            <span>
                                <?php echo _l('send_broadcast_email_banner'); ?>
                            </span>
                        </h4>
                        <hr class="hr-panel-heading">
                        <div>
                            <?= form_open('', ['id' => 'banner-mail-form']); ?>
                            <?php echo render_input('subject', 'subject', '') ?>
                            <?php echo render_select('staff[]', $staff,['staffid','full_name'], 'staff','',['multiple' => true, 'data-actions-box' => true],[],'','',false) ?>
                            <?php echo render_textarea('content', 'email_message', '', [], [], '', 'tinymce'); ?>
                            <hr class="hr-panel-heading">
                            <p class="bold text-right"><a href="#" onclick="slideToggle('.avilable_merge_fields'); return false;">
                                <?php echo _l('available_merge_fields'); ?></a>
                            </p>
                            <div class="hide avilable_merge_fields mtop15">
                                <hr class="hr-panel-heading">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h4><strong><?= _l('staff_merge_fields') ?></strong></h4>
                                        <ul class="list-group">
                                            <?php
                                                foreach ($staff_merge_fields as $f) {
                                                    echo '<li class="list-group-item"><b>' . $f['name'] . '</b> <a href="#" class="pull-right" onclick="insert_proposal_merge_field(this); return false;">' . $f['key'] . '</a></li>';
                                                     
                                                }
                                            ?>
                                        </ul>
                                    </div>
                                    <div class="col-md-6">
                                        <h4><strong><?= _l('other_merge_fields') ?></strong></h4>
                                        <ul class="list-group">
                                            <?php
                                                foreach ($other_merge_fields as $f) {
                                                    echo '<li class="list-group-item"><b>' . $f['name'] . '</b> <a href="#" class="pull-right" onclick="insert_proposal_merge_field(this); return false;">' . $f['key'] . '</a></li>'; 
                                                }
                                            ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <hr class="hr-panel-heading">
                            <div class="pull-right">
                                <button type="submit" class="btn btn-primary" id="send_mail_to_staff"><?= _l('send_mail') ?></button>
                            </div>
                            <?= form_close(); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="example_Modal" tabindex="-1" role="dialog" aria-labelledby="example_ModalLabel" aria-hidden="true"data-backdrop="static" data-keyboard="false">
      <div class="modal-dialog custom_loding_modal" role="document">
        <div class="modal-content custom_loding_content">
          <div class="modal-body">
            <div class="spinner-border text-primary" role="status">
              <img src="<?php echo base_url('assets/plugins/lightbox/images/loading.gif'); ?>" alt="" class="loading_image"> <?= _l('sending') ?>
            </div>
          </div>
        </div>
      </div>
    </div>
</div>

<?php init_tail(); ?>

<script>

    function sendBannerMail(form) {
        $('#example_Modal').modal('show');
        $('#send_mail_to_staff').attr('disabled', true);
        $.ajax({
            url: `${admin_url}banner/send_mail`,
            type: 'post',
            dataType : 'json',
            data: $(form).serialize(),
        }).done(function (res) {
            $('#example_Modal').modal('hide');
            window.location.assign(res.url);
            $('#send_mail_to_staff').attr('disabled', false);
        })
    }

    $(function() {
        
        appValidateForm($('#banner-mail-form'), {
            content : "required",
            subject : "required"
        },sendBannerMail);
    });
</script>
