<?php $from_name = "{company_name} | CRM"; ?>
<input type='hidden' name='rule_action' value='<?php echo FLEXIBLEWA_SEND_EMAIL_ACTION ?>' />
<div class="flexiblewa-email-form">
    <h4 class="tw-mb-4"><?php echo _l('flexiblewa_email'); ?></h4>
    <div class="form-group">
        <label for="to"><?php echo _l('flexiblewa_to'); ?></label>
        <select name="to[]" id="to" class="selectpicker" data-width="100%"
                                        data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>"
                                        multiple data-live-search="true">
            <option value="<?php echo $action_type ?>_user_email"><?php echo _l('flexiblewa_current_'.$action_type.'_user_email') ?></option>
            <?php foreach($members as $member) { ?>
                <option value="<?php echo $member['staffid'] ?>"><?php echo $member['firstname'] . ' ' . $member['lastname'] ?></option>
            <?php } ?>
        </select>
    </div>
    <div class="form-group">
        <label for="subject"><?php echo _l('flexiblewa_subject'); ?></label>
        <input type="text" class="form-control" id="subject" name="subject" placeholder="<?php echo _l('flexiblewa_subject'); ?>">
    </div>
    <!--from name-->
    <div class="form-group">
        <label for="from_name"><?php echo _l('flexiblewa_from_name'); ?></label>
        <input type="text" value="<?php echo $from_name; ?>" class="form-control" id="from_name" name="from_name" placeholder="<?php echo _l('flexiblewa_from_name'); ?>">
    </div>
    <!--body-->
    <div class="form-group">
        <label for="body"><?php echo _l('flexiblewa_body'); ?></label>
        <textarea class="form-control tinymce-manual" id="body" name="body" rows="10"></textarea>
    </div>

    <!--merge fields-->
    <p><?php echo _l('flexiblewa_available_merge_fields'); ?></p>
    <div class="alert alert-info">
        <?php if($action_type == 'lead') { ?>
        <ul>
            <li>{company_name}</li>
            <li>{lead_name}</li>
            <li>{lead_email}</li>
            <li>{lead_website}</li>
            <li>{lead_description}</li>
            <li>{lead_phonenumber}</li>
            <li>{lead_company}</li>
        </ul>
        <?php }elseif($action_type == 'project') { ?>
        <ul>
            <li>{project_name}</li>
            <li>{project_description}</li>
            <li>{project_start_date}</li>
            <li>{project_deadline}</li>
            <li>{project_status}</li>
            <li>{project_staff_link}</li>
            <li>{project_client_link}</li>
        </ul>
        <?php } ?>
    </div>
</div>