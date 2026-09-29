<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="tw-mb-2 sm:tw-mb-4">
                    <a href="<?php echo admin_url('appointly/services'); ?>" class="btn btn-primary">
                        <i class="fa fa-arrow-left tw-mr-1"></i>
                        <?php echo _l('appointly_service_back_to_list'); ?>
                    </a>
                </div>

                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="tw-mt-0 tw-font-semibold tw-text-lg tw-text-neutral-700">
                            <?php echo $title; ?>
                        </h4>
                        <hr class="tw-mt-4" />

                        <?php echo form_open($this->uri->uri_string(), ['id' => 'service-form']); ?>

                        <!-- Active and Name in one line -->
                        <div class="row">
                            <div class="col-md-12">

                                <?php if (isset($is_service_in_use) && $is_service_in_use): ?>
                                    <div class="alert alert-warning" style="padding: 8px; margin-bottom: 15px;">
                                        <i class="fa fa-exclamation-triangle"></i>
                                        <?php echo _l('appointly_service_in_use_warning'); ?>
                                    </div>
                                <?php endif; ?>

                                <div class="form-group pull-left" style="width: calc(100% - 100px);">
                                    <label class="control-label"><?php echo _l('appointly_service_name'); ?></label>
                                    <input type="text" class="form-control" name="name" value="<?php echo isset($service) && isset($service->name) ? $service->name : ''; ?>" required>
                                </div>
                                <div class="checkbox checkbox-primary pull-right" style="background: #f8f9fa; padding: 6px 22px; border: 1px solid #e0e3e7; border-radius: 6px; margin-top: 24px;">
                                    <!-- Hidden fallback input (ensures value is sent even if checkbox is disabled) -->
                                    <input type="hidden" name="active" value="<?php echo (isset($service) && isset($service->active) && $service->active == 1) ? '1' : '0'; ?>">

                                    <?php if (!isset($service_is_active_checked)) { ?>
                                        <input type="checkbox" name="active" id="active" value="1" checked>
                                    <?php } else { ?>
                                        <input type="checkbox" name="active" id="active" value="1"
                                            <?php echo (isset($service) && $service && $service->active == 1 ? 'checked' : ''); ?>
                                            <?php echo $is_service_in_use ? 'disabled' : ''; ?>>
                                    <?php } ?>
                                    <label style="margin-right: -13px;margin-left: 8px;" for="active"><?php echo _l('appointly_service_active'); ?></label>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-3">
                                <!-- Staff Selection -->
                                <div class="form-group">
                                    <label for="staff_members" class="control-label">
                                        <span class="text-danger">*</span>
                                        <?php echo _l('appointly_service_staff'); ?>
                                    </label>
                                    <select name="staff_members[]" id="staff_members" class="selectpicker"
                                        data-width="100%"
                                        data-live-search="true"
                                        multiple
                                        required>
                                        <?php
                                        $selected_staff_ids = [];
                                        if (isset($service) && isset($service->staff_members) && !empty($service->staff_members)) {
                                            // Handle different formats
                                            if (is_array($service->staff_members)) {
                                                // If it's already an array of staff members with details
                                                foreach ($service->staff_members as $staff_member) {
                                                    if (is_array($staff_member) && isset($staff_member['staff_id'])) {
                                                        $selected_staff_ids[] = (int)$staff_member['staff_id'];
                                                    } else if (!is_array($staff_member)) {
                                                        $selected_staff_ids[] = (int)$staff_member;
                                                    }
                                                }
                                            } else if (is_string($service->staff_members)) {
                                                // If it's a JSON string
                                                $decoded = json_decode($service->staff_members, true);
                                                if (is_array($decoded)) {
                                                    $selected_staff_ids = array_map('intval', $decoded);
                                                }
                                            }
                                        }

                                        foreach ($staff_members as $staff) { ?>
                                            <option value="<?php echo $staff['staffid']; ?>"
                                                <?php echo in_array($staff['staffid'], $selected_staff_ids) ? 'selected' : ''; ?>>
                                                <?php echo $staff['firstname'] . ' ' . $staff['lastname']; ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <!-- Directly render the price field instead of using the helper -->
                                <div class="form-group">
                                    <label for="price" class="control-label"><?php echo _l('appointly_service_price'); ?></label>
                                    <input type="number" id="price" name="price" class="form-control" value="<?php echo isset($service) && isset($service->price) ? $service->price : '0'; ?>" min="0" step="0.01" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="duration" class="control-label"><?php echo _l('appointly_service_duration'); ?></label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="duration" name="duration" value="<?php echo isset($service) && isset($service->duration) ? $service->duration : '60'; ?>" min="15" step="15" required>
                                        <span class="input-group-addon"><?php echo _l('minutes'); ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <?php echo render_color_picker('color', _l('appointly_service_color'), (isset($service) && isset($service->color) ? $service->color : '#28B8DA')); ?>
                            </div>
                        </div>
                        <hr>
                        <!-- Buffer Times -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="buffer_before"><?php echo _l('appointly_buffer_before'); ?></label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" name="buffer_before" id="buffer_before"
                                            value="<?php echo isset($service) && isset($service->buffer_before) ? $service->buffer_before : '0'; ?>" min="0">
                                        <span class="input-group-addon"><?php echo _l('minutes'); ?></span>
                                    </div>
                                    <small class="text-muted" id="buffer_before_help"><?php echo _l('appointly_buffer_before_help'); ?>
                                        <?php if (isset($service) && isset($service->buffer_before) && $service->buffer_before > 0): ?>
                                            (<?php echo $service->buffer_before; ?> <?php echo _l('appointly_duration_minutes'); ?>)
                                        <?php endif; ?>
                                    </small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="buffer_after"><?php echo _l('appointly_buffer_after'); ?></label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" name="buffer_after" id="buffer_after"
                                            value="<?php echo isset($service) && isset($service->buffer_after) ? $service->buffer_after : '0'; ?>" min="0">
                                        <span class="input-group-addon"><?php echo _l('minutes'); ?></span>
                                    </div>
                                    <small class="text-muted" id="buffer_after_help"><?php echo _l('appointly_buffer_after_help'); ?>
                                        <?php if (isset($service) && isset($service->buffer_after) && $service->buffer_after > 0): ?>
                                            (<?php echo $service->buffer_after; ?> <?php echo _l('appointly_duration_minutes'); ?>)
                                        <?php endif; ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <!-- Description -->
                        <?php echo render_textarea('description', 'appointly_service_description', isset($service) && isset($service->description) ? $service->description : ''); ?>
                        <hr>

                        <!-- Primary Provider Selection -->
                        <div class="form-group" id="primary_provider_wrapper">
                            <label for="primary_provider" class="control-label">
                                <?php echo _l('appointly_service_primary_provider'); ?>
                            </label>
                            <select name="primary_provider" id="primary_provider" class="selectpicker"
                                data-width="100%"
                                data-live-search="true">
                                <option value=""><?php echo _l('dropdown_non_selected_tex'); ?></option>
                                <?php
                                // Primary provider will be populated dynamically based on selected staff members
                                $primary_provider_id = (isset($service) && $service && isset($service->primary_provider)) ? $service->primary_provider : null;

                                foreach ($staff_members as $staff) {
                                    if (in_array($staff['staffid'], $selected_staff_ids)) {
                                        $is_primary = false;
                                        if (isset($service) && $service && isset($service->staff_members) && is_array($service->staff_members)) {
                                            foreach ($service->staff_members as $member) {
                                                if (
                                                    isset($member['staff_id'], $member['is_primary']) && is_array($member) && $member['staff_id'] == $staff['staffid'] && $member['is_primary']
                                                ) {
                                                    $is_primary = true;
                                                    break;
                                                }
                                            }
                                        }

                                        // Use the is_primary flag or fall back to primary_provider_id
                                        $selected = $is_primary || $staff['staffid'] == $primary_provider_id ? 'selected' : '';

                                        echo '<option value="' . $staff['staffid'] . '" ' . $selected . '>' .
                                            $staff['firstname'] . ' ' . $staff['lastname'] . '</option>';
                                    }
                                }
                                ?>
                            </select>
                        </div>

                        <div class="btn-bottom-toolbar text-right">
                            <button type="submit" class="btn btn-primary"><?php echo _l('submit'); ?></button>
                        </div>

                        <?php echo form_close(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>
<script>
    // Pass PHP translations to JavaScript
    var appointlyLang = {
        selectProvider: "<?php echo addslashes(_l('appointly_select_provider')); ?>",
        durationMinutes: "<?php echo addslashes(_l('appointly_duration_minutes')); ?>",
        appointly_service_in_use_warning: "<?php echo addslashes(_l('appointly_service_in_use_warning')); ?>",
        submit: "<?php echo addslashes(_l('submit')); ?>",
        // Validation messages
        serviceNameRequired: "<?php echo addslashes(_l('service_name_required')); ?>",
        staffMembersRequired: "<?php echo addslashes(_l('appointly_service_providers_required')); ?>",
        priceRequired: "<?php echo addslashes(_l('service_price_required')); ?>",
        priceInvalid: "<?php echo addslashes(_l('appointly_service_price_invalid')); ?>",
        durationRequired: "<?php echo addslashes(_l('service_duration_required')); ?>",
        durationInvalid: "<?php echo addslashes(_l('appointly_service_duration_invalid')); ?>"
    };

    // Set primary provider data attribute for JavaScript to use
    <?php if (isset($service->primary_provider) && $service): ?>
        $('#primary_provider').data('current', '<?php echo $service->primary_provider; ?>');
    <?php endif; ?>
</script>
<script src="<?php echo module_dir_url('appointly') ?>assets/js/services.js?v=<?= time(); ?>"></script>
