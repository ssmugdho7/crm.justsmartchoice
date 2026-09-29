<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php 
$sources  = get_instance()->leads_model->get_source();
$statuses = get_instance()->leads_model->get_status();
$staff    = get_instance()->staff_model->get('', ['active' => 1]);
?>
<div class="row">
    <div class="col-md-12">
        <h4><?php echo _l('ultimatepos_settings_heading'); ?></h4>
        <hr>
        <div class="form-group">
            <label for="ultimatepos_name"><?php echo _l('ultimatepos_name'); ?></label>
            <input type="text" name="settings[ultimatepos_name]" id="ultimatepos_name" class="form-control" value="<?php echo get_option('ultimatepos_name'); ?>" required />
        </div>
        <div class="form-group">
            <label for="ultimatepos_api_url"><?php echo _l('ultimatepos_api_url'); ?></label>
            <input type="text" name="settings[ultimatepos_api_url]" id="ultimatepos_api_url" class="form-control" value="<?php echo get_option('ultimatepos_api_url'); ?>" required />
        </div>
        <div class="form-group">
            <label for="ultimatepos_client_id"><?php echo _l('ultimatepos_client_id'); ?></label>
            <input type="text" name="settings[ultimatepos_client_id]" id="ultimatepos_client_id" class="form-control" value="<?php echo get_option('ultimatepos_client_id'); ?>" required />
        </div>
        <div class="form-group">
            <label for="ultimatepos_client_secret"><?php echo _l('ultimatepos_client_secret'); ?></label>
            <input type="text" name="settings[ultimatepos_client_secret]" id="ultimatepos_client_secret" class="form-control" value="<?php echo get_option('ultimatepos_client_secret'); ?>" required />
        </div>
        <div class="form-group">
            <label for="ultimatepos_username"><?php echo _l('ultimatepos_username'); ?></label>
            <input type="text" name="settings[ultimatepos_username]" id="ultimatepos_username" class="form-control" value="<?php echo get_option('ultimatepos_username'); ?>" required />
        </div>
        <div class="form-group">
            <label for="ultimatepos_password"><?php echo _l('ultimatepos_password'); ?></label>
            <input type="password" name="settings[ultimatepos_password]" id="ultimatepos_password" class="form-control" value="<?php echo get_option('ultimatepos_password'); ?>" required />
        </div>
        <div class="form-group">
            <label for="sync_interval"><?php echo _l('ultimatepos_sync_interval'); ?></label>
            <input type="number" name="settings[ultimatepos_sync_interval]" id="sync_interval" class="form-control" value="<?php echo get_option('ultimatepos_sync_interval'); ?>" min="1" required />
        </div>
        <div class="form-group">
            <label for="ultimatepos_fetch_limit"><?php echo _l('ultimatepos_fetch_limit'); ?></label>
            <input type="number" name="settings[ultimatepos_fetch_limit]" id="ultimatepos_fetch_limit" class="form-control" value="<?php echo get_option('ultimatepos_fetch_limit'); ?>" min="1" required />
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-3">
        <label><?php echo _l('ultimatepos_lead_status'); ?></label>
        <select class="selectpicker" data-width="100%" name="settings[ultimatepos_lead_status]" required>
            <?php foreach ($statuses as $status) { ?>
                <option value="<?php echo $status['id']; ?>" <?php echo (get_option('ultimatepos_lead_status') == $status['id']) ? 'selected' : ''; ?>><?php echo $status['name']; ?></option>
            <?php } ?>
        </select>
    </div>
    <div class="col-md-3">
        <label><?php echo _l('ultimatepos_lead_source'); ?></label>
        <select class="selectpicker" data-width="100%" name="settings[ultimatepos_lead_source]" required>
            <?php foreach ($sources as $source) { ?>
                <option value="<?php echo $source['id']; ?>" <?php echo (get_option('ultimatepos_lead_source') == $source['id']) ? 'selected' : ''; ?>><?php echo $source['name']; ?></option>
            <?php } ?>
        </select>
    </div>
    <div class="col-md-3">
        <label><?php echo _l('ultimatepos_lead_assigned'); ?></label>
        <select class="selectpicker" data-width="100%" name="settings[ultimatepos_lead_assigned]" required>
            <?php foreach ($staff as $staff_member) { ?>
                <option value="<?php echo $staff_member['staffid']; ?>" <?php echo (get_option('ultimatepos_lead_assigned') == $staff_member['staffid']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($staff_member['firstname'] . ' ' . $staff_member['lastname']); ?></option>
            <?php } ?>
        </select>
    </div>
</div>
