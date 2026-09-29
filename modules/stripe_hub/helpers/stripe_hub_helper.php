<?php

defined('BASEPATH') or exit('No direct script access allowed');

function stripe_hub_can_view()
{
    return is_admin() || staff_can('view', 'stripe_hub') || staff_can('view_own', 'stripe_hub');
}

function stripe_hub_require($capability)
{
    if (is_admin() || staff_can($capability, 'stripe_hub')) {
        return;
    }
    access_denied('stripe_hub');
}

function stripe_hub_money($amount, $currency = 'USD')
{
    return strtoupper($currency) . ' ' . number_format(((float) $amount) / 100, 2);
}

function stripe_hub_mask_key($value)
{
    if (!$value) return '';
    $len = strlen($value);
    if ($len <= 8) return str_repeat('•', $len);
    return substr($value, 0, 7) . str_repeat('•', max(4, $len - 11)) . substr($value, -4);
}
