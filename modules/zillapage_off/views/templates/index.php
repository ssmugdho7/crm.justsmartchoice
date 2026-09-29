<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); 
?>
<div id="wrapper"block>
    <div class="content">
        <div class="row">
                <div class="panel_s">

                    <div class="panel-body">
                      <div class="row">
                        <div class="col-md-8">
                            <div class="_buttons">
                                <a href="<?php echo admin_url('zillapage/templates/template'); ?>" class="btn btn-info btn-sm"><?php echo _l('new_template'); ?></a>
<a href="<?php echo admin_url('zillapage/templates/export_csv'); ?>" class="btn btn-success btn-sm"><?php echo _l('export'); ?></a>
<a href="<?php echo admin_url('zillapage/templates/sample_header'); ?>" class="btn btn-default btn-sm"><?php echo _l('zillapage_sample_header'); ?></a>
<button type="button" class="btn btn-warning btn-sm" onclick="document.getElementById('zillapage-import').click()"><?php echo _l('import'); ?></button>
<?php echo form_open_multipart(admin_url('zillapage/templates/import_csv'),['style'=>'display:inline']); ?><input id="zillapage-import" type="file" name="import_file" accept=".csv" style="display:none" onchange="this.form.submit()"><?php echo form_close(); ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                           
                        </div>
                      </div>
                  <div class="clearfix mbot20"></div>
                       <?php render_datatable(array(
                        _l('thumb'),
                        _l('name'),
                        _l('active'),
                        _l('created_at'),
                        _l('updated_at'),
                        ),'templates'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>
<script src="<?php echo base_url(ZILLAPAGE_ASSETS_PATH.'/templates/js/index.js'); ?>"></script>
</body>
</html>
