<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="form-group">
    <label class="control-label"><?= _l('appointly_company_schedule'); ?></label>
    <p class="text-muted"><?= _l('appointly_company_schedule_info'); ?></p>

    <?php
    // Get company schedule from database
    $CI = &get_instance();
    $CI->db->order_by('FIELD(weekday, "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday")');
    $company_schedule = $CI->db->get(db_prefix() . 'appointly_company_schedule')->result_array();

    // If no schedule set yet, create default values
    if (empty($company_schedule)) {
        $company_schedule = [
            ['weekday' => 'Monday', 'start_time' => '09:00:00', 'end_time' => '17:00:00', 'is_enabled' => 1],
            ['weekday' => 'Tuesday', 'start_time' => '09:00:00', 'end_time' => '17:00:00', 'is_enabled' => 1],
            ['weekday' => 'Wednesday', 'start_time' => '09:00:00', 'end_time' => '17:00:00', 'is_enabled' => 1],
            ['weekday' => 'Thursday', 'start_time' => '09:00:00', 'end_time' => '17:00:00', 'is_enabled' => 1],
            ['weekday' => 'Friday', 'start_time' => '09:00:00', 'end_time' => '17:00:00', 'is_enabled' => 1],
            ['weekday' => 'Saturday', 'start_time' => '09:00:00', 'end_time' => '17:00:00', 'is_enabled' => 0],
            ['weekday' => 'Sunday', 'start_time' => '09:00:00', 'end_time' => '17:00:00', 'is_enabled' => 0]
        ];
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
                <?php foreach ($company_schedule as $day): ?>
                    <tr>
                        <td>
                            <?= _l('appointly_day_' . strtolower($day['weekday'])); ?>
                            <input type="hidden" name="company_schedule[<?= $day['weekday']; ?>][weekday]" value="<?= $day['weekday']; ?>">
                        </td>
                        <td>
                            <div class="onoffswitch">
                                <input type="checkbox"
                                    id="company_schedule_<?= strtolower($day['weekday']); ?>_enabled"
                                    class="onoffswitch-checkbox"
                                    name="company_schedule[<?= $day['weekday']; ?>][is_enabled]"
                                    data-day="<?= strtolower($day['weekday']); ?>"
                                    <?= $day['is_enabled'] ? 'checked' : ''; ?>>
                                <label class="onoffswitch-label" for="company_schedule_<?= strtolower($day['weekday']); ?>_enabled"></label>
                            </div>
                        </td>
                        <td>
                            <div class="form-group no-margin">
                                <input type="time"
                                    name="company_schedule[<?= $day['weekday']; ?>][start_time]"
                                    class="form-control company-schedule-time"
                                    value="<?= substr($day['start_time'], 0, 5); ?>"
                                    <?= !$day['is_enabled'] ? 'disabled' : ''; ?>>
                            </div>
                        </td>
                        <td>
                            <div class="form-group no-margin">
                                <input type="time"
                                    name="company_schedule[<?= $day['weekday']; ?>][end_time]"
                                    class="form-control company-schedule-time"
                                    value="<?= substr($day['end_time'], 0, 5); ?>"
                                    <?= !$day['is_enabled'] ? 'disabled' : ''; ?>>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    // Toggle time inputs based on day enabled/disabled
    $(document).ready(function() {
        $('.onoffswitch-checkbox').on('change', function() {
            const day = $(this).data('day');
            const isEnabled = $(this).prop('checked');

            $(`input[name="company_schedule[${$(this).data('day')}][start_time]"]`).prop('disabled', !isEnabled);
            $(`input[name="company_schedule[${$(this).data('day')}][end_time]"]`).prop('disabled', !isEnabled);
        });
    });
</script>
