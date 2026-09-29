<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <?php if (has_permission('documents', '', 'create')) { ?>
                          <div class="_buttons">
                              <a href="<?php echo admin_url('engineering_projects/documents/document'); ?>" class="btn btn-info pull-left display-block"><?php echo _l('new_document'); ?></a>
                              <a href="<?php echo admin_url('engineering_projects'); ?>" class="mleft5 btn btn-default pull-right display-block"><?php echo _l('back_to_ingredient'); ?></a>
                          </div>
                          <div class="clearfix"></div>
                          <hr class="hr-panel-heading" />
                        <?php } ?>
                        <?php
                        render_datatable(array(
                            _l('document_name'),
                            _l('document_noc'),
                            _l('document_eng_letter'),
                            _l('document_site_insp'),
                            _l('document_permit'),
                            _l('options')
                                ), 'eng_documents');
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
  $(function () {

      initDataTable('.table-eng_documents', window.location.href, [0], [0], undefined, []);
  });

</script>
</body>
</html>
