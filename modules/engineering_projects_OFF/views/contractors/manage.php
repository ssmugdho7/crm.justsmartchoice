<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <?php if (has_permission('contractors', '', 'create')) { ?>
                          <div class="_buttons">
                              <a href="#<?php echo admin_url('engineering_projects/contractors/contractor'); ?>" data-toggle="modal" data-target="#contractor_modal" class="btn btn-info pull-left display-block"><?php echo _l('new_contractor'); ?></a>
                              <a href="<?php echo admin_url('engineering_projects'); ?>" class="mleft5 btn btn-default pull-right display-block"><?php echo _l('back_to_ingredient'); ?></a>
                          </div>
                          <div class="clearfix"></div>
                          <hr class="hr-panel-heading" />
                        <?php } ?>
                        <?php
                        render_datatable(array(
                            _l('contractor'),
                            _l('contractor_email'),
                            _l('contractor_phone'),
                            _l('contractor_address'),
                            _l('options')
                                ), 'contractors');
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="contractor_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">
                    <span class="edit-title"><?php echo _l('contractor_edit_title'); ?></span>
                    <span class="add-title"><?php echo _l('contractor_add_title'); ?></span>
                </h4>
            </div>
            <?php echo form_open('admin/engineering_projects/contractors/contractor', array('id' => 'contractor_form')); ?>
            <?php echo form_hidden('id'); ?>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <?php echo render_input('contractor', 'contractor', ''); ?>
                    </div>
                    <div class="col-md-12">
                        <?php echo render_input('email', 'contractor_email', ''); ?>
                    </div>
                    <div class="col-md-12">
                        <?php echo render_input('phone', 'contractor_phone', ''); ?>
                    </div>
                    <div class="col-md-12">
                        <?php echo render_input('address', 'contractor_address', ''); ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
  $(function () {

      initDataTable('.table-contractors', window.location.href, [0], [0], undefined, []);

      appValidateForm($('form'), {
          contractor: {
              required: true,
            //   remote: {
            //       url: admin_url + "engineering_projects/contractors/contractor_exists",
            //       type: 'post',
            //       data: {
            //           id: function () {
            //               return $('input[name="id"]').val();
            //           }
            //       }
            //   }
          },
          email:{
              required: true
          },
          phone:{
              required: true
          },
          address:{
              required: true
          }
      }, manage_contractor);

      $('#contractor_modal').on('show.bs.modal', function (event) {
          var button = $(event.relatedTarget)
          var id = button.data('id');
          $(this).find('button[type="submit"]').prop('disabled', false);
          $('#contractor_modal input[name="contractor"]').val('').prop('disabled', false);
          $('#contractor_modal input[name="id"]').val('')
          $('#contractor_modal .add-title').removeClass('hide');
          $('#contractor_modal .edit-title').addClass('hide');
          if (typeof (id) !== 'undefined') {
              $('input[name="id"]').val(id);
              var contractor = $(button).parents('tr').find('td').eq(0).text();
              var email = $(button).parents('tr').find('td').eq(1).text();
              var phone = $(button).parents('tr').find('td').eq(2).text();
              var address = $(button).parents('tr').find('td').eq(3).text();

              $('#contractor_modal .add-title').addClass('hide');
              $('#contractor_modal .edit-title').removeClass('hide');
              $('#contractor_modal input[name="contractor"]').val(contractor)
              $('#contractor_modal input[name="email"]').val(email)
              $('#contractor_modal input[name="phone"]').val(phone)
              $('#contractor_modal input[name="address"]').val(address)
          }
      });
  });

  function manage_contractor(form) {
      var data = $(form).serialize();
      var url = form.action;
      $.post(url, data).done(function (response) {
          response = JSON.parse(response);
          if (response.success > 0) {
              $('.table-contractors').DataTable().ajax.reload();
              alert_float('success', response.message);
          } else {
              if (response.message != '') {
                  alert_float('warning', response.message);
              }
          }
          $('#contractor_modal').modal('hide');
      });
      return false;
  }
</script>
</body>
</html>
