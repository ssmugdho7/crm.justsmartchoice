<?php
defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$CI = &get_instance();
$CI->load->model('appointly/appointly_model', 'apm');
$CI->load->model('appointly/service_model', 'service_model');

// Check if the library is already loaded
if (! isset($CI->googleplus)) {
    $CI->load->library('appointly/googleplus');
}

$appointments = $CI->apm->fetch_todays_appointments();
$atAppointmentsView = false;
$classPick = 'col-md-4 tw-px-2';

if (strpos($_SERVER['REQUEST_URI'], '/admin/appointly/appointments') !== false) {
    $classPick = 'col-md-3 tw-px-2';
    $atAppointmentsView = true;
}

?>
<div class="widget" id="widget-<?= basename(__FILE__, ".php"); ?>" data-name="<?= _l('appointment_todays_appointments'); ?>">
    <div class="panel_s tw-shadow-none">
        <div class="panel-body padding-10">
            <div class="widget-dragger"></div>
            <div class="tw-flex tw-justify-between tw-items-baseline" style="display: <?php echo empty($appointments) ? 'none' : 'tw-flex'; ?>">
                <div>
                    <h4 class="tw-text-lg tw-ml-3"><?= _l('appointment_todays_appointments'); ?></h4>
                </div>
                <div>
                    <span class="badge badge-primary"><?= _d(date('Y-m-d')); ?></span>
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
                    ?>
                        <div class="<?= $classPick; ?>">
                            <div class="appointment-card" style="border-left: 4px solid <?= $serviceColor; ?>;border-radius: 3px; padding: 11px 12px 10px 12px; margin-bottom: 15px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                                <div class="tw-flex tw-justify-between">
                                    <div class="tw-mb-3">
                                        <a href="<?= admin_url('appointly/appointments/view?appointment_id=' . $appointment['id']); ?>" class="font-medium"><?= $appointment['subject']; ?></a>
                                    </div>
                                    <div>
                                        <?php
                                        $sourceLabels = [
                                            'internal' => _l('appointment_internal'),
                                            'external' => _l('appointment_external'),
                                            'lead_related' => _l('appointment_lead_related'),
                                            'internal_staff' => _l('appointment_internal_staff'),
                                        ];
                                        ?>
                                        <span class="badge badge-primary"><?= $sourceLabels[$appointment['source']] ?? $sourceLabels['internal']; ?></span>
                                    </div>
                                </div>
                                <div class="tw-flex tw-justify-between">
                                    <div>
                                        <span class="text-muted">
                                            <?php
                                            $time_format = get_option('time_format') == 24 ? 'H:i' : 'g:i A';

                                            if (!empty($appointment['start_hour']) && $appointment['start_hour'] != '00:00:00' && strtotime($appointment['start_hour']) !== false) {
                                                $startFormatted = date($time_format, strtotime($appointment['start_hour']));
                                                echo "<strong>{$startFormatted}</strong>";

                                                if (!empty($appointment['end_hour']) && $appointment['end_hour'] != '00:00:00') {
                                                    $endFormatted = date($time_format, strtotime($appointment['end_hour']));
                                                    echo " - {$endFormatted}";
                                                } elseif (!empty($serviceDuration)) {
                                                    $endTimestamp = strtotime("+{$serviceDuration} minutes", strtotime($appointment['start_hour']));
                                                    $endFormatted = date($time_format, $endTimestamp);
                                                    echo " - {$endFormatted}";
                                                }
                                            } else {
                                                echo '<span class="text-muted">All Day</span>';
                                            }
                                            ?>
                                        </span>
                                    </div>
                                    <div>
                                        <?php if (!empty($serviceDuration)): ?>
                                            <span class="text-muted">(<?= $serviceDuration; ?> <?= _l('minutes'); ?>)</span>
                                        <?php endif; ?>
                                    </div>
                                </div>


                                <?php if (!empty($serviceName)): ?>
                                    <div class="tw-flex tw-justify-between tw-mt-4 tw-mb-2" style="<?= $atAppointmentsView ? 'display: none;' : ''; ?>">
                                        <div>
                                            <span class="text-muted"><?= _l('appointment_service'); ?>:</span>
                                            <span class="label label-default"><?= $serviceName; ?></span>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($providerName)): ?>
                                    <div class="tw-flex tw-justify-between tw-mb-3" style="<?= $atAppointmentsView ? 'display: none;' : ''; ?>">
                                        <div>
                                            <span class="text-muted"><?= _l('appointment_provider'); ?>:</span>
                                            <span><?= $providerName; ?></span>
                                        </div>
                                        <div>
                                            <span class="label label-primary"><?= date("Y-m-d", strtotime($appointment['date'])); ?></span>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="tw-flex tw-justify-between tw-items-baseline">
                    <div>
                        <h4 class="tw-text-lg tw-ml-3"><?= _l('appointment_no_appointments'); ?></h4>
                    </div>
                    <div>
                        <span class="badge badge-primary"><?= _d(date('Y-m-d')); ?></span>
                    </div>
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
    }

    .appointment-title {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .appointment-description {
        display: -webkit-box;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
</style>
