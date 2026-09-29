<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
  <div class="content">
    <div class="row">
      <div class="col-md-8 col-md-offset-2">
        <div class="panel_s">
          <div class="panel-body">
            <h4 class="no-margin"><?php echo _l('engineering_projects'); ?> – <?php echo _l('settings'); ?></h4>
            <hr class="hr-panel-heading" />
            <?php echo form_open(admin_url('engineering_projects/settings')); ?>

            <h4><?php echo _l('Required fields'); ?></h4>
            <p class="text-muted"><?php echo _l('Select which fields must be required in each form.'); ?></p>

            <div class="row">
              <div class="col-md-4">
                <h5><?php echo _l('Projects'); ?></h5>
                <?php
                  $proj_fields = ['customer_id','start_date','end_date','name'];
                  foreach ($proj_fields as $f) {
                    $checked = !empty($required['projects']) && in_array($f, $required['projects']) ? 'checked' : '';
                    echo '<div class="checkbox"><label><input type="checkbox" name="required_fields[projects][]" value="'.$f.'" '.$checked.'> '.htmlspecialchars($f).'</label></div>';
                  }
                ?>
              </div>
              <div class="col-md-4">
                <h5><?php echo _l('Documents'); ?></h5>
                <?php
                  $doc_fields = ['name','noc','eng_letter','site_insp','permit'];
                  foreach ($doc_fields as $f) {
                    $checked = !empty($required['documents']) && in_array($f, $required['documents']) ? 'checked' : '';
                    echo '<div class="checkbox"><label><input type="checkbox" name="required_fields[documents][]" value="'.$f.'" '.$checked.'> '.htmlspecialchars($f).'</label></div>';
                  }
                ?>
              </div>
              <div class="col-md-4">
                <h5><?php echo _l('Drawings'); ?></h5>
                <?php
                  $drw_fields = ['name','type','file'];
                  foreach ($drw_fields as $f) {
                    $checked = !empty($required['drawings']) && in_array($f, $required['drawings']) ? 'checked' : '';
                    echo '<div class="checkbox"><label><input type="checkbox" name="required_fields[drawings][]" value="'.$f.'" '.$checked.'> '.htmlspecialchars($f).'</label></div>';
                  }
                ?>
              </div>
            </div>

            <hr/>
            <h4><?php echo _l('Custom option fields'); ?></h4>
            <p class="text-muted"><?php echo _l('Add extra project fields (stored as JSON). Enter keys separated by commas or new lines.'); ?></p>
            <?php
              $val = !empty($option_fields) ? implode("\n", $option_fields) : '';
              echo render_textarea('option_fields', _l('Field keys'), $val, [], [], 'mtop15', 'tinymce');
            ?>

            <div class="text-right mtop20">
              <button type="submit" class="btn btn-primary"><?php echo _l('save'); ?></button>
            </div>
            <?php echo form_close(); ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php init_tail(); ?>
</body>
</html>
