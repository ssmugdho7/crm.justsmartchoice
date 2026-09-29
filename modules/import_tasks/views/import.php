<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
   <div class="content">
      <div class="row">
         <div class="col-md-12">
            <div class="panel_s">
               <div class="panel-body">
                  <?php
                  echo form_open($this->uri->uri_string());
                  echo form_hidden('download_sample', 'true');
                  ?>
                  <button type="submit" class="btn btn-success"><?= _l('Download Sample'); ?></button>
                  <a href="/admin/import_tasks">
                     <button type="button" style="float:right" class="btn btn-back"><?php echo _l('Show import history'); ?></button>
                  </a>
                  <hr />
                  <?= form_close(); ?>
                  <?php echo $this->import->maxInputVarsWarningHtml(); ?>
                  <?php echo $this->import->importGuidelinesInfoHtml(); ?>
                  <div style="overflow:auto"><?php echo $this->import->createSampleTableHtml(); ?></div>
                  <div class="row">
                     <div class="col-md-4 mtop15">
                        <?php echo form_open_multipart($this->uri->uri_string(), array('id' => 'import_form')); ?>
                        <?php echo form_hidden('clients_import', 'true'); ?>
                        <?php echo render_input('file_csv', 'choose_csv_file', '', 'file'); ?>
                        <div class="form-group">
                           <button type="button" class="btn btn-info import btn-import-submit"><?php echo _l('import'); ?></button>
                        </div>
                        <?php echo form_close(); ?>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
<?php $this->load->view('admin/clients/client_group'); ?>
<?php init_tail(); ?>
<script src="<?php echo base_url('assets/plugins/jquery-validation/additional-methods.min.js'); ?>"></script>
<script>
   $(function() {
      appValidateForm($('#import_form'), {
         file_csv: {
            required: true,
            extension: "csv"
         },
         source: 'required',
         status: 'required'
      });
   });
</script>
</body>

</html>