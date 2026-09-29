<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php
// ========================================
// SUCCESS PAGE CUSTOMIZATION CONFIG
// ========================================
// Modify these settings to customize the appearance of your success page
// All colors use Tailwind CSS color names (e.g., 'blue', 'emerald', 'slate')

$config = [
    // BACKGROUND SETTINGS
    'background' => [
        'gradient_from' => 'slate-100',      // Background gradient start color
        'gradient_to' => 'slate-200',        // Background gradient end color
    ],

    // HEADER SETTINGS
    'header' => [
        'gradient_from' => 'emerald-500',    // Header gradient start color
        'gradient_to' => 'emerald-600',      // Header gradient end color
        'text_color' => 'white',             // Header text color
        'icon_size' => 'w-10 h-10',          // Success icon size
    ],

    // STATUS BADGE SETTINGS
    'status_badge' => [
        'bg_color' => 'amber-100',           // Badge background color
        'text_color' => 'amber-800',         // Badge text color
        'text' => 'Pending Approval',       // Badge text
    ],

    // APPOINTMENT DETAILS CARD COLORS
    'appointment_cards' => [
        'subject' => [
            'bg_color' => 'slate-50',        // Subject card background
            'border_color' => 'slate-500',   // Subject card border
            'icon_color' => 'slate-600',     // Subject card icon
            'label_color' => 'slate-500',    // Subject card label
        ],
        'datetime' => [
            'bg_color' => 'blue-50',         // Date/Time card background
            'border_color' => 'blue-500',    // Date/Time card border
            'icon_color' => 'blue-600',      // Date/Time card icon
            'label_color' => 'blue-500',     // Date/Time card label
        ],
        'service' => [
            'bg_color' => 'emerald-50',      // Service card background
            'border_color' => 'emerald-500', // Service card border
            'icon_color' => 'emerald-600',   // Service card icon
            'label_color' => 'emerald-500',  // Service card label
        ],
        'provider' => [
            'bg_color' => 'violet-50',       // Provider card background
            'border_color' => 'violet-500',  // Provider card border
            'icon_color' => 'violet-600',    // Provider card icon
            'label_color' => 'violet-500',   // Provider card label
        ],
        'location' => [
            'bg_color' => 'orange-50',       // Location card background
            'border_color' => 'orange-500',  // Location card border
            'icon_color' => 'orange-600',    // Location card icon
            'label_color' => 'orange-500',   // Location card label
        ],
    ],

    // NEXT STEPS SECTION
    'next_steps' => [
        'icon_color' => 'slate-600',         // Section icon color
        'number_bg' => 'slate-600',          // Step number background
        'number_text' => 'white',            // Step number text color
    ],

    // ACTION BUTTON
    'button' => [
        'gradient_from' => 'emerald-600',    // Button gradient start
        'gradient_to' => 'emerald-700',      // Button gradient end
        'hover_from' => 'emerald-700',       // Button hover gradient start
        'hover_to' => 'emerald-800',         // Button hover gradient end
        'text_color' => 'white',             // Button text color
    ],

    // LAYOUT SETTINGS
    'layout' => [
        'max_width' => 'max-w-4xl',          // Container max width (max-w-2xl, max-w-4xl, max-w-6xl)
        'card_rounded' => 'rounded-2xl',     // Card border radius (rounded-lg, rounded-xl, rounded-2xl)
        'card_shadow' => 'shadow-2xl',       // Card shadow (shadow-lg, shadow-xl, shadow-2xl)
        'padding' => 'p-8',                  // Section padding (p-6, p-8, p-10)
    ],

    // TYPOGRAPHY
    'typography' => [
        'title_size' => 'text-3xl md:text-4xl',     // Main title size
        'section_title_size' => 'text-2xl',         // Section title size
        'detail_text_size' => 'text-lg',            // Detail text size
        'step_text_size' => 'text-lg',              // Step text size
    ],
];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="user-scalable=no, width=device-width, initial-scale=1, maximum-scale=1">
    <title><?= _l('appointment_successfully_scheduled') ?></title>
    <?php app_external_form_header($form); ?>

    <!-- Tailwind CSS CDN -->

    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }
    </style>
</head>

