<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="user-scalable=no, width=device-width, initial-scale=1, maximum-scale=1">
    <title><?php echo hooks()->apply_filters('appointments_form_title', _l('appointment_create_new_appointment')); ?></title>

    <?php app_external_form_header($form); ?>

    <script src="https://cdn.tailwindcss.com"></script>

    <link href="<?= module_dir_url('appointly', 'assets/css/appointments_external_form.css'); ?>" rel="stylesheet" type="text/css">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            200: '#bae6fd',
                            300: '#7dd3fc',
                            400: '#38bdf8',
                            500: '#0ea5e9',
                            600: '#0284c7',
                            700: '#0369a1',
                            800: '#075985',
                            900: '#0c4a6e',
                        },
                    },
                },
            },
        }
    </script>
    <style>
.logo-header {
    text-align: center;
    padding: 18px 0 5px;
}

.logo-header .logo,
.logo-header .appointment-logo-link {
    display: inline-block;
}

.logo-header .logo img,
.logo-header .appointment-logo-link img {
    width: 38px !important;
    height: 13px !important;
    max-width: 38px !important;
    max-height: 13px !important;
    object-fit: contain;
    display: block;
    margin: 0 auto;
}

.appointment-logo-website {
    margin-top: 6px;
    font-size: 14px;
    font-weight: 600;
}

.appointment-logo-website a {
    color: #0b5cab;
    text-decoration: none;
}

.appointment-logo-website a:hover {
    color: #f7941d;
    text-decoration: underline;
}
</style>
</head>

