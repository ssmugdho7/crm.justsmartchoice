<?php
defined('BASEPATH') or exit('No direct script access allowed');

function app_remember_login_lifetime()
{
    return defined('APP_REMEMBER_LOGIN_DAYS') ? max(1, min(30, (int) APP_REMEMBER_LOGIN_DAYS)) * 86400 : 7 * 86400;
}

function app_remember_login_data($cookie, $now = null, $checkExpiry = true)
{
    if (!is_string($cookie) || strlen($cookie) > 1024) {
        return null;
    }
    $data = json_decode($cookie, true);
    $now = $now ?? time();
    if (!is_array($data) || ($data['version'] ?? null) !== 2
        || !is_int($data['user_id'] ?? null) || $data['user_id'] < 1
        || !in_array($data['staff'] ?? null, [0, 1], true)
        || !is_int($data['expires'] ?? null)
        || ($checkExpiry && ($data['expires'] <= $now || $data['expires'] > $now + app_remember_login_lifetime()))
        || !is_string($data['key'] ?? null) || !preg_match('/\A[a-f0-9]{64}\z/', $data['key'])
        || !is_string($data['credential'] ?? null) || !preg_match('/\A[a-f0-9]{64}\z/', $data['credential'])) {
        return null;
    }
    return $data;
}

// The stored hash binds expiry, account type and password version to the random token.
function app_remember_login_key(array $data)
{
    return hash('sha256', implode('.', [$data['key'], $data['user_id'], $data['staff'], $data['expires'], $data['credential']]));
}

function app_remember_cookie_options($value, $expire)
{
    return [
        'name' => 'autologin', 'value' => $value, 'expire' => $expire,
        'domain' => (string) config_item('cookie_domain'),
        'path' => config_item('cookie_path') ?: '/',
        'prefix' => (string) config_item('cookie_prefix'),
        'secure' => parse_url(APP_BASE_URL, PHP_URL_SCHEME) === 'https',
        'httponly' => true, 'samesite' => 'Lax',
    ];
}

function app_set_remember_cookie($value, $expire)
{
    $cookie = app_remember_cookie_options($value, $expire);
    // CI 3.1.11's cookie helper ignores SameSite; PHP's options API supports it.
    return setcookie($cookie['prefix'] . $cookie['name'], $value, [
        'expires' => time() + $expire, 'path' => $cookie['path'], 'domain' => $cookie['domain'],
        'secure' => $cookie['secure'], 'httponly' => true, 'samesite' => 'Lax',
    ]);
}
