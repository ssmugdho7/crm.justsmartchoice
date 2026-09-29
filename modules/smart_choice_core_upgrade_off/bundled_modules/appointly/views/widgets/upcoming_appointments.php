<?php
defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$CI = &get_instance();
$CI->load->model('appointly/appointly_model', 'apm');
$CI->load->model('appointly/service_model', 'service_model');

// Check if the library is already loaded
if (!isset($CI->googleplus)) {
    $CI->load->library('appointly/googleplus');
}

// Get the configured date range from settings
$upcoming_range = get_option('appointly_upcoming_widget_range') ?: '7_days';
$appointments = $CI->apm->fetch_upcoming_appointments($upcoming_range);

$atAppointmentsView = false;
$classPick = 'col-md-6 tw-px-2';

if (strpos($_SERVER['REQUEST_URI'], '/admin/appointly/appointments') !== false) {
    $classPick = 'col-md-4 tw-px-2';
    $atAppointmentsView = true;
}

// Get range label for display
$rangeLabels = [
    '7_days' => _l('appointly_next_7_days'),
    '14_days' => _l('appointly_next_14_days'),
    '30_days' => _l('appointly_next_30_days'),
    '4_weeks' => _l('appointly_next_4_weeks')
];
$rangeLabel = $rangeLabels[$upcoming_range] ?? $rangeLabels['7_days'];

