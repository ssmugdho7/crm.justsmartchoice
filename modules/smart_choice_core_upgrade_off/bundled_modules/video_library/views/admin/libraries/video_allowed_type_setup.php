<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-6">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin">  <?php echo _l('vl_allowed_type'); ?></h4>
                        <hr class="hr-panel-heading" />
                        <?php
                        $vl_allowed_type = get_option('vl_allowed_type');
                        echo form_open_multipart($this->uri->uri_string(), array('id' => 'allowed_type'));
                        ?>
                        <?php
                        echo render_input('vl_allowed_type', _l('vl_allowed_file_type'), $vl_allowed_type, '', ['placeholder' => _l('vl_allowed_file_type_name')]);
                
                        ?>
                        <button type="submit" class="btn btn-info pull-right save_vl_btn" data-><?php echo _l('submit'); ?></button>
                        <?php echo form_close(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
    function validate_form() {
        <?php if (!isset($video) && empty($video)) { ?>
            appValidateForm($('#allowed_type'), {
                vl_allowed_type: 'required',   
            });
        <?php } else { ?>
            appValidateForm($('#allowed_type'), {
                vl_allowed_type: 'required',
            });
        <?php } ?>
    }
    $(function() {
        $('body').on('click', 'button.save_vl_btn', function() {
           validate_form();
            $('form#allowed_type').submit();
        });
    }); 
</script>
</body>

</html>