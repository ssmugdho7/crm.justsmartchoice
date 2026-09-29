<?php

defined('BASEPATH') or exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="user-scalable=no, width=device-width, initial-scale=1, maximum-scale=1">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?= _l('appointment_overview') ?></title>
    <?php
    app_external_form_header($form) ?>

    <!-- Favicon -->
    <link rel="icon" href="<?= base_url('favicon.ico') ?>" type="image/x-icon">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Heroicons -->
    <script src="https://unpkg.com/@heroicons/react@2.0.18/dist/index.min.js"></script>

    <!-- Styles -->
    <link href="<?= module_dir_url('appointly', 'assets/css/appointments_external_form.css'); ?>" rel="stylesheet">

    <script>
        tailwind.config = {
            prefix: 'tw-', // Prevents conflicts
            theme: {
                extend: {
                    colors: {
                        neutral: {
                            50: '#f9fafb',
                            100: '#f3f4f6',
                            200: '#e5e7eb',
                            300: '#d1d5db',
                            400: '#9ca3af',
                            500: '#6b7280',
                            600: '#4b5563',
                            700: '#374151',
                            800: '#1f2937',
                            900: '#111827',
                        },
                        success: {
                            50: '#ecfdf5',
                            100: '#d1fae5',
                            500: '#10b981',
                            600: '#059669',
                            700: '#047857',
                        },
                        danger: {
                            50: '#fef2f2',
                            100: '#fee2e2',
                            500: '#ef4444',
                            600: '#dc2626',
                            700: '#b91c1c',
                        },
                        warning: {
                            50: '#fff7ed',
                            100: '#ffedd5',
                            500: '#f97316',
                            600: '#ea580c',
                            700: '#c2410c',
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    boxShadow: {
                        card: '0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06)',
                        'card-hover': '0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05)',
                    },
                }
            }
        }
    </script>

    <style>
        html {
            font-size: 16px;
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #f5f7fa;
            font-size: 14px;
            line-height: 1.428571429;
            margin: 0;
            padding: 0;
            zoom: 1;
            -webkit-transform: scale(1);
            transform: scale(1);
        }

        /* Custom animation for cards */
        .card-hover-effect {
            transition: all 0.3s ease;
        }

        .card-hover-effect:hover {
            transform: translateY(-5px);
            box-shadow: var(--tw-shadow-colored);
        }

        /* Ensure proper scaling */
        .tw-max-w-4xl {
            max-width: 56rem;
            width: 100%;
            margin: 0 auto;
        }

        .logo {
            margin-top: 0 !important;
        }

        @media screen and (max-width: 768px) {
            .tw-grid {
                grid-template-columns: 1fr !important;
            }

            .tw-max-w-4xl {
                max-width: 100%;
                margin: 0 1rem;
            }
        }

        /* Fix any zoom issues */
        * {
            -webkit-box-sizing: border-box;
            -moz-box-sizing: border-box;
            box-sizing: border-box;
        }
    </style>
</head>

<body class="tw-bg-neutral-50">
    <div class="tw-min-h-screen tw-py-8">
        <div class="tw-max-w-4xl tw-mx-auto tw-px-4 sm:tw-px-6 lg:tw-px-8">
            <!-- Logo centered above the card -->
            <div class="tw-flex tw-justify-center tw-mb-6">
                <div class="tw-flex-shrink-0 tw-p-4"><?= get_company_logo(); ?></div>
            </div>

            <!-- Main Card -->
            <div class="tw-bg-white tw-rounded-xl tw-shadow-card tw-overflow-hidden">
                <!-- Header: Title and Status -->
                <div class="tw-bg-white tw-px-6 tw-py-5">
                    <div class="tw-flex tw-flex-col sm:tw-flex-row tw-items-start sm:tw-items-center tw-justify-between tw-py-4">
                        <div>
                            <h1 class="tw-text-2xl tw-font-semibold tw-text-neutral-800"><?= _l('appointment_overview'); ?></h1>
                        </div>
                        <!-- Status -->
                        <div class="tw-flex tw-items-center tw-gap-2 tw-mt-4 sm:tw-mt-0 tw-w-full sm:tw-w-auto">
                            <label class="tw-text-sm tw-font-medium tw-text-neutral-700 tw-mb-0"><?= _l('appointment_status'); ?></label>
                            <?php
                            $statusClass = '';
                            $statusBg    = '';
                            $statusIcon  = '';
                            $status      = '';

                            switch ($appointment['status']) {
                                case 'cancelled':
                                    $statusClass = 'tw-text-white';
                                    $statusBg    = 'tw-bg-red-500';
                                    $statusIcon  = 'x-circle';
                                    $status      = _l('appointment_status_cancelled');
                                    break;
                                case 'no-show':
                                    $statusClass = 'tw-text-white';
                                    $statusBg    = 'tw-bg-red-500';
                                    $statusIcon  = 'x-circle';
                                    $status      = _l('appointment_status_no-show');
                                    break;
                                case 'in-progress':
                                    $statusClass = 'tw-text-white';
                                    $statusBg    = 'tw-bg-blue-500';
                                    $statusIcon  = 'clock';
                                    $status      = _l('appointment_status_in-progress');
                                    break;
                                case 'pending':
                                    $statusClass = 'tw-text-white';
                                    $statusBg    = 'tw-bg-amber-500';
                                    $statusIcon  = 'clock';
                                    $status      = _l('appointment_status_pending');
                                    break;
                                case 'completed':
                                    $statusClass = 'tw-text-white';
                                    $statusBg    = 'tw-bg-emerald-500';
                                    $statusIcon  = 'check-circle';
                                    $status      = _l('appointment_status_completed');
                                    break;
                                default:
                                    $statusClass = 'tw-text-white';
                                    $statusBg    = 'tw-bg-gray-500';
                                    $statusIcon  = 'calendar';
                                    $status      = _l('appointment_upcoming');
                            }
                            ?>

                            <div class="<?= $statusBg ?> <?= $statusClass ?> tw-px-4 tw-py-1.5 tw-rounded-full tw-flex tw-items-center tw-gap-1.5 tw-ml-auto sm:tw-ml-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="tw-h-4 tw-w-4" viewBox="0 0 20 20" fill="currentColor">
                                    <?php
                                    if ($statusIcon === 'check-circle'): ?>
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                            clip-rule="evenodd" />
                                    <?php
                                    elseif ($statusIcon === 'x-circle'): ?>
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                            clip-rule="evenodd" />
                                    <?php
                                    elseif ($statusIcon === 'clock'): ?>
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                                            clip-rule="evenodd" />
                                    <?php
                                    else: ?>
                                        <path fill-rule="evenodd"
                                            d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z"
                                            clip-rule="evenodd" />
                                    <?php
                                    endif; ?>
                                </svg>
                                <span class="tw-text-xs tw-font-medium"><?= strtoupper($status) ?></span>
                            </div>
                        </div>
                    </div>
                    <hr class="tw-border-neutral-200 tw-mt-2">
                    <div class="tw-bg-white tw-p-4">
                        <!-- Service Section -->
                        <?php
                        if (isset($appointment['service_id']) && $appointment['service_id']) :
                            $service = get_instance()->apm->get_service($appointment['service_id']); ?>
                            <?php
                            if ($service) : ?>
                                <div class="tw-mb-8">
                                    <div class="tw-flex tw-justify-between">
                                        <h3 class="tw-text-base tw-font-semibold tw-text-neutral-800 tw-flex tw-items-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="tw-h-5 tw-w-5 tw-mr-2 tw-text-neutral-500" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd"
                                                    d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                            <?= _l('appointment_service') ?>
                                        </h3>
                                        <!-- Calendar Buttons -->
                                        <div style="float:right;" class="tw-flex tw-gap-2">
                                            <?php if (!empty($appointment['google_meet_link'])) : ?>
                                                <!-- Google Meet Button -->
                                                <button
                                                    class="tw-bg-neutral-100 tw-text-neutral-700 tw-rounded-md tw-text-sm tw-font-medium hover:tw-bg-neutral-200 tw-transition-colors tw-shadow-sm hover:tw-shadow-md tw-border tw-border-neutral-300 tw-px-2.5"
                                                    data-toggle="tooltip"
                                                    title="<?= _l('appointment_google_meet') ?>"
                                                    onclick="openGoogleMeetModal()">
                                                    <i class="fa fa-video"></i>
                                                </button>
                                            <?php endif; ?>

                                            <!-- Add to Calendar Button -->
                                            <button onclick="window.location.href='<?= site_url('appointly/appointments_public/download_ics/' . $appointment['id']) ?>'"
                                                class="tw-bg-neutral-100 tw-text-neutral-700 tw-rounded-md tw-text-sm tw-font-medium hover:tw-bg-neutral-200 tw-transition-colors tw-shadow-sm hover:tw-shadow-md tw-border tw-border-neutral-300 tw-py-1.5 tw-px-3"
                                                data-toggle="tooltip"
                                                title="<?= _l('appointment_download_ics_tooltip') ?>">
                                                <i class="fa fa-calendar"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <hr class="tw-mt-4">
                                    <div class="tw-bg-white tw-p-4 tw-rounded-lg tw-border tw-border-neutral-200 tw-shadow-sm hover:tw-shadow-card tw-transition-all tw-duration-300">
                                        <div class="tw-flex tw-items-center tw-justify-between">
                                            <div class="tw-flex tw-items-center tw-gap-4">
                                                <div class="tw-w-10 tw-h-10 tw-flex tw-items-center tw-justify-center tw-rounded-full"
                                                    style="background-color: <?= $service->color ?? '#0ea5e9' ?>15; color: <?= $service->color ?? '#0ea5e9' ?>;">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="tw-h-5 tw-w-5" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" clip-rule="evenodd" />
                                                        <path d="M2 13.692V16a2 2 0 002 2h12a2 2 0 002-2v-2.308A24.974 24.974 0 0110 15c-2.796 0-5.487-.46-8-1.308z" />
                                                    </svg>
                                                </div>
                                                <div>
                                                    <p class="tw-font-medium tw-text-base tw-text-neutral-800"><?= $service->name ?></p>
                                                    <?php
                                                    if (! empty($service->description)) : ?>
                                                        <p class="tw-text-sm tw-text-neutral-500 tw-mt-1"><?= $service->description ?></p>
                                                    <?php
                                                    endif; ?>
                                                </div>
                                            </div>
                                            <div class="tw-text-right">
                                                <div class="tw-flex tw-items-center tw-bg-neutral-100 tw-text-neutral-700 tw-px-3 tw-py-1 tw-rounded-full tw-text-xs tw-font-medium">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="tw-h-4 tw-w-4 tw-mr-1" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd"
                                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                                                            clip-rule="evenodd" />
                                                    </svg>
                                                    <?= $service->duration ?> <?= _l('minutes'); ?>
                                                </div>
                                                <?php
                                                if ($service->price > 0) : ?>
                                                    <div class="tw-text-sm tw-mt-2 tw-font-medium tw-text-neutral-600">
                                                        <?= app_format_money($service->price, get_base_currency()) ?>
                                                    </div>
                                                <?php
                                                endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Provider -->
                                <?php
                                if (isset($appointment['provider_id'])) :
                                    $provider = get_instance()->staff_model->get($appointment['provider_id']); ?>
                                    <?php
                                    if ($provider) : ?>
                                        <div class="tw-bg-white tw-p-4 tw-rounded-lg tw-border tw-border-neutral-200 tw-shadow-sm hover:tw-shadow-card tw-transition-all tw-duration-300 tw-flex tw-items-start tw-gap-3">
                                            <div class="tw-shrink-0 tw-flex tw-items-center tw-justify-center tw-w-10 tw-h-10 tw-rounded-full tw-bg-neutral-50 tw-text-neutral-600">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="tw-h-5 tw-w-5" viewBox="0 0 20 20" fill="currentColor">
                                                    <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z" />
                                                </svg>
                                            </div>
                                            <div>
                                                <p class="tw-text-xs tw-text-neutral-500 tw-mb-1"><?= _l('appointment_provider'); ?></p>
                                                <div class="tw-flex tw-items-center tw-gap-2">
                                                    <img src="<?= staff_profile_image_url($provider->staffid); ?>"
                                                        alt="<?= $provider->firstname . ' ' . $provider->lastname; ?>"
                                                        class="tw-w-6 tw-h-6 tw-rounded-full tw-object-cover">
                                                    <div>
                                                        <p class="tw-font-medium tw-text-sm"><?= $provider->firstname . ' ' . $provider->lastname; ?></p>
                                                        <p class="tw-text-xs tw-text-neutral-500"><?= $provider->email; ?></p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>
                            <?php endif; ?>
                        <?php endif; ?>

                        <!-- Session Details Section -->
                        <div class="tw-mb-8 tw-mt-8">
                            <h3 class="tw-text-base tw-font-semibold tw-mb-3 tw-text-neutral-800 tw-flex tw-items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="tw-h-5 tw-w-5 tw-mr-2 tw-text-neutral-500" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-6-3a2 2 0 11-4 0 2 2 0 014 0zm-2 4a5 5 0 00-4.546 2.916A5.986 5.986 0 0010 16a5.986 5.986 0 004.546-2.084A5 5 0 0010 11z"
                                        clip-rule="evenodd" />
                                </svg>
                                <?= _l('appointment_general_details') ?>
                            </h3>

                            <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-4 tw-mb-4">
                                <!-- Subject/Purpose -->
                                <div class="tw-bg-white tw-p-4 tw-rounded-lg tw-border tw-border-neutral-200 tw-shadow-sm hover:tw-shadow-card tw-transition-all tw-duration-300 tw-flex tw-items-start tw-gap-3">
                                    <div class="tw-shrink-0 tw-flex tw-items-center tw-justify-center tw-w-10 tw-h-10 tw-rounded-full tw-bg-neutral-50 tw-text-neutral-600">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="tw-h-5 tw-w-5" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd"
                                                d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="tw-text-xs tw-text-neutral-500 tw-mb-1"><?= _l('appointment_subject') ?></p>
                                        <p class="tw-font-medium tw-text-sm"><?= $appointment['subject'] ?></p>
                                    </div>
                                </div>

                                <!-- Created By -->
                                <div class="tw-bg-white tw-p-4 tw-rounded-lg tw-border tw-border-neutral-200 tw-shadow-sm hover:tw-shadow-card tw-transition-all tw-duration-300 tw-flex tw-items-start tw-gap-3">
                                    <div class="tw-shrink-0 tw-flex tw-items-center tw-justify-center tw-w-10 tw-h-10 tw-rounded-full tw-bg-neutral-50 tw-text-neutral-600">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="tw-h-5 tw-w-5" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="tw-w-full">
                                        <p class="tw-text-xs tw-text-neutral-500 tw-mb-1"><?= _l('appointment_client_information') ?></p>
                                        <div class="tw-space-y-1">
                                            <?php if (! empty($appointment['details'])): ?>
                                                <!-- Contact from CRM -->
                                                <p class="tw-font-medium tw-text-sm"><?= $appointment['details']['full_name'] ?></p>
                                                <?php
                                                if (! empty($appointment['details']['company_name'])): ?>
                                                    <p class="tw-text-xs tw-text-neutral-600"><?= $appointment['details']['company_name']; ?></p>
                                                <?php
                                                endif; ?>
                                            <?php else: ?>
                                                <!-- External appointment -->
                                                <div class="tw-flex tw-items-center tw-gap-2">
                                                    <?php
                                                    if ($appointment['created_by']): ?>
                                                        <img src="<?= staff_profile_image_url($appointment['created_by']) ?>"
                                                            alt="<?= get_staff_full_name($appointment['created_by']) ?>"
                                                            class="tw-w-6 tw-h-6 tw-rounded-full tw-object-cover">
                                                        <p class="tw-font-medium tw-text-sm"><?= get_staff_full_name($appointment['created_by']) ?></p>
                                                    <?php
                                                    else: ?>
                                                        <p class="tw-font-medium tw-text-sm"><?= $appointment['name'] ?></p>
                                                    <?php
                                                    endif; ?>
                                                </div>
                                            <?php
                                            endif; ?>
                                        </div>
                                        <!-- Email Contact -->
                                        <?php
                                        $email = '';
                                        if (isset($appointment['details']) && ! empty($appointment['details']['email'])) {
                                            $email = $appointment['details']['email'];
                                        } elseif (! empty($appointment['email'])) {
                                            $email = $appointment['email'];
                                        }
                                        ?>
                                        <a href="mailto:<?= $email ?>"
                                            class="tw-text-sm tw-underline tw-text-blue-600 hover:tw-text-blue-700">
                                            <?= $email ?>
                                        </a>
                                    </div>
                                </div>
                                <!-- Date & Time -->
                                <div class="tw-bg-white tw-p-4 tw-rounded-lg tw-border tw-border-neutral-200 tw-shadow-sm hover:tw-shadow-card tw-transition-all tw-duration-300 tw-flex tw-items-start tw-gap-3">
                                    <div class="tw-shrink-0 tw-flex tw-items-center tw-justify-center tw-w-10 tw-h-10 tw-rounded-full tw-bg-neutral-50 tw-text-neutral-600">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="tw-h-5 tw-w-5" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd"
                                                d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="tw-w-full">
                                        <div class="tw-flex tw-items-center tw-justify-between">
                                            <p class="tw-text-xs tw-text-neutral-500 tw-mb-1"><?= _l('appointment_date_and_time'); ?></p>
                                            <span class="tw-text-xs tw-text-neutral-500 tw-bg-neutral-100 tw-px-2 tw-py-1 tw-rounded">TZ: <?= $appointment['timezone']; ?></span>
                                        </div>

                                        <div class="tw-mt-2">
                                            <p class="tw-font-medium tw-text-base"><?= _d($appointment['date']); ?></p>

                                            <div class="tw-mt-1 tw-flex tw-items-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="tw-h-4 tw-w-4 tw-mr-1 tw-text-neutral-500" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                                <p class="tw-text-sm tw-text-neutral-700">
                                                    <?= date("H:i A", strtotime($appointment['start_hour'])); ?>
                                                    <?php if (! empty($appointment['end_hour'])): ?>
                                                        - <?= date("H:i A", strtotime($appointment['end_hour'])); ?>
                                                    <?php elseif (isset($service) && ! empty($service->duration)): ?>
                                                        <?php
                                                        $end_time = strtotime("+{$service->duration} minutes", strtotime($appointment['start_hour']));
                                                        ?>
                                                        - <?= date("H:i A", $end_time); ?>
                                                    <?php endif; ?>
                                                </p>
                                            </div>

                                            <?php if (isset($service) && ! empty($service->duration)): ?>
                                                <div class="tw-mt-2 tw-flex tw-items-center">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="tw-h-4 tw-w-4 tw-mr-1 tw-text-neutral-500" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                                                    </svg>
                                                    <p class="tw-text-xs tw-text-neutral-600">
                                                        <?= _l('appointment_duration'); ?>: <?= $service->duration ?> <?= _l('minutes'); ?>
                                                    </p>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <!-- Phone Contact -->
                                <?php
                                $phone = '';
                                if (isset($appointment['details']) && ! empty($appointment['details']['phone'])) {
                                    $phone = $appointment['details']['phone'];
                                } elseif (! empty($appointment['phone'])) {
                                    $phone = $appointment['phone'];
                                }
                                ?>
                                <div class="tw-bg-white tw-p-4 tw-rounded-lg tw-border tw-border-neutral-200 tw-shadow-sm hover:tw-shadow-card tw-transition-all tw-duration-300 tw-flex tw-items-start tw-gap-3">
                                    <div class="tw-shrink-0 tw-flex tw-items-center tw-justify-center tw-w-10 tw-h-10 tw-rounded-full tw-bg-neutral-50 tw-text-neutral-600">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="tw-h-5 tw-w-5" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z" />
                                        </svg>
                                    </div>
                                    <div class="tw-w-full">
                                        <p class="tw-text-xs tw-text-neutral-500 tw-mb-2"><?= _l('appointment_phone'); ?></p>
                                        <?php
                                        if (! empty($phone)): ?>
                                            <div class="tw-flex tw-flex-wrap tw-gap-2">
                                                <a href="sms:<?= $phone ?>"
                                                    class="tw-inline-flex tw-items-center tw-bg-neutral-50 tw-text-neutral-600 tw-px-3 tw-py-1.5 tw-rounded-md tw-text-xs tw-font-medium">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="tw-h-4 tw-w-4 tw-mr-1.5" viewBox="0 0 20 20" fill="currentColor">
                                                        <path d="M2 5a2 2 0 012-2h7a2 2 0 012 2v4a2 2 0 01-2 2H9l-3 3v-3H4a2 2 0 01-2-2V5z" />
                                                        <path d="M15 7v2a4 4 0 01-4 4H9.828l-1.766 1.767c.28.149.599.233.938.233h2l3 3v-3h2a2 2 0 002-2V9a2 2 0 00-2-2h-1z" />
                                                    </svg>
                                                    <?= _l('appointment_send_an_sms'); ?>
                                                </a>
                                                <a href="tel:<?= $phone ?>"
                                                    data-toggle="tooltip"
                                                    title="<?= _l('appointment_call_number'); ?>"
                                                    class="tw-inline-flex tw-items-center tw-bg-neutral-50 tw-text-neutral-600 tw-px-3 tw-py-1.5 tw-rounded-md tw-text-xs tw-font-medium">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="tw-h-4 tw-w-4 tw-mr-1.5" viewBox="0 0 20 20" fill="currentColor">
                                                        <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z" />
                                                    </svg>
                                                    <?= $phone; ?>
                                                </a>
                                            </div>
                                        <?php else: ?>
                                            <span class="tw-text-neutral-500 tw-text-sm"><?= _l('appointment_no_phone_provided'); ?></span>
                                        <?php
                                        endif; ?>
                                    </div>
                                </div>
                                <!-- Location -->
                                <?php
                                if (! empty($appointment['address'])) : ?>
                                    <div class="tw-bg-white tw-p-4 tw-rounded-lg tw-border tw-border-neutral-200 tw-shadow-sm hover:tw-shadow-card tw-transition-all tw-duration-300 tw-flex tw-items-start tw-gap-3">
                                        <div class="tw-shrink-0 tw-flex tw-items-center tw-justify-center tw-w-10 tw-h-10 tw-rounded-full tw-bg-neutral-50 tw-text-neutral-600">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="tw-h-5 tw-w-5" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="tw-text-xs tw-text-neutral-500 tw-mb-1"><?= _l('appointment_location_address'); ?></p>
                                            <a href="https://maps.google.com/?q=<?= urlencode($appointment['address']); ?>"
                                                target="_blank"
                                                class="tw-font-medium tw-text-sm tw-text-neutral-600 hover:tw-text-neutral-700 tw-break-words">
                                                <?= $appointment['address']; ?>
                                            </a>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <!-- Attendees Section -->
                            <?php
                            if (! empty($appointment['attendees'])) : ?>
                                <div class="tw-mb-8">
                                    <h3 class="tw-text-base tw-font-semibold tw-mb-3 tw-text-neutral-800 tw-flex tw-items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="tw-h-5 tw-w-5 tw-mr-2 tw-text-neutral-500" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z" />
                                        </svg>
                                        <?= _l('appointment_staff_attendees'); ?>
                                    </h3>

                                    <div class="tw-grid tw-grid-cols-1 sm:tw-grid-cols-2 md:tw-grid-cols-3 tw-gap-3">
                                        <?php
                                        foreach ($appointment['attendees'] as $staff) :
                                            $role = get_appointly_staff_userrole($staff['role']);
                                            if (! $role) {
                                                $role = $staff['admin'] ? _l('appointments_admin_label') : _l('appointments_staff_label');
                                            } elseif ($staff['admin']) {
                                                $role = _l('appointments_admin_label') . ' / ' . $role;
                                            }
                                        ?>
                                            <div class="tw-bg-white tw-p-4 tw-rounded-lg tw-border tw-border-neutral-200 tw-shadow-sm hover:tw-shadow-card tw-transition-all tw-duration-300">
                                                <div class="tw-flex tw-items-center tw-gap-3">
                                                    <img src="<?= staff_profile_image_url($staff['staffid']); ?>"
                                                        alt="<?= $staff['firstname'] . ' ' . $staff['lastname']; ?>"
                                                        class="tw-w-10 tw-h-10 tw-rounded-full tw-object-cover tw-border tw-border-neutral-200">
                                                    <div>
                                                        <p class="tw-font-medium tw-text-sm tw-text-neutral-800"><?= $staff['firstname'] . ' ' . $staff['lastname']; ?></p>
                                                        <div class="tw-flex tw-items-center tw-mt-1">
                                                            <span class="tw-text-xs tw-bg-neutral-100 tw-text-neutral-700 tw-px-2 tw-py-0.5 tw-rounded"><?= $role; ?></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                        <hr class="tw-my-4">
                        <!-- Session Overview/Notes -->
                        <div class="tw-mb-8">
                            <div class="tw-mb-6">
                                <h3 class="tw-text-base tw-font-semibold tw-mb-3 tw-text-neutral-800 tw-flex tw-items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="tw-h-5 tw-w-5 tw-mr-2 tw-text-neutral-500" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2zm0 8a1 1 0 100-2 1 1 0 000 2z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <?= _l('appointment_description'); ?>
                                </h3>

                                <div class="tw-bg-white tw-p-4 tw-rounded-lg tw-border tw-border-neutral-200 tw-shadow-sm hover:tw-shadow-card tw-transition-all tw-duration-300">
                                    <p class="tw-text-sm tw-text-neutral-700 tw-whitespace-pre-line"><?= ! empty($appointment['description']) ? $appointment['description'] : 'N/A'; ?></p>
                                </div>
                            </div>
                        </div>
                        <hr class="tw-my-4">
                        <!-- Feedback Section -->
                        <?php
                        if ($appointment['status'] == 'completed') : ?>
                            <div class="tw-mb-8 tw-mt-6">
                                <h3 class="tw-text-base tw-font-semibold tw-mb-3 tw-text-neutral-800 tw-flex tw-items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="tw-h-5 tw-w-5 tw-mr-2 tw-text-neutral-500" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                    </svg>
                                    <?= _l('appointment_feedback_title'); ?>
                                </h3>

                                <div class="tw-bg-white tw-p-5 tw-rounded-lg tw-border tw-border-neutral-200 tw-shadow-sm">
                                    <?php
                                    if (isset($appointment['feedback']) && $appointment['feedback'] > 0) : ?>
                                        <!-- Display existing feedback -->
                                        <div class="tw-text-center tw-mb-3">
                                            <span class="tw-text-sm tw-text-neutral-600"><?= _l('appointment_feedback_label_current'); ?></span>
                                        </div>
                                        <div class="tw-flex tw-items-center tw-justify-center tw-mb-4">
                                            <?php
                                            for ($i = 0; $i < 5; $i++) : ?>
                                                <div class="feedback_star tw-cursor-pointer tw-mx-1" data-count="<?= $i; ?>" data-rating="<?= $i + 1; ?>"
                                                    onclick="handle_appointment_feedback(this)">
                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                        class="tw-h-7 tw-w-7 <?= ($appointment['feedback'] > $i) ? 'tw-text-yellow-400' : 'tw-text-neutral-300'; ?>" viewBox="0 0 20 20"
                                                        fill="currentColor">
                                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                    </svg>
                                                </div>
                                            <?php
                                            endfor; ?>
                                        </div>
                                        <?php
                                        if (! empty($appointment['feedback_comment'])) : ?>
                                            <div class="tw-mt-3 tw-p-4 tw-bg-neutral-50 tw-rounded-md tw-border tw-border-neutral-200">
                                                <p class="tw-text-xs tw-text-neutral-500 tw-mb-1"><?= _l('appointmenet_feedback_comment'); ?></p>
                                                <p class="tw-text-sm tw-text-neutral-700 tw-whitespace-pre-line"><?= $appointment['feedback_comment']; ?></p>
                                            </div>
                                        <?php
                                        endif; ?>
                                        <div class="tw-text-center tw-mt-3">
                                            <span class="tw-text-xs tw-text-neutral-500"><?= _l('appointment_click_to_change_rating'); ?></span>
                                        </div>
                                    <?php
                                    else : ?>
                                        <!-- Allow providing new feedback -->
                                        <div class="tw-text-center">
                                            <p class="tw-text-sm tw-text-neutral-600 tw-mb-4"><?= _l('appointment_leave_feedback'); ?></p>
                                            <div class="tw-flex tw-items-center tw-justify-center tw-gap-2">
                                                <?php
                                                for ($i = 0; $i < 5; $i++) : ?>
                                                    <div class="feedback_star tw-cursor-pointer" data-count="<?= $i; ?>" data-rating="<?= $i + 1; ?>"
                                                        onclick="handle_appointment_feedback(this)">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="tw-h-7 tw-w-7 tw-text-neutral-300" viewBox="0 0 20 20" fill="currentColor">
                                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                        </svg>
                                                    </div>
                                                <?php
                                                endfor; ?>
                                            </div>
                                        </div>
                                    <?php
                                    endif; ?>
                                </div>
                            </div>
                        <?php
                        endif; ?>
                        <?php
                        // Show actions for appointments that can be cancelled OR have pending/completed cancellations
                        $cancellableStatuses    = ['pending', 'in-progress'];
                        $canBeCancelled         = in_array($appointment['status'], $cancellableStatuses, true) && ! $appointment['cancel_notes'];
                        $hasPendingCancellation = ! empty($appointment['cancel_notes']);

                        // Reschedule logic
                        $reschedulableStatuses = ['pending', 'in-progress'];
                        $canBeRescheduled      = in_array($appointment['status'], $reschedulableStatuses, true) && ! $appointment['has_pending_reschedule'];
                        $hasPendingReschedule  = $appointment['has_pending_reschedule'] == 1;

                        // Book again logic for cancelled/no-show appointments
                        $bookAgainStatuses = ['cancelled', 'no-show'];
                        $canBookAgain      = in_array($appointment['status'], $bookAgainStatuses, true);

                        $showActionsSection = in_array(
                            $appointment['status'],
                            $cancellableStatuses,
                            true
                        ) || $hasPendingCancellation || $canBeRescheduled || $hasPendingReschedule || $canBookAgain;

                        if ($showActionsSection) : ?>
                            <div class="tw-mb-6">
                                <h3 class="tw-text-base tw-font-semibold tw-mb-4 tw-text-neutral-800 tw-flex tw-items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="tw-h-5 tw-w-5 tw-mr-2 tw-text-neutral-500" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <?= _l('appointment_actions'); ?>
                                </h3>

                                <?php
                                if ($hasPendingReschedule) : ?>
                                    <!-- Reschedule Status -->
                                    <div class="tw-p-6 tw-bg-white tw-border-l-4 tw-border-l-blue-500 tw-border tw-border-neutral-200 tw-rounded-lg tw-mb-6 tw-shadow-sm">
                                        <div class="tw-flex tw-items-start tw-gap-4">
                                            <div class="tw-flex-shrink-0 tw-w-12 tw-h-12 tw-rounded-full tw-bg-blue-100 tw-flex tw-items-center tw-justify-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="tw-h-6 tw-w-6 tw-text-blue-600" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd"
                                                        d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                            <div class="tw-flex-1">
                                                <h4 class="tw-text-lg tw-font-medium tw-text-neutral-800"><?= _l('appointment_reschedule_requested'); ?></h4>
                                                <p class="tw-text-sm tw-text-neutral-600 tw-mt-1"><?= _l('appointment_reschedule_pending_review'); ?></p>
                                                <?php
                                                if (! empty($appointment['pending_reschedule']['requested_date']) && ! empty($appointment['pending_reschedule']['requested_time'])) : ?>
                                                    <div class="tw-mt-4 tw-p-4 tw-bg-blue-50 tw-rounded-md tw-border tw-border-blue-100">
                                                        <div class="tw-flex tw-items-center tw-mb-2">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="tw-h-5 tw-w-5 tw-text-blue-500 tw-mr-2" viewBox="0 0 20 20" fill="currentColor">
                                                                <path fill-rule="evenodd"
                                                                    d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z"
                                                                    clip-rule="evenodd" />
                                                            </svg>
                                                            <p class="tw-text-sm tw-font-medium tw-text-blue-700"><?= _l('appointment_requested_date'); ?></p>
                                                        </div>
                                                        <p class="tw-text-base tw-text-neutral-800 tw-font-medium tw-ml-7"><?= date('M j, Y', strtotime($appointment['pending_reschedule']['requested_date'])) ?>
                                                            at <?= $appointment['pending_reschedule']['requested_time']; ?></p>
                                                        <?php
                                                        if (! empty($appointment['pending_reschedule']['reason'])) : ?>
                                                            <div class="tw-mt-4 tw-pt-4 tw-border-t tw-border-blue-200">
                                                                <div class="tw-flex tw-items-center tw-mb-2">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" class="tw-h-5 tw-w-5 tw-text-blue-500 tw-mr-2" viewBox="0 0 20 20"
                                                                        fill="currentColor">
                                                                        <path fill-rule="evenodd"
                                                                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                                                            clip-rule="evenodd" />
                                                                    </svg>
                                                                    <p class="tw-text-sm tw-font-medium tw-text-blue-700"><?= _l('appointment_reschedule_reason'); ?></p>
                                                                </div>
                                                                <p class="tw-text-sm tw-text-neutral-700 tw-ml-7"><?= htmlspecialchars($appointment['pending_reschedule']['reason']); ?></p>
                                                            </div>
                                                        <?php
                                                        endif; ?>
                                                    </div>
                                                <?php
                                                endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php
                                endif; ?>

                                <?php
                                if ($hasPendingCancellation) : ?>
                                    <!-- Cancellation Status -->
                                    <div class="tw-p-6 tw-bg-white tw-border-l-4 tw-border-l-red-500 tw-border tw-border-neutral-200 tw-rounded-lg tw-mb-6 tw-shadow-sm">
                                        <div class="tw-flex tw-items-start tw-gap-4">
                                            <div class="tw-flex-shrink-0 tw-w-12 tw-h-12 tw-rounded-full tw-bg-red-100 tw-flex tw-items-center tw-justify-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="tw-h-6 tw-w-6 tw-text-red-600" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd"
                                                        d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                            <div class="tw-flex-1">
                                                <h4 class="tw-text-lg tw-font-medium tw-text-neutral-800"><?= _l('appointment_cancellation_requested'); ?></h4>
                                                <p class="tw-text-sm tw-text-neutral-600 tw-mt-1"><?= _l('appointment_cancellation_pending_review'); ?></p>
                                                <div class="tw-mt-4 tw-p-4 tw-bg-red-50 tw-rounded-md tw-border tw-border-red-100">
                                                    <div class="tw-flex tw-items-center tw-mb-2">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="tw-h-5 tw-w-5 tw-text-red-500 tw-mr-2" viewBox="0 0 20 20" fill="currentColor">
                                                            <path fill-rule="evenodd"
                                                                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                                                clip-rule="evenodd" />
                                                        </svg>
                                                        <p class="tw-text-sm tw-font-medium tw-text-red-700"><?= _l('appointment_cancellation_reason'); ?></p>
                                                    </div>
                                                    <p class="tw-text-sm tw-text-neutral-700 tw-ml-7"><?= htmlspecialchars($appointment['cancel_notes']); ?></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php
                                endif; ?>
                                <?php
                                if (! $hasPendingCancellation && ! $hasPendingReschedule && ! $canBookAgain) : ?>
                                    <!-- Action Buttons for Active Appointments -->
                                    <div class="tw-flex tw-flex-col sm:tw-flex-row tw-gap-3 tw-mt-6">
                                        <?php if ($canBeRescheduled) : ?>
                                            <button type="button"
                                                class="tw-flex-1 tw-px-4 tw-py-2 tw-bg-blue-600 tw-text-white tw-rounded-md tw-text-sm tw-font-medium hover:tw-bg-blue-700 tw-shadow-sm tw-transition-colors tw-flex tw-items-center tw-justify-center"
                                                onclick="openRescheduleModal()">
                                                <i class="fa fa-calendar-alt tw-mr-2"></i>
                                                <?= _l('appointment_reschedule'); ?>
                                            </button>
                                        <?php endif; ?>

                                        <?php if ($canBeCancelled) : ?>
                                            <button type="button"
                                                class="tw-flex-1 tw-px-4 tw-py-2 tw-bg-danger-600 tw-text-white tw-rounded-md tw-text-sm tw-font-medium hover:tw-bg-danger-700 tw-shadow-sm tw-transition-colors tw-flex tw-items-center tw-justify-center"
                                                onclick="openCancelModal()">
                                                <i class="fa fa-times-circle tw-mr-2"></i>
                                                <?= _l('appointment_cancel'); ?>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                <?php elseif ($canBookAgain) : ?>
                                    <!-- Book Again Button for Cancelled/No-Show Appointments -->
                                    <div class="tw-p-4 tw-bg-white tw-border tw-border-neutral-200 tw-rounded-lg tw-shadow-sm tw-mb-4">
                                        <div class="tw-flex tw-items-center tw-justify-between">
                                            <div class="tw-flex tw-items-center tw-gap-3">
                                                <div class="tw-flex-shrink-0 tw-w-10 tw-h-10 tw-rounded-full tw-bg-green-100 tw-flex tw-items-center tw-justify-center">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="tw-h-5 tw-w-5 tw-text-green-600" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd"
                                                            d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z"
                                                            clip-rule="evenodd" />
                                                    </svg>
                                                </div>
                                                <div class="tw-flex-1 tw-min-w-0">
                                                    <h4 class="tw-text-base tw-font-medium tw-text-neutral-800 tw-truncate">
                                                        <?= $appointment['status'] == 'cancelled' ? _l('appointment_cancelled_title') : _l('appointment_no_show_title'); ?>
                                                    </h4>
                                                    <p class="tw-text-sm tw-text-neutral-600 tw-truncate">
                                                        <?= $appointment['status'] == 'cancelled' ? _l('appointment_cancelled_book_again_message') : _l('appointment_no_show_book_again_message'); ?>
                                                    </p>
                                                </div>
                                            </div>
                                            <a href="<?= site_url('appointly/appointments_public/book'); ?>"
                                                class="tw-inline-flex tw-items-center tw-px-3 tw-py-2 tw-bg-green-600 tw-text-white tw-rounded-lg tw-text-xs tw-whitespace-nowrap hover:tw-bg-green-700 hover:tw-text-white tw-transition-all tw-duration-200 tw-shadow-sm hover:tw-shadow-md tw-ml-3">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="tw-h-4 tw-w-4 tw-mr-1.5" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd"
                                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.293l-3-3a1 1 0 00-1.414 1.414L10.586 9H7a1 1 0 100 2h3.586l-1.293 1.293a1 1 0 101.414 1.414l3-3a1 1 0 000-1.414z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                                <?= _l('appointment_book_again'); ?>
                                            </a>
                                        </div>
                                    </div>
                                <?php
                                endif; ?>
                            </div>
                        <?php
                        endif; ?>


                    </div>
                </div>
            </div>
        </div>
        <?php
        app_external_form_footer($form); ?>

        <script>
            // JavaScript configuration for the reschedule functionality
            var site_url = "<?= site_url(); ?>";
            var app = {
                options: {
                    calendar_first_day: <?= get_option('calendar_first_day') ?: 0; ?>
                }
            };

            // Language strings
            var appointlyLang = {
                appointment_loading: "<?= addslashes(_l('appointment_loading')); ?>",
                appointment_select_date: "<?= addslashes(_l('appointment_select_date')); ?>",
                appointment_blocked_days: "<?= addslashes(_l('appointment_blocked_days')); ?>",
                appointment_provider_unavailable: "<?= addslashes(_l('appointment_provider_unavailable')); ?>",
                appointment_available_days: "<?= addslashes(_l('appointment_available_days')); ?>"
            };

            // Simple alert function for compatibility
            function alert_float(type, message) {
                // Create a simple notification
                var alertClass = type === 'success' ? 'tw-bg-green-500' :
                    type === 'warning' ? 'tw-bg-yellow-500' :
                    type === 'danger' ? 'tw-bg-red-500' : 'tw-bg-blue-500';

                var alertDiv = $('<div class="tw-fixed tw-top-4 tw-right-4 tw-z-50 tw-px-4 tw-py-2 tw-rounded tw-text-white tw-shadow-lg ' + alertClass + '">' + message + '</div>');
                $('body').append(alertDiv);

                setTimeout(function() {
                    alertDiv.fadeOut(function() {
                        alertDiv.remove();
                    });
                }, 3000);
            }

            // Custom fields functionality
            var cfh_popover_templates = {};
            window.addEventListener('load', function() {
                if (typeof custom_fields_hyperlink === 'function') {
                    custom_fields_hyperlink();
                }
            });
        </script>

        <?php require 'modules/appointly/assets/js/clients_hash_js.php'; ?>

        <!-- Google Meet Modal -->
        <div id="googleMeetModal" class="modal fade" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-md" role="document">
                <div class="modal-content tw-rounded-lg tw-shadow-lg tw-overflow-hidden" style="margin-top:35%;">
                    <div class="tw-flex tw-items-center tw-justify-between tw-px-5 tw-py-4 tw-border-b tw-border-neutral-200 tw-bg-neutral-50">
                        <h3 class="tw-text-lg tw-font-semibold tw-text-neutral-800">
                            <div class="tw-flex tw-items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="tw-h-5 tw-w-5 tw-mr-2 tw-text-green-600" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M2 6a2 2 0 012-2h6a2 2 0 012 2v4a2 2 0 01-2 2H9l-3 3v-3H4a2 2 0 01-2-2V6zM14.553 7.106A1 1 0 0014 8v4a1 1 0 00.553.894l2 1A1 1 0 0018 13V7a1 1 0 00-1.447-.894l-2 1z" />
                                </svg>
                                <?= _l('appointment_google_meet'); ?>
                            </div>
                        </h3>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="tw-p-5" id="googleMeetContent">
                        <div class="tw-grid tw-grid-cols-1 tw-gap-4">
                            <!-- Enhanced Google Meet -->
                            <?php
                            if (! empty($appointment['google_meet_link'])) : ?>
                                <div class="tw-bg-white tw-p-4 tw-rounded-lg tw-border tw-border-neutral-200 tw-shadow-sm">
                                    <div class="tw-mb-4">
                                        <p class="tw-text-sm tw-text-neutral-600">
                                            <?= _l('appointment_google_meet_info_2'); ?>
                                        </p>
                                    </div>

                                    <div class="tw-flex tw-flex-col tw-gap-3">
                                        <a href="<?= $appointment['google_meet_link']; ?>"
                                            target="_blank"
                                            class="tw-flex tw-items-center tw-justify-center tw-w-full tw-px-4 tw-py-2.5 tw-bg-green-600 tw-text-white hover:tw-text-white tw-text-sm tw-font-medium tw-rounded-lg hover:tw-bg-green-700 tw-transition-colors tw-duration-200">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="tw-h-5 tw-w-5 tw-mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                            </svg>
                                            <?= _l('appointment_google_meet_quick_join'); ?>
                                        </a>

                                        <button onclick="copyClientGoogleMeetLink('<?= addslashes($appointment['google_meet_link']); ?>')"
                                            class="tw-flex tw-items-center tw-justify-center tw-w-full tw-px-4 tw-py-2.5 tw-bg-white tw-border tw-border-neutral-300 tw-text-neutral-700 tw-text-sm tw-font-medium tw-rounded-lg hover:tw-bg-neutral-50 tw-transition-colors tw-duration-200">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="tw-h-5 tw-w-5 tw-mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                            </svg>
                                            <?= _l('appointment_google_meet_copy_link'); ?>
                                        </button>
                                    </div>

                                    <hr class="tw-my-4 tw-border-neutral-200">

                                    <div class="tw-grid tw-grid-cols-2 tw-gap-4">
                                        <div class="tw-flex tw-items-start">
                                            <div class="tw-flex-shrink-0 tw-mt-0.5">
                                                <svg class="tw-w-5 tw-h-5 tw-text-green-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                                    <path d="M15 10.5c0 .825-.675 1.5-1.5 1.5S12 11.325 12 10.5 12.675 9 13.5 9s1.5.675 1.5 1.5zm-5-1c-.55 0-1 .45-1 1s.45 1 1 1 1-.45 1-1-.45-1-1-1zm-6 2.75c0-2.068 1.682-3.75 3.75-3.75.46 0 .9.085 1.31.22C10.13 7.615 11.7 7 13.5 7c3.025 0 5.5 2.475 5.5 5.5v.5c0 2.25-1.57 4.125-3.66 4.625l1.49 1.49c.19.19.19.5 0 .7-.19.19-.5.19-.7 0l-2.13-2.13V20h-2v-2h-2v2H8v-2.8l-2.24 2.24c-.19.19-.5.19-.7 0-.19-.19-.19-.5 0-.7l1.47-1.47C4.575 16.375 3 14.075 3 11.5v-.75z" />
                                                </svg>
                                            </div>
                                            <div class="tw-ml-3">
                                                <p class="tw-text-sm tw-font-medium tw-text-neutral-800"><?= _l('appointment_google_meet_hd_video_audio'); ?></p>
                                                <p class="tw-text-xs tw-text-green-600 tw-font-medium"><?= _l('appointment_google_meet_always_enabled'); ?></p>
                                            </div>
                                        </div>

                                        <div class="tw-flex tw-items-start">
                                            <div class="tw-flex-shrink-0 tw-mt-0.5">
                                                <svg class="tw-w-5 tw-h-5 tw-text-green-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                                    <path d="M20 18c1.1 0 1.99-.9 1.99-2L22 6c0-1.1-.9-2-2-2H4c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2H0v2h24v-2h-4zM4 6h16v10H4V6z" />
                                                </svg>
                                            </div>
                                            <div class="tw-ml-3">
                                                <p class="tw-text-sm tw-font-medium tw-text-neutral-800"><?= _l('appointment_google_meet_share_screen'); ?></p>
                                                <p class="tw-text-xs tw-text-green-600 tw-font-medium"><?= _l('appointment_google_meet_always_enabled'); ?></p>
                                            </div>
                                        </div>

                                        <?php if (get_option('appointly_google_meet_recording') == '1'): ?>
                                            <div class="tw-flex tw-items-start">
                                                <div class="tw-flex-shrink-0 tw-mt-0.5">
                                                    <svg class="tw-w-5 tw-h-5 tw-text-blue-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                                        <path d="M12 14c1.66 0 3-1.34 3-3V5c0-1.66-1.34-3-3-3S9 3.34 9 5v6c0 1.66 1.34 3 3 3zm5.91-3c-.49 0-.9.36-.98.85C16.52 14.2 14.47 16 12 16s-4.52-1.8-4.93-4.15c-.08-.49-.49-.85-.98-.85-.61 0-1.09.54-1 1.14.49 3 2.89 5.35 5.91 5.78V20c0 .55.45 1 1 1s1-.45 1-1v-2.08c3.02-.43 5.42-2.78 5.91-5.78.1-.6-.39-1.14-1-1.14z" />
                                                    </svg>
                                                </div>
                                                <div class="tw-ml-3">
                                                    <p class="tw-text-sm tw-font-medium tw-text-neutral-800"><?= _l('appointment_google_meet_recording_enabled'); ?></p>
                                                    <p class="tw-text-xs tw-text-blue-500 tw-font-medium">
                                                        <?php if (get_option('appointly_google_meet_recording') == '1'): ?>
                                                            <?= _l('appointly_enabled'); ?>
                                                        <?php else: ?>
                                                            <?= _l('appointly_disabled'); ?>
                                                        <?php endif; ?>
                                                    </p>
                                                </div>
                                            </div>
                                        <?php endif; ?>

                                        <div class="tw-flex tw-items-start">
                                            <div class="tw-flex-shrink-0 tw-mt-0.5">
                                                <svg class="tw-w-5 tw-h-5 tw-text-amber-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                                    <path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8z" />
                                                    <path d="M12.5 7H11v6l5.25 3.15.75-1.23-4.5-2.67z" />
                                                </svg>
                                            </div>
                                            <div class="tw-ml-3">
                                                <p class="tw-text-sm tw-font-medium tw-text-neutral-800"><?= _l('appointment_google_meet_waiting_room_enabled_status'); ?></p>
                                                <p class="tw-text-xs tw-text-amber-500 tw-font-medium">
                                                    <?php if (get_option('appointly_google_meet_waiting_room') == '1'): ?>
                                                        <?= _l('appointly_enabled'); ?>
                                                    <?php else: ?>
                                                        <?= _l('appointly_disabled'); ?>
                                                    <?php endif; ?>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Client-side JavaScript for Google Meet -->
                                <script>
                                    function copyClientGoogleMeetLink(meetLink) {
                                        if (navigator.clipboard && window.isSecureContext) {
                                            navigator.clipboard.writeText(meetLink).then(function() {
                                                showClientNotification('Google Meet link copied to clipboard!', 'success');
                                            }).catch(function(err) {
                                                fallbackCopyTextToClipboard(meetLink);
                                            });
                                        } else {
                                            fallbackCopyTextToClipboard(meetLink);
                                        }
                                    }

                                    function fallbackCopyTextToClipboard(text) {
                                        var textArea = document.createElement("textarea");
                                        textArea.value = text;
                                        textArea.style.position = "fixed";
                                        textArea.style.top = "0";
                                        textArea.style.left = "0";
                                        document.body.appendChild(textArea);
                                        textArea.focus();
                                        textArea.select();

                                        showClientNotification('Google Meet link copied to clipboard!', 'success');

                                        document.body.removeChild(textArea);
                                    }

                                    // Google Meet modal functions
                                    function openGoogleMeetModal() {
                                        $('#googleMeetModal').modal('show');
                                    }

                                    function closeGoogleMeetModal() {
                                        $('#googleMeetModal').modal('hide');
                                    }

                                    function showClientNotification(message, type) {
                                        var alertClass = type === 'success' ? 'tw-bg-green-100 tw-text-green-800 tw-border-green-200' : 'tw-bg-yellow-100 tw-text-yellow-800 tw-border-yellow-200';
                                        var notification = $('<div class="tw-fixed tw-top-4 tw-right-4 tw-z-50 tw-max-w-sm tw-p-4 tw-rounded-lg tw-border tw-shadow-lg ' + alertClass + '">' +
                                            '<div class="tw-flex tw-items-center">' +
                                            '<span class="tw-text-sm tw-font-medium">' + message + '</span>' +
                                            '<button type="button" class="tw-ml-4 tw-text-gray-400 hover:tw-text-gray-600" onclick="$(this).parent().parent().remove()">' +
                                            '<svg class="tw-h-4 tw-w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">' +
                                            '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />' +
                                            '</svg>' +
                                            '</button>' +
                                            '</div>' +
                                            '</div>');

                                        $('body').append(notification);

                                        setTimeout(function() {
                                            notification.fadeOut(function() {
                                                $(this).remove();
                                            });
                                        }, 3000);
                                    }
                                </script>
                            <?php
                            endif; ?>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">
                            <?= _l('close'); ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cancellation Modal -->
        <div id="cancelModal" class="tw-hidden tw-fixed tw-inset-0 tw-bg-black/50 tw-z-50 tw-overflow-y-auto tw-flex tw-items-center tw-justify-center">
            <div class="tw-bg-white tw-rounded-lg tw-shadow-lg tw-max-w-md tw-w-full tw-mx-4 tw-overflow-hidden">
                <div class="tw-flex tw-items-center tw-justify-between tw-px-5 tw-py-4 tw-border-b tw-border-neutral-200 tw-bg-neutral-50">
                    <h3 class="tw-text-lg tw-font-semibold tw-text-neutral-800">
                        <div class="tw-flex tw-items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="tw-h-5 tw-w-5 tw-mr-2 tw-text-danger-500" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                    clip-rule="evenodd" />
                            </svg>
                            <?= _l('appointment_cancel'); ?>
                        </div>
                    </h3>
                    <button type="button" onclick="closeCancelModal()" class="tw-text-neutral-500 hover:tw-text-neutral-700 tw-bg-transparent tw-rounded-full tw-p-1 hover:tw-bg-neutral-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="tw-h-5 tw-w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
                <div class="tw-p-5">
                    <div id="alert" class="tw-hidden tw-p-4 tw-mb-4 tw-rounded-md"></div>

                    <label for="notes" class="tw-block tw-text-sm tw-font-medium tw-mb-2 tw-text-neutral-700"><?= _l('appointment_cancellation_description_label'); ?></label>
                    <textarea id="notes" rows="4"
                        class="tw-w-full tw-px-3 tw-py-2 tw-border tw-border-neutral-300 tw-rounded-md tw-shadow-sm focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-neutral-500 focus:tw-border-neutral-500"
                        placeholder="<?= _l('appointment_description_to_cancel'); ?>"></textarea>
                </div>
                <div class="tw-flex tw-justify-end tw-p-4 tw-bg-neutral-50 tw-border-t tw-border-neutral-200">
                    <button onclick="closeCancelModal()"
                        class="tw-px-4 tw-py-2 tw-bg-white tw-text-neutral-700 tw-border tw-border-neutral-300 tw-rounded-md tw-mr-2 tw-text-sm tw-font-medium hover:tw-bg-neutral-50 tw-shadow-sm">
                        <?= _l('close'); ?>
                    </button>
                    <button id="cancelAppointmentForm" onclick="submitCancellation()"
                        class="tw-px-4 tw-py-2 tw-bg-danger-600 tw-text-white tw-rounded-md tw-text-sm tw-font-medium hover:tw-bg-danger-700 tw-shadow-sm">
                        <?= _l('appointment_request_to_cancel'); ?>
                    </button>
                </div>
            </div>
        </div>

        <!-- Review Modal -->
        <div id="reviewModal" class="tw-hidden tw-fixed tw-inset-0 tw-bg-black/50 tw-z-50 tw-overflow-y-auto tw-flex tw-items-center tw-justify-center">
            <div class="tw-bg-white tw-rounded-lg tw-shadow-lg tw-max-w-md tw-w-full tw-mx-4 tw-overflow-hidden">
                <div class="tw-flex tw-items-center tw-justify-between tw-px-5 tw-py-4 tw-border-b tw-border-neutral-200 tw-bg-neutral-50">
                    <h3 class="tw-text-lg tw-font-semibold tw-text-neutral-800">
                        <div class="tw-flex tw-items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="tw-h-5 tw-w-5 tw-mr-2 tw-text-neutral-500" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                            <?= _l('appointment_your_feedback'); ?>
                        </div>
                    </h3>
                    <button type="button" onclick="closeReviewModal()" class="tw-text-neutral-500 hover:tw-text-neutral-700 tw-bg-transparent tw-rounded-full tw-p-1 hover:tw-bg-neutral-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="tw-h-5 tw-w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
                <div class="tw-p-5">
                    <div id="review-alert" class="tw-mb-3 tw-rounded-md"></div>

                    <label for="feedback_comment" class="tw-block tw-text-sm tw-font-medium tw-mb-2 tw-text-neutral-700"><?= _l('appointmenet_feedback_comment'); ?></label>
                    <textarea id="feedback_comment" rows="4"
                        class="tw-w-full tw-px-3 tw-py-2 tw-border tw-border-neutral-300 tw-rounded-md tw-shadow-sm focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-neutral-500 focus:tw-border-neutral-500"
                        placeholder="<?= _l('appointment_feedback_comment_textarea'); ?>"></textarea>
                </div>
                <div class="tw-flex tw-justify-end tw-p-4 tw-bg-neutral-50 tw-border-t tw-border-neutral-200">
                    <button onclick="closeReviewModal()"
                        class="tw-px-4 tw-py-2 tw-bg-white tw-text-neutral-700 tw-border tw-border-neutral-300 tw-rounded-md tw-mr-2 tw-text-sm tw-font-medium hover:tw-bg-neutral-50 tw-shadow-sm">
                        <?= _l('close'); ?>
                    </button>
                    <button id="reviewModalSubmitBtn" class="tw-px-4 tw-py-2 tw-bg-neutral-600 tw-text-white tw-rounded-md tw-text-sm tw-font-medium hover:tw-bg-neutral-700 tw-shadow-sm">
                        <?= _l('submit'); ?>
                    </button>
                </div>
            </div>
        </div>

        <!-- Reschedule Modal -->
        <div id="rescheduleModal" class="tw-hidden tw-fixed tw-inset-0 tw-bg-black/50 tw-z-50 tw-overflow-y-auto tw-flex tw-items-center tw-justify-center">
            <div class="tw-bg-white tw-rounded-lg tw-shadow-lg tw-max-w-md tw-w-full tw-mx-4 tw-overflow-hidden">
                <div class="tw-flex tw-items-center tw-justify-between tw-px-5 tw-py-4 tw-border-b tw-border-neutral-200 tw-bg-neutral-50">
                    <h3 class="tw-text-lg tw-font-semibold tw-text-neutral-800">
                        <div class="tw-flex tw-items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="tw-h-5 tw-w-5 tw-mr-2 tw-text-blue-500" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z"
                                    clip-rule="evenodd" />
                            </svg>
                            <?= _l('appointment_reschedule'); ?>
                        </div>
                    </h3>
                    <button type="button" onclick="closeRescheduleModal()"
                        class="tw-text-neutral-500 hover:tw-text-neutral-700 tw-bg-transparent tw-rounded-full tw-p-1 hover:tw-bg-neutral-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="tw-h-5 tw-w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
                <div class="tw-p-5">
                    <div id="reschedule-alert" class="tw-hidden tw-p-4 tw-mb-4 tw-rounded-md"></div>

                    <!-- Current appointment details -->
                    <div class="tw-mb-4 tw-p-3 tw-bg-neutral-50 tw-rounded-md tw-border tw-border-neutral-200">
                        <p class="tw-text-xs tw-text-neutral-600 tw-mb-1"><?= _l('appointment_current_details'); ?>:</p>
                        <p class="tw-text-sm tw-text-neutral-800 tw-font-medium"><?= date('M j, Y', strtotime($appointment['date'])); ?> at <?= $appointment['start_hour']; ?></p>
                    </div>

                    <!-- New date -->
                    <div class="tw-mb-4">
                        <label for="reschedule_date" class="tw-block tw-text-sm tw-font-medium tw-mb-2 tw-text-neutral-700"><?= _l('appointment_new_date'); ?></label>
                        <div class="tw-relative">
                            <input type="text" id="reschedule_date" class="form-control tw-w-full" placeholder="<?= _l('appointment_select_date'); ?>" readonly>
                            <div id="reschedule_date_loading" class="tw-absolute tw-right-3 tw-top-1/2 tw-transform -tw-translate-y-1/2 tw-hidden">
                                <div class="tw-w-5 tw-h-5 tw-border-2 tw-border-blue-500 tw-border-t-transparent tw-rounded-full tw-animate-spin"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Time Slots Section (initially hidden) -->
                    <div id="reschedule-time-slots-section" class="tw-mb-4 tw-hidden">
                        <label class="tw-block tw-text-sm tw-font-medium tw-mb-2 tw-text-neutral-700"><?= _l('appointment_new_time'); ?></label>
                        <div id="reschedule-time-slots-container" class="tw-grid tw-grid-cols-2 sm:tw-grid-cols-3 tw-gap-2">
                        </div>
                        <!-- Loading indicator for time slots -->
                        <div id="reschedule-slot-loading" class="tw-text-center tw-py-8 tw-hidden">
                            <div class="tw-w-8 tw-h-8 tw-mx-auto tw-border-2 tw-border-blue-500 tw-border-t-transparent tw-rounded-full tw-animate-spin"></div>
                            <p class="tw-mt-2 tw-text-sm tw-text-neutral-600"><?= _l('appointment_loading'); ?></p>
                        </div>
                    </div>

                    <!-- Hidden inputs to store selected date and time -->
                    <input type="hidden" id="reschedule_date_field" name="reschedule_date" value="">
                    <input type="hidden" id="reschedule_time_field" name="reschedule_time" value="">

                    <!-- Reason -->
                    <div class="tw-mb-4">
                        <label for="reschedule_reason" class="tw-block tw-text-sm tw-font-medium tw-mb-2 tw-text-neutral-700"><?= _l('appointment_reschedule_reason'); ?></label>
                        <textarea id="reschedule_reason" rows="3"
                            class="tw-w-full tw-px-3 tw-py-2 tw-border tw-border-neutral-300 tw-rounded-md tw-shadow-sm focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-blue-500 focus:tw-border-blue-500"
                            placeholder="<?= _l('appointment_reschedule_request_details'); ?>"></textarea>
                    </div>
                </div>
                <div class="tw-p-4 tw-bg-neutral-50 tw-border-t tw-border-neutral-200">
                    <div class="tw-flex tw-gap-3">
                        <button onclick="closeRescheduleModal()"
                            class="tw-flex-1 tw-py-3 tw-bg-white tw-text-neutral-700 tw-border tw-border-neutral-300 tw-rounded-md tw-text-sm tw-font-medium hover:tw-bg-neutral-50 tw-shadow-sm tw-transition-colors">
                            <?= _l('close'); ?>
                        </button>
                        <button id="rescheduleSubmitBtn" onclick="submitReschedule()"
                            class="tw-flex-1 tw-py-3 tw-bg-blue-600 tw-text-white tw-rounded-md tw-text-sm tw-font-medium hover:tw-bg-blue-700 tw-shadow-sm tw-transition-colors">
                            <?= _l('appointment_request_reschedule'); ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
</body>

</html>
