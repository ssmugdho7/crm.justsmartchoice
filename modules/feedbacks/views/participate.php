<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php $design = null; if ($this->db->table_exists(db_prefix().'feedbacks_design')) { $design = $this->db->where('feedbackid', $feedback->feedbackid)->get(db_prefix().'feedbacks_design')->row(); } $designSettings = $design ? (json_decode($design->settings_json, true) ?: []) : []; ?>
<?php $scPrimary = preg_match('/^#[0-9a-fA-F]{6}$/', $designSettings['primary_color'] ?? '') ? $designSettings['primary_color'] : '#2f7d32'; $scBg = preg_match('/^#[0-9a-fA-F]{6}$/', $designSettings['background_color'] ?? '') ? $designSettings['background_color'] : '#ffffff'; $scRadius = max(0,min(32,(int)($designSettings['corner_radius'] ?? 12))); ?>
<style>.feedback{background:<?php echo $scBg; ?>;border-radius:<?php echo $scRadius; ?>px;padding:24px}.feedback h2{color:<?php echo $scPrimary; ?>}.feedback .btn-success{background:<?php echo $scPrimary; ?>;border-color:<?php echo $scPrimary; ?>}.feedback{font-size:<?php echo max(12,min(22,(int)($designSettings['font_size'] ?? 16))); ?>px}</style>
<div class="col-md-8 col-md-offset-2 feedback">
   <div class="row">
      <?php echo form_open($this->uri->uri_string(),array('id'=>'feedback_form')); ?>
      <div class="col-md-12">
         <?php
         if ($design && !empty($design->header_html)) { echo $design->header_html; } else { ?><div id="company-logo"><?php get_company_logo(); ?></div><?php }
         ?>
         <h2 class="bold"><?php echo $feedback->subject; ?></h2>
         <hr />
         <p><?php echo $feedback->viewdescription; ?></p>
         <?php if(count($feedback->questions) > 0){
            $question_area = '<ul class="list-unstyled mtop25">';
            foreach($feedback->questions as $question){
             $question_area .= '<li>';
             $question_area .= '<div class="form-group">';
             $question_area .= '<label class="control-label" for="'.$question['questionid'].'">'.html_escape($question['question']).'</label>';
             $advancedType = $question['advanced_type'] ?? $question['boxtype'];
             $advancedOptions = $question['advanced_options'] ?? [];
             $advancedSettings = $question['advanced_settings'] ?? [];
             if ($advancedType === 'dropdown') {
                $question_area .= '<select class="form-control" name="question['.$question['questionid'].'][]" data-required="'.$question['required'].'"><option value="">'. _l('select') .'</option>';
                foreach ($advancedOptions as $option) { $question_area .= '<option value="'.html_escape($option).'">'.html_escape($option).'</option>'; }
                $question_area .= '</select>';
             } else if ($advancedType === 'toggle') {
                $question_area .= '<select class="form-control" name="question['.$question['questionid'].'][]" data-required="'.$question['required'].'"><option value="">'. _l('select') .'</option><option value="Yes">Yes</option><option value="No">No</option></select>';
             } else if (in_array($advancedType, ['rating','linear_scale'], true)) {
                $min = (int)($advancedSettings['min'] ?? 1); $max = (int)($advancedSettings['max'] ?? 5);
                $question_area .= '<div class="btn-group" data-toggle="buttons">';
                for ($v=$min; $v<=$max; $v++) { $question_area .= '<label class="btn btn-default"><input type="radio" name="question['.$question['questionid'].'][]" value="'.$v.'" data-required="'.$question['required'].'"> '.$v.'</label>'; }
                $question_area .= '</div>';
             } else if ($advancedType === 'textarea' || $question['boxtype'] == 'textarea') {
              $question_area .= '<textarea class="form-control" rows="6" name="question['.$question['questionid'].'][]" data-for="'.$question['questionid'].'" id="'.$question['questionid'].'" data-required="'.$question['required'].'"></textarea>';
            } else if($question['boxtype'] == 'checkbox' || $question['boxtype'] == 'radio'){
              $question_area .= '<div class="row box chk" data-boxid="'.$question['boxid'].'">';
              foreach($question['box_descriptions'] as $box_description){
               $question_area .= '<div class="col-md-12">';
               $question_area .= '<div class="'.$question['boxtype'].' '.$question['boxtype'].'-default">';
               $question_area .=
               '<input type="'.$question['boxtype'].'" data-for="'.$question['questionid'].'"
               name="selectable['.$question['boxid'].']['.$question['questionid'].'][]" value="'.$box_description['questionboxdescriptionid'].'" data-required="'.$question['required'].'" id="chk_'.$question['boxtype'].'_'.$box_description['questionboxdescriptionid'].'"/>';
               $question_area .= '
               <label for="chk_'.$question['boxtype'].'_'.$box_description['questionboxdescriptionid'].'">
               '.$box_description['description'].'
               </label>';
               $question_area .= '</div>';
               $question_area .= '</div>';
             }
                        // end box row
             $question_area .= '</div>';
            } else {
            $htmlType = ['number'=>'number','currency'=>'number','percentage'=>'number','date'=>'date','time'=>'time','email'=>'email','phone'=>'tel','url'=>'url'][ $advancedType ] ?? 'text';
            $step = in_array($advancedType,['currency','percentage'],true) ? '0.01' : '1';
            $question_area .= '<div class="input-group"><input type="'.$htmlType.'" step="'.$step.'" data-for="'.$question['questionid'].'" class="form-control sc-numeric-input" name="question['.$question['questionid'].'][]" id="'.$question['questionid'].'" data-required="'.$question['required'].'">';
            if ($advancedType === 'number' || $advancedType === 'currency' || $advancedType === 'percentage') { $question_area .= '<span class="input-group-btn"><button type="button" class="btn btn-default sc-open-keypad" data-target="'.$question['questionid'].'" aria-label="Open numeric keypad"><i class="fa fa-calculator"></i></button></span>'; }
            $question_area .= '</div>';
            }
            $question_area .= '</div>';
            $question_area .= '<hr /></li>';
            }
            $question_area .= '</ul>';
            echo $question_area; ?>
         <div class="row">
            <div class="col-md-12 text-center">
               <button type="submit" class="btn btn-success" id="submit"><?php echo _l('feedback_submit'); ?></button>
            </div>
         </div>
         <?php } else { ?>
         <p class="no-margin text-center bold mtop20"><?php echo _l('feedback_no_questions'); ?></p>
         <?php } ?>
      </div>
      <?php echo form_close(); ?>
   </div>
