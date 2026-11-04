<?php
defined('BASEPATH') or exit('No direct script access allowed');

function sc_google_secret_encrypt($value)
{
    if ($value === '') return '';
    $CI = &get_instance();
    $CI->load->library('encryption');
    $encrypted = $CI->encryption->encrypt((string) $value);
    if (!is_string($encrypted) || $encrypted === '') {
        throw new RuntimeException('Unable to securely store Google credentials.');
    }
    return 'scg1:' . $encrypted;
}

function sc_google_secret_decrypt($value)
{
    $value = (string) $value;
    // Existing credentials remain readable; subsequent writes use encryption.
    if (strpos($value, 'scg1:') !== 0) return $value;
    $CI = &get_instance();
    $CI->load->library('encryption');
    $decrypted = $CI->encryption->decrypt(substr($value, 5));
    return is_string($decrypted) ? $decrypted : '';
}
