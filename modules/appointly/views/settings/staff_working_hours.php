<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <?php if ($staff && (!empty($staff->firstname) || !empty($staff->lastname))): ?>
                            <h4><?= _l('appointly_staff_working_hours'); ?> - <?= $staff->firstname . ' ' . $staff->lastname; ?></h4>
                        <?php else: ?>
                            <h4><?= _l('appointly_staff_working_hours'); ?></h4>
                        <?php endif; ?>
                        <hr class="hr-panel-heading">

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <a href="<?= admin_url('appointly/services/company_schedule'); ?>" class="btn btn-info pull-right">
                                    <i class="fa fa-building"></i> <?= _l('appointly_company_schedule'); ?>
                                </a>
                            </div>
                        </div>

                        <?php if (is_admin() || get_staff_user_id() == $staff->staffid): ?>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="control-label"><?= _l('appointly_select_staff'); ?></label>
                                        <select class="selectpicker" data-width="100%" data-none-selected-text="<?= _l('dropdown_non_selected_tex'); ?>" onchange="window.location.href = admin_url + 'appointly/services/staff_working_hours/' + this.value;">
                                            <?php foreach ($staff_members as $member): ?>
                                                <option value="<?= $member['staffid']; ?>" <?= $member['staffid'] == $staff->staffid ? 'selected' : ''; ?>>
                                                    <?= $member['firstname'] . ' ' . $member['lastname']; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <p class="text-muted"><?= _l('appointly_staff_working_hours_info'); ?></p>

                        <div class="alert alert-info alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <p><i class="fa fa-info-circle"></i> <?= _l('appointly_note'); ?>: <?= _l('working_hours_time_intervals_note'); ?></p>
                        </div>
                        <div class="alert alert-warning alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <p><i class="fa fa-link"></i> <?= _l('appointly_company_schedule_sync_help'); ?></p>
                        </div>

                        <?php echo form_open(admin_url('appointly/services/staff_working_hours/' . $staff->staffid), ['id' => 'staff-working-hours-form']); ?>

                        <?php
                        // If no working hours set yet, create default values based on company schedule
                        $CI = &get_instance();
                        $CI->load->model('appointly/appointly_model');
                        $company_schedule = $CI->appointly_model->get_company_schedule();

                        $weekdays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                        foreach ($weekdays as $day) {
                            if (!isset($working_hours[$day])) {
                                $working_hours[$day] = [
                                    'start_time' => isset($company_schedule[$day]['start_time']) ? $company_schedule[$day]['start_time'] : '09:00:00',
                                    'end_time' => isset($company_schedule[$day]['end_time']) ? $company_schedule[$day]['end_time'] : '17:00:00',
                                    'is_available' => isset($company_schedule[$day]['is_enabled']) ? $company_schedule[$day]['is_enabled'] : in_array($day, ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'])
                                ];
                            }
                        }
                        ?>

                        <div class="table-responsive">
                            <table class="table table-hover table-bordered">
                                <thead>
                                    <tr>
                                        <th><?= _l('appointly_day'); ?></th>
                                        <th><?= _l('appointly_available'); ?></th>
                                        <th><?= _l('appointly_start_time'); ?></th>
                                        <th><?= _l('appointly_end_time'); ?></th>
                                        <th><?= _l('appointly_use_company_schedule'); ?> <i class="fa fa-question-circle" data-toggle="tooltip" data-placement="top" title="<?= _l('appointly_use_company_schedule_tooltip'); ?>"></i></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($weekdays as $day): ?>
                                        <tr>
                                            <td>
                                                <?= _l('appointly_day_' . strtolower($day)); ?>
                                                <input type="hidden" name="working_hours[<?= $day; ?>][weekday]" value="<?= $day; ?>">
                                            </td>
                                            <td>
                                                <div class="onoffswitch">
                                                    <input type="hidden" name="working_hours[<?= $day; ?>][is_available]" value="0">
                                                    <input type="checkbox"
                                                        id="working_hours_<?= strtolower($day); ?>_available"
                                                        class="onoffswitch-checkbox"
                                                        name="working_hours[<?= $day; ?>][is_available]"
                                                        value="1"
                                                        data-day="<?= strtolower($day); ?>"
                                                        <?= $working_hours[$day]['is_available'] ? 'checked' : ''; ?>>
                                                    <label class="onoffswitch-label" for="working_hours_<?= strtolower($day); ?>_available"></label>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="form-group no-margin">
                                                    <select name="working_hours[<?= $day; ?>][start_time]" class="form-control working-hours-time" <?= !$working_hours[$day]['is_available'] ? 'disabled' : ''; ?>>
                                                        <?php
                                                        for ($hour = 0; $hour < 24; $hour++) {
                                                            foreach ([0, 15, 30, 45] as $minute) {
                                                                $time = sprintf("%02d:%02d", $hour, $minute);
                                                                $selected = (str_starts_with($working_hours[$day]['start_time'], $time)) ? 'selected' : '';
                                                                echo "<option value=\"{$time}\" {$selected}>{$time}</option>";
                                                            }
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="form-group no-margin">
                                                    <select name="working_hours[<?= $day; ?>][end_time]" class="form-control working-hours-time" <?= !$working_hours[$day]['is_available'] ? 'disabled' : ''; ?>>
                                                        <?php
                                                        for ($hour = 0; $hour < 24; $hour++) {
                                                            foreach ([0, 15, 30, 45] as $minute) {
                                                                $time = sprintf("%02d:%02d", $hour, $minute);
                                                                $selected = (str_starts_with($working_hours[$day]['end_time'], $time)) ? 'selected' : '';
                                                                echo "<option value=\"{$time}\" {$selected}>{$time}</option>";
                                                            }
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="checkbox">
                                                    <input type="hidden" name="working_hours[<?= $day; ?>][use_company_schedule]" value="0">
                                                    <input type="checkbox"
                                                        id="use_company_schedule_<?= strtolower($day); ?>"
                                                        name="working_hours[<?= $day; ?>][use_company_schedule]"
                                                        value="1"
                                                        class="use-company-schedule"
                                                        data-day="<?= $day; ?>"
                                                        <?= isset($working_hours[$day]['use_company_schedule']) && $working_hours[$day]['use_company_schedule'] ? 'checked' : ''; ?>>
                                                    <label for="use_company_schedule_<?= strtolower($day); ?>"></label>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary pull-right mtop15"><?= _l('submit'); ?></button>
                            </div>
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
    $(document).ready(function() {

        // Set active menu state
        $('li.menu-item-appointly').addClass('active');
        $('li.menu-item-appointly > ul').addClass('in');
        $('li.sub-menu-item-appointly-staff-working-hours').addClass('active');

        // Initialize tooltips
        $('[data-toggle="tooltip"]').tooltip();

        // Add translation if not exists
        if (typeof(app.lang.working_hours_time_intervals_note) === 'undefined') {
            app.lang.working_hours_time_intervals_note = "<?= _l('working_hours_time_intervals_note'); ?>";
        }

        // Toggle time inputs based on day availability
        $('.onoffswitch-checkbox').on('change', function() {
            const day = $(this).data('day');
            const isAvailable = $(this).prop('checked');
            const capitalizedDay = day.charAt(0).toUpperCase() + day.slice(1);

            // Only disable/enable time inputs, not the use_company_schedule checkbox
            $(`input[name="working_hours[${capitalizedDay}][start_time]\"]`).prop('disabled', !isAvailable);
            $(`input[name="working_hours[${capitalizedDay}][end_time]\"]`).prop('disabled', !isAvailable);

            // Important: Do NOT modify the use_company_schedule checkbox state
            // Let it remain independent of the availability setting
        });

        // Use company schedule checkbox - enhance to work with unavailable days
        $('.use-company-schedule').on('change', function() {
            const day = $(this).data('day');
            const useCompanySchedule = $(this).prop('checked');

            // Get start and end time inputs
            const startTimeInput = $(`input[name="working_hours[${day}][start_time]"]`);
            const endTimeInput = $(`input[name="working_hours[${day}][end_time]"]`);

            // When using company schedule, disable time inputs regardless of availability
            if (useCompanySchedule) {
                // Get company schedule for this day
                <?php foreach ($weekdays as $day): ?>
                    if (day === '<?= $day ?>') {
                        const companyStartTime = '<?= isset($company_schedule[$day]['start_time']) ? substr($company_schedule[$day]['start_time'], 0, 5) : '09:00'; ?>';
                        const companyEndTime = '<?= isset($company_schedule[$day]['end_time']) ? substr($company_schedule[$day]['end_time'], 0, 5) : '17:00'; ?>';
                        const companyEnabled = <?= isset($company_schedule[$day]['is_enabled']) && $company_schedule[$day]['is_enabled'] ? 'true' : 'false'; ?>;

                        // Update time inputs with company values
                        startTimeInput.val(companyStartTime);
                        endTimeInput.val(companyEndTime);

                        // Update availability to match company schedule
                        const availabilityCheckbox = $(`#working_hours_${day.toLowerCase()}_available`);
                        availabilityCheckbox.prop('checked', companyEnabled);

                        // Disable time inputs when using company schedule
                        startTimeInput.prop('disabled', true);
                        endTimeInput.prop('disabled', true);

                        // Show temporary feedback
                        const $row = $(this).closest('tr');
                        $row.addClass('success');
                        setTimeout(() => {
                            $row.removeClass('success');
                        }, 1000);
                    }
                <?php endforeach; ?>
            } else {
                // Re-enable time inputs if day is available
                const isAvailable = $(`#working_hours_${day.toLowerCase()}_available`).prop('checked');
                startTimeInput.prop('disabled', !isAvailable);
                endTimeInput.prop('disabled', !isAvailable);
            }
        });

        // Form validation
        $('#staff-working-hours-form').on('submit', function(e) {
            // Enable all time inputs before form submission to ensure values are submitted
            $('.working-hours-time').prop('disabled', false);

            // Check that start time is before end time for each available day
            let valid = true;

            $('.onoffswitch-checkbox:checked').each(function() {
                const day = $(this).data('day').charAt(0).toUpperCase() + $(this).data('day').slice(1);
                const startTime = $(`input[name="working_hours[${day}][start_time]\"]`).val();
                const endTime = $(`input[name="working_hours[${day}][end_time]\"]`).val();

                if (startTime >= endTime) {
                    alert("<?= _l('appointly_time_error'); ?>: " + day);
                    valid = false;
                    e.preventDefault();
                    return false;
                }
            });

            return valid;
        });

        // Initialize company schedule checkboxes on page load
        $('.use-company-schedule').each(function() {
            if ($(this).prop('checked')) {
                const day = $(this).data('day');
                const startTimeInput = $(`input[name="working_hours[${day}][start_time]"]`);
                const endTimeInput = $(`input[name="working_hours[${day}][end_time]"]`);

                // Disable time inputs when using company schedule
                startTimeInput.prop('disabled', true);
                endTimeInput.prop('disabled', true);
            }
        });
    });
</script>
