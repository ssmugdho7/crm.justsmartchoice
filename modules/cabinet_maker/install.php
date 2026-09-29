<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Cabinet Maker activation bootstrap.
 *
 * Activation must never run schema rebuilds, catalog imports, or destructive
 * operations. Perfex activates the module first; migration 127 performs the
 * idempotent database repair through the normal Upgrade Database workflow.
 */
$defaults = [
    'cabinet_maker_version'             => CABINET_MAKER_VERSION,
    'cabinet_maker_default_vendor_tier' => 'core',
    'cabinet_maker_units'               => 'in',
    'cabinet_maker_sheet_width'         => '48',
    'cabinet_maker_sheet_height'        => '96',
    'cabinet_maker_kerf'                => '0.125',
    'cabinet_maker_default_depth_base'  => '24',
    'cabinet_maker_default_depth_wall'  => '12',
    'cabinet_maker_enable_ai'           => '1',
    'cabinet_maker_ai_image_model'      => 'gpt-image-1',
    'cabinet_maker_share_expiry_days'   => '30',
];

foreach ($defaults as $name => $value) {
    $current = get_option($name);
    if ($current === false || $current === null || $current === '') {
        add_option($name, $value);
    }
}

update_option('cabinet_maker_version', CABINET_MAKER_VERSION);
update_option('cabinet_maker_activation_status', 'activated_pending_database_upgrade');