<body class="bg-gradient-to-br from-<?= $config['background']['gradient_from'] ?> to-<?= $config['background']['gradient_to'] ?> min-h-screen">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="w-full <?= $config['layout']['max_width'] ?>">
            <!-- Success Header -->
            <div class="bg-gradient-to-r from-<?= $config['header']['gradient_from'] ?> to-<?= $config['header']['gradient_to'] ?> rounded-t-2xl text-<?= $config['header']['text_color'] ?> text-center py-12 px-6">
                <div class="w-20 h-20 bg-white bg-opacity-20 rounded-full flex items-center justify-center mx-auto mb-6 backdrop-blur-sm">
                    <svg class="<?= $config['header']['icon_size'] ?> text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <h1 class="<?= $config['typography']['title_size'] ?> font-bold mb-3">
                    <?= isset($title) ? $title : _l('appointment_successfully_scheduled') ?>
                </h1>
                <p class="text-lg text-<?= $config['header']['gradient_from'] ?>-50 mb-6">
                    <?= _l('appointment_pending_approval_message') ?: 'Your appointment request has been received and is pending approval.' ?>
                </p>
                <div class="inline-flex items-center px-4 py-2 bg-<?= $config['status_badge']['bg_color'] ?> text-<?= $config['status_badge']['text_color'] ?> rounded-full text-sm font-medium">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <?= $config['status_badge']['text'] ?>
                </div>
            </div>

            <!-- Content Section -->
            <div class="bg-white <?= $config['layout']['card_rounded'] ?> <?= $config['layout']['card_shadow'] ?>">
                <!-- Appointment Details -->
                <?php if (isset($appointment) && $appointment): ?>
                    <div class="<?= $config['layout']['padding'] ?> border-b border-gray-100">
                        <div class="flex items-center mb-6">
                            <svg class="w-6 h-6 text-<?= $config['header']['gradient_from'] ?> mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <h2 class="<?= $config['typography']['section_title_size'] ?> font-bold text-gray-900">Appointment Details</h2>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Subject -->
                            <div class="bg-<?= $config['appointment_cards']['subject']['bg_color'] ?> rounded-xl p-6 border-l-4 border-<?= $config['appointment_cards']['subject']['border_color'] ?>">
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-<?= $config['appointment_cards']['subject']['icon_color'] ?> mt-1 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                                    </svg>
                                    <div>
                                        <p class="text-sm font-medium text-<?= $config['appointment_cards']['subject']['label_color'] ?> uppercase tracking-wide mb-1">Subject</p>
                                        <p class="<?= $config['typography']['detail_text_size'] ?> font-semibold text-gray-900"><?= $appointment['subject'] ?? 'Not specified' ?></p>
                                    </div>
                                </div>
                            </div>

                            <!-- Date & Time -->
                            <div class="bg-<?= $config['appointment_cards']['datetime']['bg_color'] ?> rounded-xl p-6 border-l-4 border-<?= $config['appointment_cards']['datetime']['border_color'] ?>">
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-<?= $config['appointment_cards']['datetime']['icon_color'] ?> mt-1 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    <div>
                                        <p class="text-sm font-medium text-<?= $config['appointment_cards']['datetime']['label_color'] ?> uppercase tracking-wide mb-1">Date & Time</p>
                                        <p class="<?= $config['typography']['detail_text_size'] ?> font-semibold text-gray-900">
                                            <?php
                                            if (isset($appointment['date']) && isset($appointment['start_hour'])) {
                                                echo date('F j, Y', strtotime($appointment['date'])) . ' at ' . date('g:i A', strtotime($appointment['start_hour']));
                                            } else {
                                                echo 'To be confirmed';
                                            }
                                            ?>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Service -->
                            <?php if (isset($appointment['service_name']) && $appointment['service_name']): ?>
                                <div class="bg-<?= $config['appointment_cards']['service']['bg_color'] ?> rounded-xl p-6 border-l-4 border-<?= $config['appointment_cards']['service']['border_color'] ?>">
                                    <div class="flex items-start">
                                        <svg class="w-5 h-5 text-<?= $config['appointment_cards']['service']['icon_color'] ?> mt-1 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2-2v2m8 0V6a2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2V8a2 2 0 012-2V6z"></path>
                                        </svg>
                                        <div>
                                            <p class="text-sm font-medium text-<?= $config['appointment_cards']['service']['label_color'] ?> uppercase tracking-wide mb-1">Service</p>
                                            <p class="<?= $config['typography']['detail_text_size'] ?> font-semibold text-gray-900"><?= $appointment['service_name'] ?></p>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Provider -->
                            <?php if (isset($appointment['provider_name']) && $appointment['provider_name']): ?>
                                <div class="bg-<?= $config['appointment_cards']['provider']['bg_color'] ?> rounded-xl p-6 border-l-4 border-<?= $config['appointment_cards']['provider']['border_color'] ?>">
                                    <div class="flex items-start">
                                        <svg class="w-5 h-5 text-<?= $config['appointment_cards']['provider']['icon_color'] ?> mt-1 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                        <div>
                                            <p class="text-sm font-medium text-<?= $config['appointment_cards']['provider']['label_color'] ?> uppercase tracking-wide mb-1">Provider</p>
                                            <p class="<?= $config['typography']['detail_text_size'] ?> font-semibold text-gray-900"><?= $appointment['provider_name'] ?></p>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Location -->
                            <?php if (isset($appointment['address']) && $appointment['address']): ?>
                                <div class="bg-<?= $config['appointment_cards']['location']['bg_color'] ?> rounded-xl p-6 border-l-4 border-<?= $config['appointment_cards']['location']['border_color'] ?> md:col-span-2">
                                    <div class="flex items-start">
                                        <svg class="w-5 h-5 text-<?= $config['appointment_cards']['location']['icon_color'] ?> mt-1 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                        <div>
                                            <p class="text-sm font-medium text-<?= $config['appointment_cards']['location']['label_color'] ?> uppercase tracking-wide mb-1">Location</p>
                                            <p class="<?= $config['typography']['detail_text_size'] ?> font-semibold text-gray-900"><?= $appointment['address'] ?></p>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Invoice Link Section (for logged in users when setting is enabled) -->
                <?php if (
                    get_option('appointly_show_invoice_option') == '1' &&
                    isset($appointment) &&
                    $appointment &&
                    !empty($appointment['contact_id']) &&
                    !empty($appointment['invoice_id'])
                ):
                    // Get invoice hash from database
                    $CI = &get_instance();
                    $CI->db->select('hash');
                    $CI->db->where('id', $appointment['invoice_id']);
                    $invoice_hash_result = $CI->db->get(db_prefix() . 'invoices')->row();
                    $invoice_hash = $invoice_hash_result ? $invoice_hash_result->hash : '';
                ?>
                    <div class="<?= $config['layout']['padding'] ?> border-b border-gray-100">
                        <div class="flex items-center mb-6">
                            <svg class="w-6 h-6 text-<?= $config['header']['gradient_from'] ?> mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <h2 class="<?= $config['typography']['section_title_size'] ?> font-bold text-gray-900">Invoice</h2>
                        </div>

                        <div class="bg-blue-50 rounded-xl p-6 border-l-4 border-blue-500">
                            <div class="flex items-start">
                                <svg class="w-5 h-5 text-blue-600 mt-1 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-blue-800 uppercase tracking-wide mb-2">Invoice</p>
                                    <p class="<?= $config['typography']['detail_text_size'] ?> font-semibold text-gray-900 mb-3">
                                        <?= _l('invoice_created_for_appointment', format_invoice_number($appointment['invoice_id'])); ?>
                                    </p>
                                    <?php if ($invoice_hash): ?>
                                        <a href="<?= site_url('invoice/' . $appointment['invoice_id'] . '/' . $invoice_hash); ?>"
                                            target="_blank"
                                            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700 hover:text-white transition-colors">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                            </svg>
                                            View Invoice
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Next Steps -->
                <div class="<?= $config['layout']['padding'] ?> border-b border-gray-100">
                    <div class="flex items-center mb-6">
                        <svg class="w-6 h-6 text-<?= $config['next_steps']['icon_color'] ?> mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <h2 class="<?= $config['typography']['section_title_size'] ?> font-bold text-gray-900">What Happens Next?</h2>
                    </div>

                    <div class="space-y-4">
                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-8 h-8 bg-<?= $config['next_steps']['number_bg'] ?> text-<?= $config['next_steps']['number_text'] ?> rounded-full flex items-center justify-center text-sm font-bold mr-4">1</div>
                            <p class="text-gray-700 <?= $config['typography']['step_text_size'] ?>">Our team will review your appointment request within 24 hours.</p>
                        </div>
                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-8 h-8 bg-<?= $config['next_steps']['number_bg'] ?> text-<?= $config['next_steps']['number_text'] ?> rounded-full flex items-center justify-center text-sm font-bold mr-4">2</div>
                            <p class="text-gray-700 <?= $config['typography']['step_text_size'] ?>">You will receive an email confirmation once your appointment is approved.</p>
                        </div>
                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-8 h-8 bg-<?= $config['next_steps']['number_bg'] ?> text-<?= $config['next_steps']['number_text'] ?> rounded-full flex items-center justify-center text-sm font-bold mr-4">3</div>
                            <p class="text-gray-700 <?= $config['typography']['step_text_size'] ?>">Please prepare any necessary documents or information for your appointment.</p>
                        </div>
                    </div>
                </div>

                <!-- Action Button -->
                <div class="<?= $config['layout']['padding'] ?> text-center">
                    <a href="<?= site_url('appointly/appointments_public/book') ?>"
                        class="inline-flex items-center px-8 py-4 bg-gradient-to-r from-<?= $config['button']['gradient_from'] ?> to-<?= $config['button']['gradient_to'] ?> text-<?= $config['button']['text_color'] ?> hover:text-<?= $config['button']['text_color'] ?> font-semibold rounded-xl hover:from-<?= $config['button']['hover_from'] ?> hover:to-<?= $config['button']['hover_to'] ?> transform hover:-translate-y-1 transition-all duration-200 shadow-lg hover:shadow-xl">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        <?= _l('appointment_schedule_another') ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
    <?php app_external_form_footer($form); ?>
</body>

</html>