</div>
</div>
<div class="modal fade" id="scNumericKeypad" tabindex="-1"><div class="modal-dialog modal-sm"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button><h4><?php echo _l('fb_numeric_keypad'); ?></h4></div><div class="modal-body"><input type="text" id="scKeypadDisplay" class="form-control input-lg text-center" readonly><div class="row mtop10"><?php foreach ([['7','8','9'],['4','5','6'],['1','2','3'],['-','0','.']] as $keyRow) { foreach($keyRow as $keyValue) { ?><div class="col-xs-4 mtop5"><button type="button" class="btn btn-default btn-lg btn-block sc-keypad-key" data-key="<?php echo $keyValue; ?>"><?php echo $keyValue; ?></button></div><?php } } ?><div class="col-xs-6 mtop5"><button type="button" class="btn btn-warning btn-block" id="scKeypadClear"><?php echo _l('fb_clear'); ?></button></div><div class="col-xs-6 mtop5"><button type="button" class="btn btn-default btn-block" id="scKeypadBack">⌫</button></div></div></div><div class="modal-footer"><button type="button" class="btn btn-primary" id="scKeypadDone"><?php echo _l('fb_use_value'); ?></button></div></div></div></div>
<script>
   $(function(){
     $('#feedback_form').appFormValidator();
     var scKeypadTarget=null; $(document).on('click','.sc-keypad-key',function(){var v=$('#scKeypadDisplay').val(),k=String($(this).data('key'));if(k==='-'&&v.length===0)v='-';else if(k==='.'&&v.indexOf('.')===-1)v+=k;else if(/[0-9]/.test(k))v+=k;$('#scKeypadDisplay').val(v);});$('#scKeypadClear').on('click',function(){$('#scKeypadDisplay').val('');});$('#scKeypadBack').on('click',function(){$('#scKeypadDisplay').val($('#scKeypadDisplay').val().slice(0,-1));});$('#scKeypadDone').on('click',function(){if(scKeypadTarget)scKeypadTarget.val($('#scKeypadDisplay').val()).trigger('change');$('#scNumericKeypad').modal('hide');}); $(document).on('click','.sc-open-keypad',function(){ scKeypadTarget=$('#'+$(this).data('target')); $('#scKeypadDisplay').val(scKeypadTarget.val()||''); $('#scNumericKeypad').modal('show'); });
     var feedback_fields_required = $('#feedback_form').find('[data-required="1"]');
     $.each(feedback_fields_required, function() {
       $(this).rules("add", {
         required: true
       });
       var name = $(this).data('for');
       var label = $(this).parents('.form-group').find('[for="' + name + '"]');
       if (label.length > 0) {
         if (label.find('.req').length == 0) {
           label.prepend(' <small class="req text-danger">* </small>');
         }
       }
     });
   });
   <?php if($this->input->get('participated')) { ?>
     $('body').find('input, textarea, #submit').prop('disabled',true);
   <?php } ?>
</script>
