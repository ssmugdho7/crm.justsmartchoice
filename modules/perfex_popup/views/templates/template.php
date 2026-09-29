<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
<div class="content">
   <div class="row">
      <div class="col-md-12" id="block-add-edit-wrapper">
         <div class="row">
            <div class="col-md-8">
               <div class="panel_s">
                  <?php echo form_open_multipart($this->uri->uri_string(),array('id'=>'template_form')) ;?>
                  <div class="panel-body">
                     <h4 class="no-margin">
                        <?php echo html_escape($title); ?>
                     </h4>
                     <hr class="hr-panel-heading" />
                     <?php $value = (isset($item) ? $item->name : ''); ?>
                     <?php $attrs = (isset($item) ? array() : array('autofocus'=>true)); ?>
                     <?php echo render_input('name','name',$value,'text',$attrs); ?>

                      <?php echo render_input('thumbnail','thumbnail_(950x600)','','file'); ?>
                      <?php if(isset($item) && $item->thumbnail){ ?>
                          <img src="<?php echo base_url(PERFEX_POPUP_UPLOAD_PATH.'/popup_thumb_templates/'. $item->thumbnail); ?>" class="img img-responsive" width="200">
                      <?php } ?>

                     <?php $attrs = (isset($item) ? array() : array('autofocus'=>true)); ?>
                     
                     <?php $value = (isset($item) ? $item->width : 650); ?>
                     <?php echo render_input('width','width',$value,'number',$attrs); ?>

                     <?php $value = (isset($item) ? $item->height : 460); ?>
                     <?php echo render_input('height','height',$value,'number',$attrs); ?>

                     <div class="checkbox checkbox-primary">
                          <input type="checkbox" name="active" id="active" <?php if(isset($item)){if($item->active == 1){echo 'checked';} } ?>>
                          <label for="active"><?php echo _l('active'); ?></label>
                      </div>
                     <button type="submit" name="action" value="save" class="btn btn-info pull-right mright5"><?php echo _l('save'); ?></button>
                     <button type="submit" name="action" value="save_and_builder" class="btn btn-info pull-right mright5"><?php echo _l('save_and_builder'); ?></button>
                  </div>
                  <?php echo form_close(); ?>
               </div>
            </div>

         </div>
      </div>
   </div>
</div>
<?php init_tail(); ?>
<script src="<?php echo base_url(PERFEX_POPUP_ASSETS_PATH.'/templates/js/template.js'); ?>"></script>
</body>
</html>
