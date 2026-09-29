<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <?php if (has_permission('drawings', '', 'create')) { ?>
                          <div class="_buttons">
                              <a href="<?php echo admin_url('engineering_projects/drawings/drawing'); ?>" class="btn btn-info pull-left display-block"><?php echo _l('new_drawing'); ?></a>
                              <a href="<?php echo admin_url('engineering_projects'); ?>" class="mleft5 btn btn-default pull-right display-block"><?php echo _l('back_to_ingredient'); ?></a>
                          </div>
                          <div class="clearfix"></div>
                          <hr class="hr-panel-heading" />
                        <?php } ?>
                        <?php
                        render_datatable(array(
                            _l('drawing_name'),
                            _l('drawing_type'),
                            _l('drawing_draf'),
                            _l('drawing_final'),
                            _l('options')
                                ), 'drawings');
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="drawing_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">
                    <span class="edit-title"><?php echo _l('drawing_edit_title'); ?></span>
                    <span class="add-title"><?php echo _l('drawing_add_title'); ?></span>
                </h4>
            </div>
            <?php echo form_open_multipart('admin/engineering_projects/drawings/drawing', array('id' => 'drawing_form')); ?>
            <?php echo form_hidden('id'); ?>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <?php echo render_input('type', 'drawing_type', ''); ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <label for="type" class="control-label"><?php echo _l('drawing_file'); ?></label>
                        <input type="file" name="file" class="form-control">
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

      initDataTable('.table-drawings', window.location.href, [0], [0], undefined, []);

      appValidateForm($('form'), {
          type: {
              required: true,
        //       remote: {
        //           url: admin_url + "engineering_projects/drawings/drawing_exists",
        //           type: 'post',
        //           data: {
        //               id: function () {
        //                   return $('input[name="id"]').val();
        //               }
        //           }
        //       }
          }
      }, manage_drawing);

      // $('#drawing_modal').on('show.bs.modal', function (event) {
      //     var button = $(event.relatedTarget)
      //     var id = button.data('id');
      //     $(this).find('button[type="submit"]').prop('disabled', false);
      //     $('#drawing_modal input[name="type"]').val('').prop('disabled', false);
      //     $('#drawing_modal input[name="id"]').val('')
      //     $('#drawing_modal .add-title').removeClass('hide');
      //     $('#drawing_modal .edit-title').addClass('hide');
      //     if (typeof (id) !== 'undefined') {
      //         $('input[name="id"]').val(id);
      //         var type = $(button).parents('tr').find('td').eq(0).text();
      //
      //         $('#drawing_modal .add-title').addClass('hide');
      //         $('#drawing_modal .edit-title').removeClass('hide');
      //         $('#drawing_modal input[name="type"]').val(type)
      //     }
      // });
  });

  function manage_drawing(form) {
      var data = $(form).serialize();
      var url = form.action;
      $.post(url, data).done(function (response) {
          response = JSON.parse(response);
          if (response.success > 0) {
              $('.table-drawings').DataTable().ajax.reload();
              alert_float('success', response.message);
          } else {
              if (response.message != '') {
                  alert_float('warning', response.message);
              }
          }
          $('#drawing_modal').modal('hide');
      });
      return false;
  }
</script>
</body>
</html>
