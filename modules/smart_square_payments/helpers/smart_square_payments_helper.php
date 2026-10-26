<?php

defined('BASEPATH') or exit('No direct script access allowed');

function smart_square_idempotency_key($invoiceId, $source, $minorUnits)
{
    // Square permits at most 45 characters; keep 160 bits of the digest.
    return substr(hash('sha256', implode('|', ['smart-square', (string) $invoiceId, $source, (string) $minorUnits])), 0, 40);
}

/**
 * Convert a non-negative decimal currency amount to integer minor units.
 *
 * @param mixed $amount
 * @return int|null
 */
function smart_square_minor_units($amount)
{
    $normalized = trim((string) $amount);
    if (!preg_match('/^(?:0|[1-9][0-9]*)(?:\.([0-9]{1,2}))?$/D', $normalized, $matches)) {
        return null;
    }

    $whole = strstr($normalized, '.', true);
    if ($whole === false) {
        $whole = $normalized;
    }
    $fraction = isset($matches[1]) ? str_pad($matches[1], 2, '0') : '00';

    if (strlen($whole) > 16) {
        return null;
    }

    return ((int) $whole * 100) + (int) $fraction;
}