?>
<div class="widget" id="widget-<?= basename(__FILE__, ".php"); ?>" data-name="<?= _l('appointly_upcoming_appointments'); ?>">
    <div class="panel_s tw-shadow-none">
        <div class="panel-body padding-10">
            <div class="widget-dragger"></div>
            <div class="tw-flex tw-justify-between tw-items-baseline" style="display: <?php echo empty($appointments) ? 'none' : 'tw-flex'; ?>">
                <div>
                    <h4 class="tw-text-lg tw-ml-3"><?= _l('appointly_upcoming_appointments'); ?></h4>
                </div>
                <div>
                    <span class="badge badge-info"><?= $rangeLabel; ?></span>
                </div>
            </div>
            <hr class="tw-mb-3 tw-mt-2" style="<?php echo empty($appointments) ? 'display: none;' : ''; ?>">

            <?php if (!empty($appointments)): ?>
                <div class="container-fluid tw-px-0">
                    <?php foreach ($appointments as $appointment):
                        $service = null;
                        $serviceColor = '#28B8DA';
                        $serviceName = '';
                        $serviceDuration = '';

                        if (!empty($appointment['service_id'])) {
                            $service = $CI->service_model->get($appointment['service_id']);
                            if ($service) {
                                $serviceColor = $service->color ?? '#28B8DA';
                                $serviceName = $service->name ?? '';
                                $serviceDuration = $service->duration ?? '';
                            }
                        }

                        // Get provider info
                        $providerName = '';
                        if (!empty($appointment['provider_id'])) {
                            $CI->load->model('staff_model');
                            $provider = $CI->staff_model->get($appointment['provider_id']);
                            if ($provider) {
                                $providerName = $provider->firstname . ' ' . $provider->lastname;
                            }
                        }

                        // Calculate days until appointment
                        $appointmentDate = strtotime($appointment['date']);
                        $today = strtotime(date('Y-m-d'));
                        $daysUntil = ceil(($appointmentDate - $today) / (60 * 60 * 24));

                        // Get urgency class
                        $urgencyClass = '';
                        $urgencyText = '';
                        if ($daysUntil == 0) {
                            $urgencyClass = 'badge-danger';
                            $urgencyText = _l('appointly_today');
                        } elseif ($daysUntil == 1) {
                            $urgencyClass = 'badge-warning';
                            $urgencyText = _l('appointly_tomorrow');
                        } elseif ($daysUntil <= 3) {
                            $urgencyClass = 'badge-info';
                            $urgencyText = $daysUntil . ' days';
                        } else {
                            $urgencyClass = 'badge-success';
                            $urgencyText = $daysUntil . ' days';
                        }
                    ?>
                        <div class="<?= $classPick; ?>">
                            <div class="appointment-card" style="border-left: 4px solid <?= $serviceColor; ?>;border-radius: 3px; padding: 11px 12px 10px 12px; margin-bottom: 15px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                                <div class="row">
                                    <div class="col-md-8">
                                        <?php
                                        // Use Google Calendar link for Google appointments, local link for others
                                        if ($appointment['source'] == 'google' && !empty($appointment['google_calendar_link'])) {
                                            $appointmentLink = $appointment['google_calendar_link'];
                                            $linkTarget = ' target="_blank"';
                                        } else {
                                            $appointmentLink = admin_url('appointly/appointments/view?appointment_id=' . $appointment['id']);
                                            $linkTarget = '';
                                        }
                                        ?>
                                        <a href="<?= $appointmentLink; ?>" <?= $linkTarget; ?> class="font-medium"><?= $appointment['subject']; ?></a>
                                    </div>
                                    <div class="col-md-4 text-right">
                                        <span class="badge <?= $urgencyClass; ?>" style="font-size: 10px;">
                                            <?= $urgencyText; ?>
                                        </span>
                                    </div>
                                </div>

                                <div class="row" style="margin-top: 10px;">
                                    <div class="col-md-6">
                                        <i class="fa fa-calendar text-muted" style="margin-right: 5px;"></i>
                                        <span class="text-muted"><small><?= _d($appointment['date']); ?></small></span>
                                    </div>
                                    <div class="col-md-6 text-right">
                                        <i class="fa fa-clock-o text-muted" style="margin-right: 5px;"></i>
                                        <span class="text-muted"><small>
                                                <?php
                                                $time_format = get_option('time_format') == 24 ? 'H:i' : 'g:i A';

                                                if (!empty($appointment['start_hour']) && $appointment['start_hour'] != '00:00:00' && strtotime($appointment['start_hour']) !== false) {
                                                    $startFormatted = date($time_format, strtotime($appointment['start_hour']));
                                                    echo "<strong>{$startFormatted}</strong>";

                                                    if (!empty($appointment['end_hour']) && $appointment['end_hour'] != '00:00:00') {
                                                        $endFormatted = date($time_format, strtotime($appointment['end_hour']));
                                                        echo " - {$endFormatted}";
                                                    } elseif (isset($appointment['service']) && !empty($appointment['service']->duration)) {
                                                        $endTimestamp = strtotime("+{$appointment['service']->duration} minutes", strtotime($appointment['start_hour']));
                                                        $endFormatted = date($time_format, $endTimestamp);
                                                        echo " - {$endFormatted}";
                                                    }
                                                } else {
                                                    echo '<span class="text-muted">All Day</span>';
                                                }
                                                ?>
                                            </small></span>
                                    </div>
                                </div>

                                <?php if (!empty($serviceName) && !$atAppointmentsView): ?>
                                    <div class="row" style="margin-top: 8px;">
                                        <div class="col-md-8">
                                            <i class="fa fa-briefcase text-muted" style="margin-right: 5px;"></i>
                                            <span class="label label-default"><?= $serviceName; ?></span>
                                        </div>
                                        <?php if (!empty($serviceDuration)): ?>
                                            <div class="col-md-4 text-right">
                                                <small class="text-muted"><?= $serviceDuration; ?> <?= _l('minutes'); ?></small>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($providerName) && !$atAppointmentsView): ?>
                                    <div style="margin-top: 8px;">
                                        <i class="fa fa-user text-muted" style="margin-right: 5px;"></i>
                                        <small class="text-muted"><?= $providerName; ?></small>
                                    </div>
                                <?php endif; ?>

                                <?php
                                $sourceLabels = [
                                    'internal' => _l('appointment_internal'),
                                    'external' => _l('appointment_external'),
                                    'lead_related' => _l('appointment_lead_related'),
                                    'internal_staff' => _l('appointment_internal_staff'),
                                    'google' => _l('appointment_google_calendar'),
                                ];
                                $sourceLabel = $sourceLabels[$appointment['source']] ?? $sourceLabels['internal'];
                                ?>
                                <div class="row" style="margin-top: 10px;">
                                    <div class="col-md-6">
                                        <span class="badge badge-light" style="font-size: 10px;"><?= $sourceLabel; ?></span>
                                    </div>
                                    <div class="col-md-6 text-right">
                                        <?php
                                        $statusLabels = [
                                            'pending' => ['label' => _l('appointment_status_pending'), 'class' => 'badge-warning'],
                                            'cancelled' => ['label' => _l('appointment_cancelled'), 'class' => 'badge-danger'],
                                            'completed' => ['label' => _l('appointment_finished'), 'class' => 'badge-success'],
                                            'no-show' => ['label' => _l('appointment_no_show'), 'class' => 'badge-secondary'],
                                            'in-progress' => ['label' => _l('appointment_status_in-progress'), 'class' => 'badge-info'],
                                        ];
                                        // Handle Google Calendar statuses that might not match our system
                                        $appointmentStatus = $appointment['status'];
                                        if ($appointmentStatus == 'confirmed') {
                                            $appointmentStatus = 'pending'; // Map Google's "confirmed" to our "pending"
                                        }
                                        $status = $statusLabels[$appointmentStatus] ?? ['label' => ucfirst($appointmentStatus), 'class' => 'badge-secondary'];
                                        ?>
                                        <span class="badge <?= $status['class']; ?>" style="font-size: 10px;"><?= $status['label']; ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if (count($appointments) >= 20): ?>
                    <div class="tw-text-center tw-mt-3">
                        <a href="<?= admin_url('appointly/appointments'); ?>" class="btn btn-default btn-sm">
                            <i class="fa fa-eye tw-mr-2"></i><?= _l('appointly_view_all_appointments'); ?>
                        </a>
                    </div>
                <?php endif; ?>

            <?php else: ?>
                <div class="tw-flex tw-justify-between tw-items-baseline">
                    <div>
                        <h4 class="tw-text-lg tw-ml-3"><?= _l('appointly_no_upcoming_appointments'); ?></h4>
                        <p class="tw-text-sm tw-text-neutral-600 tw-ml-3 tw-mb-4"><?= sprintf(_l('appointly_no_appointments_in_range'), strtolower($rangeLabel)); ?></p>
                    </div>
                    <div>
                        <span class="badge badge-info"><?= $rangeLabel; ?></span>
                    </div>
                </div>
                <div class="tw-text-center tw-mt-3">
                    <a href="<?= admin_url('appointly/appointments/create_page'); ?>" class="btn btn-primary btn-sm">
                        <i class="fa fa-calendar-plus-o tw-mr-2"></i><?= _l('appointment_new_appointment'); ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
    <?php if (strpos($_SERVER['REQUEST_URI'], '/appointly/appointments') !== false): ?>.widget-dragger {
        display: none;
    }

    <?php endif; ?>.appointment-card {
        transition: all 0.2s ease;
        background: #fff;
    }

    .appointment-card:hover {
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15) !important;
        transform: translateY(-1px);
    }

    .appointment-title {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
</style>
