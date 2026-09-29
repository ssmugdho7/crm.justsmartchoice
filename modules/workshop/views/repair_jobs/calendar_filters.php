<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div id="calendar_filters" style="<?php if(!$this->input->post('calendar_filters')){ echo 'display:none;'; } ?>">
    <?php echo form_open(); ?>
    <?php echo form_hidden('calendar_filters',true); ?>
    <div class="row">
        
        <div class="col-md-3">
            <div class="checkbox">
                <input type="checkbox" value="1" name="Booked_In" id="cf_Booked_In"<?php if($this->input->post('Booked_In')){echo ' checked';} ?>>
                <label for="cf_Booked_In"><?php echo _l('wshop_Booked_In'); ?></label>
            </div>
            <div class="checkbox">
                <input type="checkbox" value="1" name="In_Progress" id="cf_In_Progress"<?php if($this->input->post('In_Progress')){echo ' checked';} ?>>
                <label for="cf_In_Progress"><?php echo _l('wshop_In_Progress'); ?></label>
            </div>
            <div class="checkbox">
                <input type="checkbox" value="1" name="Cancelled" id="cf_Cancelled"<?php if($this->input->post('Cancelled')){echo ' checked';} ?>>
                <label for="cf_Cancelled"><?php echo _l('wshop_Cancelled'); ?></label>
            </div>
            <div class="checkbox">
                <input type="checkbox" value="1" name="Waiting_For_Parts" id="cf_Waiting_For_Parts"<?php if($this->input->post('Waiting_For_Parts')){echo ' checked';} ?>>
                <label for="cf_Waiting_For_Parts"><?php echo _l('wshop_Waiting_For_Parts'); ?></label>
            </div>
            <div class="checkbox">
                <input type="checkbox" value="1" name="Job_Complete" id="cf_Job_Complete"<?php if($this->input->post('Job_Complete')){echo ' checked';} ?>>
                <label for="cf_Job_Complete"><?php echo _l('wshop_Job_Complete'); ?></label>
            </div>
        </div>
        <div class="col-md-3">
            <div class="checkbox">
                <input type="checkbox" value="1" name="Customer_Notified" id="cf_Customer_Notified"<?php if($this->input->post('Customer_Notified')){echo ' checked';} ?>>
                <label for="cf_Customer_Notified"><?php echo _l('wshop_Customer_Notified'); ?></label>
            </div>
            <div class="checkbox">
                <input type="checkbox" value="1" name="Complete_Awaiting_Finalise" id="cf_Complete_Awaiting_Finalise"<?php if($this->input->post('Complete_Awaiting_Finalise')){echo ' checked';} ?>>
                <label for="cf_Complete_Awaiting_Finalise"><?php echo _l('wshop_Complete_Awaiting_Finalise'); ?></label>
            </div>
            <div class="checkbox">
                <input type="checkbox" value="1" name="Finalised" id="cf_Finalised"<?php if($this->input->post('Finalised')){echo ' checked';} ?>>
                <label for="cf_Finalised"><?php echo _l('wshop_Finalised'); ?></label>
            </div>
            <div class="checkbox">
                <input type="checkbox" value="1" name="Waiting_For_User_Approval" id="cf_Waiting_For_User_Approval"<?php if($this->input->post('Waiting_For_User_Approval')){echo ' checked';} ?>>
                <label for="cf_Waiting_For_User_Approval"><?php echo _l('wshop_Waiting_For_User_Approval'); ?></label>
            </div>
            

        </div>
        <div class="col-md-3 text-right">
            <a class="btn btn-default" href="<?php echo site_url($this->uri->uri_string()); ?>"><?php echo _l('clear'); ?></a>
            <button class="btn btn-success" type="submit"><?php echo _l('apply'); ?></button>
        </div>

    </div>
    <hr class="mbot15" />
    <div class="clearfix"></div>
    <?php echo form_close(); ?>
</div>