<body class="appointments-external-form tw-bg-neutral-50"
    <?php if (is_rtl(true)) {
        echo ' dir="rtl"';
    } ?>>
    <?php
    $clientUserData = $this->session->userdata();

    $this->load->model('appointly/appointly_model');

    $externalHeading = get_option('external_form_heading');
    if (empty($externalHeading)) {
        $externalHeading = _l('appointment_schedule_appointment');
    }

    $externalDescription = get_option('external_form_description');
    if (empty($externalDescription)) {
        $externalDescription = _l('appointment_schedule_description');
    }

    $logo = get_dark_company_logo($uri = 'justsmartchoice.com', $href_class = 'appointment-logo-link');
    echo '<div class="logo-header">' . $logo . '<div class="appointment-logo-website"><a href="https://justsmartchoice.com" target="_blank" rel="noopener">justsmartchoice.com</a></div></div>';

    $us_timezones = [
        'America/Tampa New York'    => 'Eastern Time - Tampa / New York / Miami',
        'America/Chicago'     => 'Central Time - Chicago / Dallas / Houston',
        'America/Denver'      => 'Mountain Time - Denver / Salt Lake City',
        'America/Phoenix'     => 'Arizona Time - Phoenix',
        'America/Los_Angeles' => 'Pacific Time - Los Angeles / Seattle',
        'America/Anchorage'   => 'Alaska Time - Anchorage',
        'Pacific/Honolulu'    => 'Hawaii Time - Honolulu',
    ];
    ?>
    <div id="wrapper" class="tw-min-h-screen tw-py-4 md:tw-py-12">
        <div id="content">
            <div class="container tw-px-2 md:tw-px-4">
                <div id="response"></div>
                <?php echo form_open('appointly/appointments_public/create_external_appointment', ['id' => 'appointment-form', 'class' => 'form-booking-steps', 'autocomplete' => 'off', 'data-prevent-submit' => 'true']); ?>
                <input type="hidden" name="rel_type" value="external">
                <input type="hidden" name="hidden_staff_id" id="hidden_staff_id" value="">
                <input type="hidden" name="service_id" id="service_id" value="">
                <input type="hidden" name="staff_id" id="staff_id" value="">
                <input type="hidden" name="date" id="appointment_date_field" value="">
                <input type="hidden" name="start_hour" id="start_hour" value="">
                <input type="hidden" name="end_hour" id="end_hour" value="">

                <div class="row">
                    <div class="tw-rounded-md main_wrapper <?= ($this->input->get('col')) ?: 'col-md-8 col-md-offset-2' ?>">
                        <div class="appointment-header">
                            <h4 class="tw-text-2xl tw-font-bold tw-text-neutral-900">
                                <?= $externalHeading; ?>
                            </h4>
                        </div>

                        <p class="tw-text-neutral-600 tw-mt-5 tw-text-center">
                            <?= $externalDescription; ?>
                        </p>
                        <hr class="tw-border-t tw-border-neutral-200 tw-my-6" />

                        <div>
                            <div class="progress-steps-container tw-relative">
                                <div class="tw-flex tw-justify-between tw-items-center">
                                    <div class="step-indicator step-1 tw-text-center tw-flex tw-flex-col tw-items-center active" id="step-indicator-1">
                                        <div class="step-number tw-w-10 tw-h-10 tw-flex tw-items-center tw-justify-center tw-rounded-full tw-bg-primary-500 tw-text-white tw-font-medium tw-mb-2">1</div>
                                        <div class="step-title tw-text-sm"><?= _l('appointment_select_service'); ?></div>
                                    </div>

                                    <div class="step-line tw-mt-6 tw-flex-1 tw-h-1 tw-bg-neutral-300 tw-mx-2" id="line-1-2"></div>

                                    <div class="step-indicator step-2 tw-text-center tw-flex tw-flex-col tw-items-center" id="step-indicator-2">
                                        <div class="step-number tw-w-10 tw-h-10 tw-flex tw-items-center tw-justify-center tw-rounded-full tw-bg-neutral-300 tw-text-white tw-font-medium tw-mb-2">2</div>
                                        <div class="step-title tw-text-sm"><?= _l('appointment_select_provider'); ?></div>
                                    </div>

                                    <div class="step-line tw-mt-6 tw-flex-1 tw-h-1 tw-bg-neutral-300 tw-mx-2" id="line-2-3"></div>

                                    <div class="step-indicator step-3 tw-text-center tw-flex tw-flex-col tw-items-center" id="step-indicator-3">
                                        <div class="step-number tw-w-10 tw-h-10 tw-flex tw-items-center tw-justify-center tw-rounded-full tw-bg-neutral-300 tw-text-white tw-font-medium tw-mb-2">3</div>
                                        <div class="step-title tw-text-sm"><?= _l('appointment_date_time'); ?></div>
                                    </div>

                                    <div class="step-line tw-mt-6 tw-flex-1 tw-h-1 tw-bg-neutral-300 tw-mx-2" id="line-3-4"></div>

                                    <div class="step-indicator step-4 tw-text-center tw-flex tw-flex-col tw-items-center" id="step-indicator-4">
                                        <div class="step-number tw-w-10 tw-h-10 tw-flex tw-items-center tw-justify-center tw-rounded-full tw-bg-neutral-300 tw-text-white tw-font-medium tw-mb-2">4</div>
                                        <div class="step-title tw-text-sm"><?= _l('appointment_your_details'); ?></div>
                                    </div>
                                </div>
                            </div>

                            <div class="tw-mt-6 tw-bg-neutral-200 tw-h-2 tw-rounded-full tw-relative">
                                <div class="tw-h-full tw-bg-primary-500 tw-rounded-full progress-bar tw-absolute tw-left-0 tw-top-0" style="width: 25%"></div>
                            </div>
                        </div>
                        <hr class="tw-border-t tw-border-neutral-200 tw-my-6" />

                        <div class="booking-step step-1-content active" id="step-1">
                            <h4 class="tw-text-lg tw-font-semibold tw-mb-4"><?= _l('appointments_service_heading'); ?></h4>
                            <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-<?= min(2, count($services)) ?> lg:tw-grid-cols-<?= min(3, count($services)) ?> tw-gap-3 md:tw-gap-4">
                                <?php
                                if (!empty($services)) :
                                    foreach ($services as $service) :
                                ?>
                                        <div class="service-card tw-bg-white tw-border tw-border-neutral-200 tw-p-4 tw-rounded-xl tw-transition-all hover:tw-border-primary-500 <?= isset($service['color']) ? 'service-card-' . trim($service['color'], '#') : '' ?>"
                                            onclick="window.selectService(<?= $service['id'] ?>);"
                                            data-service-id="<?= $service['id'] ?>"
                                            data-price="<?= isset($service['price']) ? app_format_money($service['price'], $baseCurrency) : 'Free' ?>"
                                            data-duration="<?= $service['duration'] ?>">
                                            <div class="service-header tw-flex tw-justify-between tw-items-center tw-mb-3">
                                                <h5 class="tw-font-semibold tw-text-lg"><?= $service['name'] ?></h5>
                                                <?php if (!empty($service['color'])) : ?>
                                                    <span class="tw-h-4 tw-w-4 tw-rounded-full" style="background-color: <?= $service['color'] ?>"></span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="service-details">
                                                <div class="tw-flex tw-justify-between tw-mb-1">
                                                    <span class="tw-text-neutral-600"><?= _l('appointment_service_duration') ?>:</span>
                                                    <span class="tw-font-medium"><?= $service['duration'] ?> <?= _l('appointly_duration_minutes') ?></span>
                                                </div>
                                                <?php if (isset($service['price']) && $service['price'] > 0) : ?>
                                                    <div class="tw-flex tw-justify-between tw-mb-1">
                                                        <span class="tw-text-neutral-600"><?= _l('appointment_service_price') ?>:</span>
                                                        <span class="tw-font-medium">
                                                            <?= app_format_money($service['price'], $baseCurrency); ?>
                                                        </span>
                                                    </div>
                                                <?php endif; ?>
                                                <?php if (isset($service['description']) && !empty($service['description'])) : ?>
                                                    <div class="tw-mt-2">
                                                        <p class="tw-text-neutral-600 tw-text-sm"><?= $service['description'] ?></p>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php
                                    endforeach;
                                else :
                                    ?>
                                    <div class="col-md-12">
                                        <div class="alert alert-warning">
                                            <?= _l('no_services_available') ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <?php if (count($services) > 0) : ?>
                                <div class="tw-flex tw-justify-end tw-mt-6">
                                    <button type="button" class="btn btn-primary btn-next" data-step="1"><?= _l('appointment_continue'); ?></button>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="booking-step step-2-content hidden" id="step-2">
                            <div class="tw-flex tw-justify-between tw-items-center tw-mb-4">
                                <a href="#" class="btn-prev-step tw-text-primary-600 hover:tw-text-primary-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 tw-inline tw-mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                    </svg>
                                    <?= _l('appointment_back'); ?>
                                </a>
                                <h4 class="tw-text-lg tw-font-semibold tw-m-0"><?= _l('appointment_select_provider'); ?></h4>
                            </div>
                            <div id="providers-container" class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-3 md:tw-gap-4">
                                <div class="tw-col-span-full tw-flex tw-flex-col tw-items-center tw-justify-center tw-py-12">
                                    <div class="tw-w-12 tw-h-12 tw-border-4 tw-border-primary-500 tw-border-t-transparent tw-rounded-full tw-animate-spin tw-mb-4"></div>
                                    <p class="tw-text-neutral-600 tw-text-center"><?= _l('appointment_loading_providers'); ?></p>
                                </div>
                            </div>

                            <div class="tw-flex tw-justify-end tw-mt-6">
                                <button type="button" class="btn btn-primary btn-next" data-step="2"><?= _l('appointment_continue'); ?></button>
                            </div>
                        </div>

                        <div class="booking-step step-3-content hidden" id="step-3">
                            <div class="tw-flex tw-justify-between tw-items-center tw-mb-6">
                                <a href="#" class="btn-prev-step tw-text-primary-600 hover:tw-text-primary-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 tw-inline tw-mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                    </svg>
                                    <?= _l('appointment_back'); ?>
                                </a>
                                <h4 class="tw-text-lg tw-font-semibold tw-m-0"><?= _l('appointment_date_time'); ?></h4>
                            </div>

                            <div class="tw-bg-white tw-border tw-border-neutral-200 tw-rounded-lg tw-p-3 md:tw-p-5 tw-mb-6">
                                <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-6">
                                    <div class="tw-flex tw-flex-col">
                                        <label for="appointment-date" class="tw-font-medium tw-mb-2"><?= _l('appointment_date'); ?></label>
                                        <div class="tw-relative">
                                            <input type="text" id="appointment-date" class="form-control tw-w-full" placeholder="<?= _l('appointment_select_date'); ?>" readonly onchange="return false;">
                                            <div id="date_loading" class="tw-absolute tw-right-3 tw-top-1/2 tw-transform -tw-translate-y-1/2 hide">
                                                <div class="tw-w-5 tw-h-5 tw-border-2 tw-border-primary-500 tw-border-t-transparent tw-rounded-full tw-animate-spin"></div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="tw-flex tw-flex-col">
                                        <label class="tw-font-medium tw-mb-2">Timezone</label>
                                        <div class="input-group tw-w-full">
                                            <span class="input-group-addon">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            </span>
                                            <select name="timezone" id="timezone" class="form-control selectpicker tw-w-full" data-width="100%" data-live-search="true">
                                                <optgroup label="UNITED STATES">
                                                    <?php foreach ($us_timezones as $timezone => $timezone_label) { ?>
                                                        <option value="<?= e($timezone); ?>"
                                                            <?= get_option('default_timezone') == $timezone ? 'selected' : ''; ?>>
                                                            <?= e($timezone_label); ?>
                                                        </option>
                                                    <?php } ?>
                                                </optgroup>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <input type="hidden" name="start_hour" id="start_hour">
                            <input type="hidden" name="end_hour" id="end_hour">

                            <div id="time-slots-section" class="tw-bg-white tw-border tw-border-neutral-200 tw-rounded-lg tw-p-3 md:tw-p-5 tw-hidden">
                                <h5 class="tw-font-medium tw-mb-4"><?= _l('appointly_available_time_slots'); ?></h5>
                                <div id="time-slots-container" class="tw-grid tw-grid-cols-1 sm:tw-grid-cols-2 md:tw-grid-cols-3 lg:tw-grid-cols-4 tw-gap-3">
                                </div>
                                <div id="slot-loading" class="tw-text-center tw-py-8 tw-hidden">
                                    <div class="tw-w-8 tw-h-8 tw-mx-auto tw-border-2 tw-border-primary-500 tw-border-t-transparent tw-rounded-full tw-animate-spin"></div>
                                    <p class="tw-mt-2 tw-text-sm tw-text-neutral-600"><?= _l('appointment_loading'); ?></p>
                                </div>
                            </div>

                            <div class="tw-flex tw-justify-end tw-mt-6">
                                <button type="button" class="btn btn-primary btn-next" data-step="3" disabled><?= _l('appointment_continue'); ?></button>
                            </div>
                        </div>

                        <div class="booking-step step-4-content hidden" id="step-4">
                            <div class="tw-flex tw-justify-between tw-items-center tw-mb-6">
                                <a href="#" class="btn-prev-step tw-text-primary-600 hover:tw-text-primary-700 tw-transition-colors tw-duration-200">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 tw-inline tw-mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                    </svg>
                                    <?= _l('appointment_back'); ?>
                                </a>
                                <h4 class="tw-text-xl tw-font-semibold tw-m-0 tw-text-neutral-800"><?= _l('appointment_your_details'); ?></h4>
                            </div>

                            <div class="tw-mb-8 tw-rounded-lg tw-border tw-border-neutral-200 tw-shadow-sm tw-p-6">
                                <div class="tw-flex tw-items-center tw-mb-4">
                                    <div class="tw-bg-primary-500 tw-rounded-full tw-p-2 tw-mr-3">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="tw-w-5 tw-h-5 tw-text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <h5 class="tw-text-lg tw-font-semibold tw-m-0 tw-text-neutral-800"><?= _l('appointment_summary'); ?></h5>
                                </div>
                                <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-3 tw-gap-4">
                                    <div class="tw-bg-white tw-p-4">
                                        <span class="tw-text-sm tw-font-medium tw-text-neutral-600"><?= _l('appointment_service'); ?></span>
                                        <p class="tw-font-semibold tw-text-neutral-900 tw-m-0 tw-text-lg" id="final-service-name">-</p>
                                        <p class="tw-text-sm tw-text-primary-600 tw-m-0 tw-font-medium tw-mt-1" id="final-service-price">-</p>
                                    </div>
                                    <div class="tw-bg-white tw-p-4">
                                        <span class="tw-text-sm tw-font-medium tw-text-neutral-600"><?= _l('appointment_provider'); ?></span>
                                        <p class="tw-font-semibold tw-text-neutral-900 tw-m-0 tw-text-lg" id="final-provider-name">-</p>
                                    </div>
                                    <div class="tw-bg-white tw-p-4">
                                        <span class="tw-text-sm tw-font-medium tw-text-neutral-600"><?= _l('appointment_date_time'); ?></span>
                                        <p class="tw-font-semibold tw-text-neutral-900 tw-m-0 tw-text-lg" id="final-date-time">-</p>
                                    </div>
                                </div>
                            </div>

                            <div class="tw-bg-white tw-rounded-lg tw-border tw-border-neutral-200 tw-mb-6 tw-shadow-sm tw-p-6">
                                <h5 class="tw-text-lg tw-font-semibold tw-m-0 tw-text-neutral-800 tw-mb-4"><?= _l('appointment_details'); ?></h5>
                                <div class="tw-space-y-4">
                                    <div class="form-group tw-mb-4">
                                        <label for="subject" class="control-label tw-text-sm tw-font-medium tw-text-neutral-700 tw-mb-2"><?= _l('appointment_subject') ?> <small class="req text-danger">*</small></label>
                                        <input type="text" class="form-control tw-border-neutral-300 tw-rounded-lg tw-px-4 tw-py-3 tw-text-sm focus:tw-border-primary-500 focus:tw-ring-2 focus:tw-ring-primary-200 tw-transition-all tw-duration-200" name="subject" id="subject" required>
                                    </div>
                                    <div class="form-group tw-mb-0">
                                        <label for="description" class="control-label tw-text-sm tw-font-medium tw-text-neutral-700 tw-mb-2"><?= _l('appointment_description') ?></label>
                                        <textarea class="form-control tw-border-neutral-300 tw-rounded-lg tw-px-4 tw-py-3 tw-text-sm focus:tw-border-primary-500 focus:tw-ring-2 focus:tw-ring-primary-200 tw-transition-all tw-duration-200" name="description" id="description" rows="3"></textarea>
                                    </div>
                                </div>
                            </div>

                            <?php
                            $logged_in_client_data = [];
                            if (is_client_logged_in()) {
                                $client_id = get_client_user_id();
                                $CI = &get_instance();
                                $CI->db->select('firstname, lastname, email, phonenumber');
                                $CI->db->where('userid', $client_id);
                                $CI->db->where('is_primary', 1);
                                $primary_contact = $CI->db->get(db_prefix() . 'contacts')->row();

                                if ($primary_contact) {
                                    $logged_in_client_data = [
                                        'firstname' => $primary_contact->firstname,
                                        'lastname' => $primary_contact->lastname,
                                        'email' => $primary_contact->email,
                                        'phone' => $primary_contact->phonenumber
                                    ];
                                }
                            }
                            ?>
                            <input type="hidden" name="logged_in_client_id" value="<?= is_client_logged_in() ? get_client_user_id() : '' ?>">

                            <div class="tw-bg-white tw-rounded-lg tw-border tw-border-neutral-200 tw-mb-6 tw-shadow-sm tw-p-6">
                                <h5 class="tw-text-lg tw-font-semibold tw-m-0 tw-text-neutral-800 tw-mb-4"><?= _l('appointment_contact'); ?></h5>
                                <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-4">
                                    <div class="form-group tw-mb-4">
                                        <label for="firstname" class="control-label tw-text-sm tw-font-medium tw-text-neutral-700 tw-mb-2"><?= _l('client_firstname') ?> <small class="req text-danger">*</small></label>
                                        <input type="text" class="form-control tw-border-neutral-300 tw-rounded-lg tw-px-4 tw-py-3 tw-text-sm focus:tw-border-primary-500 focus:tw-ring-2 focus:tw-ring-primary-200 tw-transition-all tw-duration-200" name="firstname" id="firstname" value="<?= isset($logged_in_client_data['firstname']) ? htmlspecialchars($logged_in_client_data['firstname']) : '' ?>" required>
                                    </div>
                                    <div class="form-group tw-mb-4">
                                        <label for="lastname" class="control-label tw-text-sm tw-font-medium tw-text-neutral-700 tw-mb-2"><?= _l('client_lastname') ?> <small class="req text-danger">*</small></label>
                                        <input type="text" class="form-control tw-border-neutral-300 tw-rounded-lg tw-px-4 tw-py-3 tw-text-sm focus:tw-border-primary-500 focus:tw-ring-2 focus:tw-ring-primary-200 tw-transition-all tw-duration-200" name="lastname" id="lastname" value="<?= isset($logged_in_client_data['lastname']) ? htmlspecialchars($logged_in_client_data['lastname']) : '' ?>" required>
                                    </div>
                                    <div class="form-group tw-mb-4">
                                        <label for="email" class="control-label tw-text-sm tw-font-medium tw-text-neutral-700 tw-mb-2"><?= _l('appointment_your_email') ?> <small class="req text-danger">*</small></label>
                                        <input type="email" class="form-control tw-border-neutral-300 tw-rounded-lg tw-px-4 tw-py-3 tw-text-sm focus:tw-border-primary-500 focus:tw-ring-2 focus:tw-ring-primary-200 tw-transition-all tw-duration-200" name="email" id="email" value="<?= isset($logged_in_client_data['email']) ? htmlspecialchars($logged_in_client_data['email']) : '' ?>" required>
                                    </div>
                                    <div class="form-group tw-mb-4">
                                        <label for="phone" class="control-label tw-text-sm tw-font-medium tw-text-neutral-700 tw-mb-2"><?= _l('appointment_your_phone') ?></label>
                                        <input type="text" class="form-control tw-border-neutral-300 tw-rounded-lg tw-px-4 tw-py-3 tw-text-sm focus:tw-border-primary-500 focus:tw-ring-2 focus:tw-ring-primary-200 tw-transition-all tw-duration-200" name="phone" id="phone" value="<?= isset($logged_in_client_data['phone']) ? htmlspecialchars($logged_in_client_data['phone']) : '' ?>" placeholder="<?= _l('appointment_your_phone_example') ?>">
                                    </div>
                                    <div class="form-group tw-mb-0 tw-col-span-full">
                                        <label for="address" class="control-label tw-text-sm tw-font-medium tw-text-neutral-700 tw-mb-2"><?= _l('appointment_location_address') ?></label>
                                        <input type="text" class="form-control tw-border-neutral-300 tw-rounded-lg tw-px-4 tw-py-3 tw-text-sm focus:tw-border-primary-500 focus:tw-ring-2 focus:tw-ring-primary-200 tw-transition-all tw-duration-200" name="address" id="address" value="<?= isset($logged_in_client_data['address']) ? htmlspecialchars($logged_in_client_data['address']) : '' ?>" placeholder="<?= _l('appointment_location_placeholder'); ?>">
                                    </div>
                                </div>
                            </div>

                            <?php $custom_fields = get_custom_fields('appointly'); ?>
                            <?php if (!empty($custom_fields)): ?>
                                <div class="tw-bg-white tw-rounded-lg tw-border tw-border-neutral-200 tw-mb-6 tw-shadow-sm tw-p-6">
                                    <h5 class="tw-text-lg tw-font-semibold tw-m-0 tw-text-neutral-800 tw-mb-4"><?= _l('appointment_additional_info'); ?></h5>
                                    <div class="tw-space-y-4">
                                        <?= render_custom_fields('appointly'); ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if (get_option('appointments_enable_terms_conditions')): ?>
                                <div class="tw-bg-white tw-rounded-lg tw-border tw-border-neutral-200 tw-mb-6 tw-shadow-sm tw-p-6">
                                    <h5 class="tw-text-lg tw-font-semibold tw-m-0 tw-text-neutral-800 tw-mb-4"><?= _l('appointment_terms_link'); ?></h5>
                                    <div class="form-group tw-mb-0">
                                        <div class="checkbox checkbox-primary tw-flex tw-items-start tw-flex-col">
                                            <input type="checkbox" name="terms_accepted" id="terms_accepted" value="1" required class="tw-mr-3 tw-mt-1">
                                            <label for="terms_accepted" class="tw-text-sm tw-text-neutral-700 tw-cursor-pointer">
                                                <?= _l('appointment_accept_terms') ?>
                                            </label>
                                        </div>
                                        <p class="text-muted small tw-mt-2 tw-text-xs tw-text-neutral-500"><?= _l('appointment_terms_description') ?> <a href="<?= site_url('terms_and_conditions') ?>" target="_blank" class="tw-text-primary-600 hover:tw-text-primary-700 tw-underline"><?= _l('appointment_terms_link') ?></a></p>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if ($form->recaptcha == 1): ?>
                                <div class="tw-bg-white tw-rounded-lg tw-border tw-border-neutral-200 tw-mb-6 tw-shadow-sm tw-p-6">
                                    <h5 class="tw-text-lg tw-font-semibold tw-m-0 tw-text-neutral-800 tw-mb-4"><?= _l('appointment_security_verification'); ?></h5>
                                    <div class="form-group tw-mb-0">
                                        <div class="g-recaptcha" data-sitekey="<?= get_option('recaptcha_site_key'); ?>"></div>
                                        <div id="recaptcha_response_field" class="text-danger tw-mt-2"></div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <div class="tw-flex tw-justify-end tw-mt-8">
                                <button type="submit" class="btn-primary btn-lg tw-w-full sm:tw-w-auto tw-transform hover:tw-scale-105" id="book-appointment-btn">
                                    <?= _l('appointment_book_now') ?>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
    </div>

    <?php app_external_form_footer($form); ?>

    <?php if (isset($form)): ?>
        <script>
            site_url = "<?= site_url(); ?>";
            appointlyLang = {
                appointment_select_service: "<?= addslashes(_l('appointment_select_service')); ?>",
                appointment_select_provider: "<?= addslashes(_l('appointment_select_provider')); ?>",
                appointment_date_time: "<?= addslashes(_l('appointment_date_time')); ?>",
                appointment_your_details: "<?= addslashes(_l('appointment_your_details')); ?>",
                appointment_summary: "<?= addslashes(_l('appointment_summary')); ?>",
                appointment_book_now: "<?= addslashes(_l('appointment_book_now')); ?>",
                service_required: "<?= addslashes(_l('appointment_service_required')); ?>",
                provider_required: "<?= addslashes(_l('appointment_select_provider_warning')); ?>",
                date_required: "<?= addslashes(_l('appointment_date_required')); ?>",
                time_required: "<?= addslashes(_l('appointment_time_required')); ?>",
                name_required: "<?= addslashes(_l('appointment_name_required')); ?>",
                email_required: "<?= addslashes(_l('appointment_email_required')); ?>",
                email_invalid: "<?= addslashes(_l('appointment_email_invalid')); ?>",
                select_time: "<?= addslashes(_l('appointment_select_time')); ?>",
                loading: "<?= addslashes(_l('appointment_loading')); ?>",
                error_loading_providers: "<?= addslashes(_l('appointment_error_loading_providers')); ?>",
                no_providers: "<?= addslashes(_l('appointment_no_providers')); ?>",
                no_working_hours: "<?= addslashes(_l('appointly_no_working_hours_found')); ?>",
                no_providers_with_hours: "<?= addslashes(_l('appointly_no_providers_with_hours')); ?>",
                closed: "<?= addslashes(_l('appointment_closed')); ?>",
                error_loading_slots: "<?= addslashes(_l('appointment_error_loading_slots')); ?>",
                no_slots_available: "<?= addslashes(_l('appointment_no_slots_available')); ?>",
                monday: "<?= addslashes(_l('monday')); ?>",
                tuesday: "<?= addslashes(_l('tuesday')); ?>",
                wednesday: "<?= addslashes(_l('wednesday')); ?>",
                thursday: "<?= addslashes(_l('thursday')); ?>",
                friday: "<?= addslashes(_l('friday')); ?>",
                saturday: "<?= addslashes(_l('saturday')); ?>",
                sunday: "<?= addslashes(_l('sunday')); ?>",
                minutes: "<?= addslashes(_l('appointment_minutes')); ?>",
                submitting: "<?= addslashes(_l('appointment_submitting')); ?>",
                view_details: "<?= addslashes(_l('appointment_view_details')); ?>",
                select: "<?= addslashes(_l('appointment_select')); ?>",
                appointment_unavailable: "<?= addslashes(_l('appointment_unavailable')); ?>",
                appointment_unavailable_slots_shown: "<?= addslashes(_l('appointment_unavailable_slots_shown')); ?>",
                firstname_required: "<?= addslashes(_l('client_firstname') . ' ' . _l('is_required')); ?>",
                lastname_required: "<?= addslashes(_l('client_lastname') . ' ' . _l('is_required')); ?>"
            }
            app.locale = "<?= get_locale_key($form->language); ?>";
            var baseCurrencySymbol = "<?= is_object($baseCurrency) ? $baseCurrency->symbol : $baseCurrency ?>";
            var appointlyShowStaffEmail = "<?= get_option('appointly_show_staff_email') != '0' ? '1' : '0' ?>";

            <?php if ($this->security->get_csrf_token_name() && $this->security->get_csrf_hash()) { ?>
                var csrfTokenName = "<?= $this->security->get_csrf_token_name(); ?>";
                var csrfTokenValue = "<?= $this->security->get_csrf_hash(); ?>";
            <?php } ?>
        </script>
    <?php endif; ?>

    <script src="<?= module_dir_url('appointly', 'assets/js/helpers/appointly_helpers.js'); ?>?v=<?= time(); ?>"></script>

    <?php require('modules/appointly/assets/js/appointments_external_form_js.php'); ?>

    <script>
        $(document).ready(function() {
            $('#terms_accepted').on('click change', function() {
                var $checkbox = $(this);
                var $formGroup = $checkbox.closest('.form-group');

                $checkbox.removeAttr('aria-invalid');
                $checkbox.removeAttr('aria-describedby');

                $formGroup.removeClass('has-error');
                $checkbox.removeClass('error');

                $formGroup.find('label.error').remove();
                $formGroup.find('.text-danger').not('.req').remove();
                $('#terms_accepted-error').remove();

                if ($checkbox.is(':checked')) {
                    $checkbox.prop('checked', true);
                }
            });
        });
    </script>

    <div id="providerDetailsModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title provider-name"></h4>
                </div>
                <div class="modal-body">
                    <div class="tw-flex tw-items-center tw-gap-4 tw-mb-6">
                        <div class="tw-w-20 tw-h-20 tw-rounded-full tw-overflow-hidden tw-bg-neutral-100">
                            <img src="" alt="" class="provider-avatar tw-w-full tw-h-full tw-object-cover">
                        </div>
                        <div>
                            <h5 class="provider-name tw-font-medium tw-text-xl tw-text-neutral-900"></h5>
                            <p class="provider-email tw-text-sm tw-text-neutral-600"></p>
                            <p class="provider-phone tw-text-sm tw-text-neutral-600"></p>
                        </div>
                    </div>

                    <div class="provider-schedule">
                        <h6 class="tw-font-medium tw-mb-3"><?= _l('appointly_working_hours'); ?></h6>
                        <div class="schedule-list tw-space-y-2"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?= _l('close'); ?></button>
                    <button type="button" class="btn btn-primary select-provider" data-provider-id=""><?= _l('service_provider_select'); ?></button>
                </div>
            </div>
        </div>
    </div>
</body>

</html>