<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="row">
                <div class="col-md-12">
                    <div class="panel_s">
                        <div class="panel-body">
                            <h4><?= _l('appointly_company_schedule'); ?></h4>
                            <hr class="hr-panel-heading">

                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <a href="<?= admin_url('appointly/services/staff_working_hours'); ?>" class="btn btn-info pull-right">
                                        <i class="fa fa-user"></i> <?= _l('appointly_staff_working_hours'); ?>
                                    </a>
                                </div>
                            </div>

                            <p class="text-muted"><?= _l('appointly_company_schedule_info'); ?></p>

                            <div class="alert alert-info alert-dismissible">
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                <p><i class="fa fa-info-circle"></i> <?= _l('appointly_note'); ?>: <?= _l('company_schedule_time_intervals_note'); ?></p>
                            </div>

                            <?php echo form_open(admin_url('appointly/services/company_schedule'), ['id' => 'company-schedule-form']); ?>

                            <?php
                            // Get company schedule from database
                            $CI = &get_instance();
                            $CI->load->model('appointly/appointly_model');
                            $company_schedule = $CI->appointly_model->get_company_schedule();

                            // If no schedule set yet, create default values
                            $weekdays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                            foreach ($weekdays as $day) {
                                if (!isset($company_schedule[$day])) {
                                    $company_schedule[$day] = [
                                        'start_time' => '09:00',
                                        'end_time' => '17:00',
                                        'is_enabled' => in_array($day, ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'])
                                    ];
                                }
                            }
                            ?>

                            <div class="table-responsive">
                                <table class="table table-hover table-bordered">
                                    <thead>
                                        <tr>
                                            <th><?= _l('appointly_day'); ?></th>
                                            <th><?= _l('appointly_enabled'); ?></th>
                                            <th><?= _l('appointly_start_time'); ?></th>
                                            <th><?= _l('appointly_end_time'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($weekdays as $day): ?>
                                            <tr>
                                                <td>
                                                    <?= _l('appointly_day_' . strtolower($day)); ?>
                                                    <input type="hidden" name="company_schedule[<?= $day; ?>][weekday]" value="<?= $day; ?>">
                                                </td>
                                                <td>
                                                    <div class="onoffswitch">
                                                        <input type="hidden" name="company_schedule[<?= $day; ?>][is_enabled]" value="0">
                                                        <input type="checkbox"
                                                            id="company_schedule_<?= strtolower($day); ?>_enabled"
                                                            class="onoffswitch-checkbox"
                                                            name="company_schedule[<?= $day; ?>][is_enabled]"
                                                            value="1"
                                                            data-day="<?= strtolower($day); ?>"
                                                            <?= $company_schedule[$day]['is_enabled'] ? 'checked' : ''; ?>>
                                                        <label class="onoffswitch-label" for="company_schedule_<?= strtolower($day); ?>_enabled"></label>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="form-group no-margin">
                                                        <select name="company_schedule[<?= $day; ?>][start_time]" class="form-control company-schedule-time" <?= !$company_schedule[$day]['is_enabled'] ? 'disabled' : ''; ?>>
                                                            <?php
                                                            for ($hour = 0; $hour < 24; $hour++) {
                                                                foreach ([0, 15, 30, 45] as $minute) {
                                                                    $time = sprintf("%02d:%02d", $hour, $minute);
                                                                    $selected = (str_starts_with($company_schedule[$day]['start_time'], $time)) ? 'selected' : '';
                                                                    echo "<option value=\"{$time}\" {$selected}>{$time}</option>";
                                                                }
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="form-group no-margin">
                                                        <select name="company_schedule[<?= $day; ?>][end_time]" class="form-control company-schedule-time" <?= !$company_schedule[$day]['is_enabled'] ? 'disabled' : ''; ?>>
                                                            <?php
                                                            for ($hour = 0; $hour < 24; $hour++) {
                                                                foreach ([0, 15, 30, 45] as $minute) {
                                                                    $time = sprintf("%02d:%02d", $hour, $minute);
                                                                    $selected = (str_starts_with($company_schedule[$day]['end_time'], $time)) ? 'selected' : '';
                                                                    echo "<option value=\"{$time}\" {$selected}>{$time}</option>";
                                                                }
                                                            }
                                                            ?>
                                                        </select>
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
</div>
<?php init_tail(); ?>
<script>
    $(document).ready(function() {
        // Set active menu state
        $('li.menu-item-appointly').addClass('active');
        $('li.menu-item-appointly > ul').addClass('in');
        $('li.sub-menu-item-appointly-company-schedule').addClass('active');

        // Initialize tooltips
        $('[data-toggle="tooltip"]').tooltip();

        // Add translation if not exists
        if (typeof(app.lang.company_schedule_time_intervals_note) === 'undefined') {
            app.lang.company_schedule_time_intervals_note = "<?= _l('company_schedule_time_intervals_note'); ?>";
        }

        // Toggle time inputs based on day enabled/disabled
        $('.onoffswitch-checkbox').on('change', function() {
            const day = $(this).data('day');
            const isEnabled = $(this).prop('checked');

            $(`select[name="company_schedule[${$(this).data('day').charAt(0).toUpperCase() + $(this).data('day').slice(1)}][start_time]\"]`).prop('disabled', !isEnabled);
            $(`select[name="company_schedule[${$(this).data('day').charAt(0).toUpperCase() + $(this).data('day').slice(1)}][end_time]\"]`).prop('disabled', !isEnabled);
        });

        // Form validation
        $('#company-schedule-form').on('submit', function(e) {
            // Enable all time inputs before form submission to ensure values are submitted
            $('.company-schedule-time').prop('disabled', false);

            // Check that start time is before end time for each available day
            let valid = true;

            $('.onoffswitch-checkbox:checked').each(function() {
                const day = $(this).data('day').charAt(0).toUpperCase() + $(this).data('day').slice(1);
                const startTime = $(`select[name="company_schedule[${day}][start_time]\"]`).val();
                const endTime = $(`select[name="company_schedule[${day}][end_time]\"]`).val();

                if (startTime >= endTime) {
                    alert("<?= _l('appointly_time_error'); ?>: " + day);
                    valid = false;
                    e.preventDefault();
                    return false;
                }
            });

            return valid;
        });
    });
</script>
