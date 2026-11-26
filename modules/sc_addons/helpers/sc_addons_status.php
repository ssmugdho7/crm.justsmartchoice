<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
 * Status logic for the Add-ons & Integrations page.
 * States: not_installed | disabled | not_configured | needs_attention | connected
 * Every non-connected state carries a human reason. Secret values are never returned.
 */

const SC_ADDONS_TEST_OPTION_PREFIX = 'sc_addons_test_';
// Admin opt-in that lets tests send an API key to a custom (non-provider) host.
const SC_ADDONS_ALLOW_CUSTOM_HOST_OPTION = 'sc_addons_allow_custom_ai_host';
const SC_ADDONS_KNOWN_AI_HOSTS = ['api.openai.com', 'api.anthropic.com', 'generativelanguage.googleapis.com', 'api.groq.com',
    'api.mistral.ai', 'openrouter.ai', 'api.together.xyz', 'api.deepseek.com'];
const SC_ADDONS_KNOWN_AI_HOST_SUFFIXES = ['.openai.azure.com'];

/**
 * Options that must be filled for the entry to count as connected.
 * $val reads a prospective value (used while validating a save); defaults to the stored option.
 */
function sc_addons_required_options(array $entry, ?callable $val = null)
{
    $req = $entry['required_for_connected'];
    if (is_callable($req)) {
        $val = $val ?: fn ($o) => (string) get_option($o);

        return array_values(array_unique((array) call_user_func($req, $val)));
    }

    return (array) $req;
}

/** Is the option's value "filled in"? Encrypted values are only checked for presence. */
function sc_addons_option_filled($option)
{
    return trim((string) get_option($option)) !== '';
}

function sc_addons_module_info($name)
{
    if (!$name) {
        return null;
    }
    $CI = &get_instance();

    return $CI->app_modules->get($name);
}

/** Is the integration switched on? (null active_option = always on once configured). */
function sc_addons_is_enabled(array $entry)
{
    $active = $entry['active_option'];
    if ($active === null) {
        return null;
    }
    foreach ((array) $active as $opt) {
        if ((string) get_option($opt) === (string) $entry['active_on']) {
            return true;
        }
    }

    return false;
}

function sc_addons_missing_required(array $entry)
{
    $missing = [];
    $labels  = [];
    foreach ($entry['fields'] as $f) {
        $labels[$f['option']] = $f['label'];
    }
    foreach (sc_addons_required_options($entry) as $opt) {
        if (!sc_addons_option_filled($opt)) {
            $missing[] = $labels[$opt] ?? $opt;
        }
    }
    if ($entry['required_any']) {
        $any = false;
        foreach ($entry['required_any'] as $opt) {
            $any = $any || sc_addons_option_filled($opt);
        }
        if (!$any) {
            $missing[] = $labels[$entry['required_any'][0]] ?? $entry['required_any'][0];
        }
    }

    return $missing;
}

function sc_addons_last_test($id)
{
    $raw = get_option(SC_ADDONS_TEST_OPTION_PREFIX . $id);
    if ($raw === '' || $raw === null) {
        return null;
    }
    $d = json_decode($raw, true);

    return is_array($d) && isset($d['ok'], $d['at']) ? $d : null;
}

function sc_addons_clear_test($id)
{
    if (option_exists(SC_ADDONS_TEST_OPTION_PREFIX . $id)) {
        update_option(SC_ADDONS_TEST_OPTION_PREFIX . $id, '', 0);
    }
}

function sc_addons_store_test($id, $ok, $message)
{
    update_option(SC_ADDONS_TEST_OPTION_PREFIX . $id, json_encode([
        'ok' => (bool) $ok, 'message' => mb_substr((string) $message, 0, 300), 'at' => time(),
    ]), 0);
}

/**
 * Compute status for one registry entry.
 * @return array{status:string, reason:string, issues:array, missing:array, enabled:?bool, module:?array, last_test:?array}
 */
function sc_addons_status(array $entry)
{
    $out = ['status' => 'connected', 'reason' => '', 'issues' => [], 'missing' => [], 'enabled' => null,
        'module' => null, 'last_test' => null];

    // 1. Owning module
    if ($entry['module']) {
        $mod = sc_addons_module_info($entry['module']);
        if (!$mod) {
            $out['status'] = 'not_installed';
            $out['reason'] = sprintf(sc_addons_l('sc_addons_reason_not_installed', 'The "%s" module is not on this system. Install it to use this integration.'), $entry['module']);

            return $out;
        }
        $out['module'] = ['name' => $entry['module'], 'active' => (int) $mod['activated'] === 1, 'installed' => $mod['installed_version'] !== false, 'version' => $mod['headers']['version'] ?? ''];
        if ((int) $mod['activated'] !== 1) {
            $out['status'] = $mod['installed_version'] === false ? 'not_installed' : 'disabled';
            $out['reason'] = $mod['installed_version'] === false
                ? sprintf(sc_addons_l('sc_addons_reason_not_installed_disk', 'The "%s" module is on disk but not installed yet.'), $entry['module'])
                : sprintf(sc_addons_l('sc_addons_reason_module_off', 'The "%s" module is deactivated.'), $entry['module']);

            return $out;
        }
        if (empty($mod['init_file']) || !is_file($mod['init_file'])) {
            $out['status'] = 'needs_attention';
            $out['reason'] = sc_addons_l('sc_addons_reason_files_missing', 'The module is active but its files are missing.');

            return $out;
        }
    }

    // 2. Entries whose state lives outside tbloptions (e.g. Telegram's own bot table) report it directly.
    if (!empty($entry['status_override']) && is_callable($entry['status_override'])) {
        try {
            $override = call_user_func($entry['status_override'], $entry);
        } catch (Throwable $e) {
            log_message('error', 'sc_addons status_override failed for ' . $entry['id'] . ': ' . get_class($e));
            $override = ['needs_attention', sc_addons_l('sc_addons_reason_status_error', 'Status could not be read.')];
        }
        if (is_array($override)) {
            $out['status'] = $override[0];
            $out['reason'] = $override[1];
            if ($override[0] === 'needs_attention') {
                $out['issues'] = [$override[1]];
            }

            return $out;
        }
        $out['last_test'] = sc_addons_last_test($entry['id']);
        if ($out['last_test'] && !$out['last_test']['ok']) {
            $out['status'] = 'needs_attention';
            $out['issues'] = [sprintf(sc_addons_l('sc_addons_reason_test_failed', 'Last test failed: %s'), $out['last_test']['message'])];
            $out['reason'] = $out['issues'][0];
        }

        return $out;
    }

    // 3. Credentials
    $out['missing'] = sc_addons_missing_required($entry);
    $out['enabled'] = sc_addons_is_enabled($entry);
    $anyFilled      = false;
    foreach ($entry['fields'] as $f) {
        if ($f['type'] !== 'checkbox' && $f['type'] !== 'select' && sc_addons_option_filled($f['option'])) {
            $anyFilled = true;
        }
    }

    if ($out['missing']) {
        $partial = $anyFilled && $out['enabled'];
        $out['status'] = $partial ? 'needs_attention' : 'not_configured';
        $out['reason'] = $partial
            ? sprintf(sc_addons_l('sc_addons_reason_enabled_missing', 'Turned on but missing: %s.'), implode(', ', $out['missing']))
            : sprintf(sc_addons_l('sc_addons_reason_missing', 'Not set up yet. Needs: %s.'), implode(', ', $out['missing']));

        return $out;
    }

    if ($out['enabled'] === false) {
        $out['status'] = 'disabled';
        $out['reason'] = sc_addons_l('sc_addons_reason_disabled', 'Credentials are saved but the integration is switched off.');

        return $out;
    }

    // 4. Problems: custom checks + last test
    if (is_callable($entry['checks'])) {
        try {
            $out['issues'] = array_values(array_filter((array) call_user_func($entry['checks'], $entry)));
        } catch (Throwable $e) {
            log_message('error', 'sc_addons check failed for ' . $entry['id'] . ': ' . get_class($e));
        }
    }
    $out['last_test'] = sc_addons_last_test($entry['id']);
    if ($out['last_test'] && !$out['last_test']['ok']) {
        array_unshift($out['issues'], sprintf(sc_addons_l('sc_addons_reason_test_failed', 'Last test failed: %s'), $out['last_test']['message']));
    }
    if ($out['issues']) {
        $out['status'] = 'needs_attention';
        $out['reason'] = implode(' ', $out['issues']);
    }

    return $out;
}

/**
 * Module header description without marketing noise ("Quality ★★★★★.", star runs, "Last Modified …"),
 * whitespace collapsed. Returns [full, short] where short is trimmed to $max characters with an ellipsis.
 */
function sc_addons_clean_description($text, $max = 160)
{
    $t = (string) $text;
    $t = preg_replace('/\bQuality\s*:?\s*[\x{2605}\x{2606}\x{2B50}]+\s*\.?/iu', ' ', $t);
    $t = preg_replace('/[\x{2605}\x{2606}\x{2B50}]+/u', ' ', $t);
    $t = preg_replace('/\bLast Modified\s+\d{4}-\d{2}-\d{2}\.?/i', ' ', $t);
    $t = trim(preg_replace('/\s+/u', ' ', (string) $t));
    $t = trim(preg_replace('/^[.\s]+/u', '', $t));
    $full  = mb_substr($t, 0, 2000);
    $short = $full;
    if (mb_strlen($full) > $max) {
        $cut   = mb_substr($full, 0, $max - 1);
        $space = mb_strrpos($cut, ' ');
        if ($space !== false && $space > $max * 0.6) {
            $cut = mb_substr($cut, 0, $space);
        }
        $short = rtrim($cut, " ,.;:-") . "\u{2026}";
    }

    return ['full' => $full, 'short' => $short];
}

/** Safe JSON-able card for the page. */
function sc_addons_card(array $entry)
{
    $s = sc_addons_status($entry);

    return [
        'id' => $entry['id'], 'name' => $entry['name'], 'category' => $entry['category'], 'icon' => $entry['icon'],
        'description' => $entry['description'], 'status' => $s['status'], 'reason' => $s['reason'],
        'issues' => $s['issues'], 'enabled' => $s['enabled'], 'module' => $s['module'], 'module_name' => $entry['module'],
        'module_on_disk' => $s['module'] !== null,
        'toggleable' => $entry['active_option'] !== null, 'configurable' => !empty($entry['fields']),
        // Every entry with saved credentials can be disconnected: toggle off, or erase credentials when there is no switch.
        'disconnect_mode' => $entry['active_option'] !== null ? 'toggle' : 'erase',
        'erase_labels' => $entry['active_option'] === null ? sc_addons_erase_labels($entry) : [],
        'disconnect_block' => sc_addons_disconnect_block($entry),
        'settings_url' => $entry['settings_url'], 'testable' => true,
        'last_test' => $s['last_test'] ? ['ok' => $s['last_test']['ok'], 'message' => $s['last_test']['message'], 'at' => date('Y-m-d H:i', $s['last_test']['at'])] : null,
    ];
}

/** Credential options (non-checkbox, non-select fields) that "erase" disconnect clears. */
function sc_addons_erase_options(array $entry)
{
    // Entries may list exactly which options Disconnect erases (e.g. mail keeps the From address).
    if (isset($entry['erase_options']) && is_array($entry['erase_options'])) {
        return $entry['erase_options'];
    }
    $out = [];
    foreach ($entry['fields'] as $f) {
        if ($f['type'] !== 'checkbox' && $f['type'] !== 'select') {
            $out[] = $f['option'];
        }
    }

    return $out;
}

/** Primary Telegram bot row used by the telegram_chat module for CRM alerts, or null. */
function sc_addons_telegram_account()
{
    $CI = &get_instance();
    if (!$CI->db->table_exists(db_prefix() . 'telegram_info')) {
        return null;
    }

    return $CI->db->order_by('id', 'ASC')->limit(1)->get(db_prefix() . 'telegram_info')->row();
}

/** Reason Disconnect is refused right now (e.g. the mail provider every CRM email is sent with), or ''. */
function sc_addons_disconnect_block(array $entry)
{
    $protocol = (string) get_option('email_protocol');
    $map      = ['smtp' => ['smtp', 'mail', ''], 'microsoft_mail' => ['microsoft'], 'google_mail' => ['google']];
    if (isset($map[$entry['id']]) && in_array($protocol, $map[$entry['id']], true)) {
        return sprintf(sc_addons_l('sc_addons_disconnect_blocked_mail', '%s sends every CRM email right now. Switch the email provider in Setup > Settings > Email first, then disconnect it here.'), $entry['name']);
    }

    return '';
}

function sc_addons_erase_labels(array $entry)
{
    $labels = [];
    $opts   = sc_addons_erase_options($entry);
    foreach ($entry['fields'] as $f) {
        if (in_array($f['option'], $opts, true) && sc_addons_option_filled($f['option'])) {
            $labels[] = $f['label'];
        }
    }

    return $labels;
}

/** Run test (live callable or structural) and store the result. */
function sc_addons_run_test(array $entry)
{
    if ($entry['module']) {
        $mod = sc_addons_module_info($entry['module']);
        if (!$mod || (int) $mod['activated'] !== 1) {
            $r = [false, sc_addons_l('sc_addons_test_module_off', 'The owning module is not active.')];
            sc_addons_store_test($entry['id'], $r[0], $r[1]);

            return $r;
        }
    }
    $missing = sc_addons_missing_required($entry);
    if ($missing) {
        $r = [false, sprintf(sc_addons_l('sc_addons_reason_missing', 'Not set up yet. Needs: %s.'), implode(', ', $missing))];
    } elseif (is_callable($entry['test'])) {
        try {
            $r = call_user_func($entry['test'], $entry);
        } catch (Throwable $e) {
            log_message('error', 'sc_addons test failed for ' . $entry['id'] . ': ' . get_class($e));
            $r = [false, sc_addons_l('sc_addons_test_error', 'The test could not run (unexpected error).')];
        }
    } else {
        $r = [true, sc_addons_l('sc_addons_test_structural', 'Settings look complete (no live check available for this provider).')];
    }
    sc_addons_store_test($entry['id'], (bool) $r[0], (string) $r[1]);

    return [(bool) $r[0], (string) $r[1]];
}

// ---------------- Secrets & HTTP helpers (used by registry test callables) ----------------

/** Decrypt a stored secret. $ci = CI encryption; 'scg' prefix handled too. Returns '' when unreadable. */
function sc_addons_secret($option, $encrypted = true)
{
    $value = trim((string) get_option($option));
    if ($value === '') {
        return '';
    }
    if (strpos($value, 'scg1:') === 0 && function_exists('sc_google_secret_decrypt')) {
        return trim(sc_google_secret_decrypt($value));
    }
    if (!$encrypted) {
        return $value;
    }
    $CI  = &get_instance();
    $dec = $CI->encryption->decrypt($value);

    return is_string($dec) ? trim($dec) : '';
}

/** Is $ip a public unicast address (not private, loopback, link-local, CGNAT, ULA, metadata, unspecified)? */
function sc_addons_ip_is_public($ip)
{
    if (!filter_var($ip, FILTER_VALIDATE_IP)) {
        return false;
    }
    if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
        return false;
    }
    $bin = inet_pton($ip);
    if ($bin === false) {
        return false;
    }
    if (strlen($bin) === 16) {
        // IPv4-mapped / compatible IPv6 (::ffff:a.b.c.d) -> check the embedded IPv4.
        if (substr($bin, 0, 12) === str_repeat("\0", 10) . "\xff\xff" || substr($bin, 0, 12) === str_repeat("\0", 12)) {
            return sc_addons_ip_is_public(inet_ntop(substr($bin, 12)));
        }
        $blocked6 = ['::/128', '::1/128', 'fc00::/7', 'fe80::/10', 'fec0::/10', 'ff00::/8', '64:ff9b::/96', '64:ff9b:1::/48', '2001:db8::/32', '100::/64',
            '2002::/16', '2001::/32', '::ffff:0:0/96'];
        foreach ($blocked6 as $cidr) {
            if (sc_addons_ip_in_cidr($bin, $cidr)) {
                return false;
            }
        }

        return true;
    }
    $blocked4 = ['0.0.0.0/8', '10.0.0.0/8', '100.64.0.0/10', '127.0.0.0/8', '169.254.0.0/16', '172.16.0.0/12', '192.0.0.0/24',
        '192.0.2.0/24', '192.168.0.0/16', '198.18.0.0/15', '198.51.100.0/24', '203.0.113.0/24', '224.0.0.0/4', '240.0.0.0/4', '168.63.129.16/32'];
    foreach ($blocked4 as $cidr) {
        if (sc_addons_ip_in_cidr($bin, $cidr)) {
            return false;
        }
    }

    return true;
}

function sc_addons_ip_in_cidr($bin, $cidr)
{
    [$net, $bits] = explode('/', $cidr);
    $netBin       = inet_pton($net);
    if ($netBin === false || strlen($netBin) !== strlen($bin)) {
        return false;
    }
    $bits  = (int) $bits;
    $bytes = intdiv($bits, 8);
    if (substr($bin, 0, $bytes) !== substr($netBin, 0, $bytes)) {
        return false;
    }
    $rem = $bits % 8;
    if ($rem === 0) {
        return true;
    }
    $mask = (0xFF << (8 - $rem)) & 0xFF;

    return (ord($bin[$bytes]) & $mask) === (ord($netBin[$bytes]) & $mask);
}

function sc_addons_is_known_ai_host($host)
{
    $host = strtolower(rtrim((string) $host, '.'));
    if (in_array($host, SC_ADDONS_KNOWN_AI_HOSTS, true)) {
        return true;
    }
    foreach (SC_ADDONS_KNOWN_AI_HOST_SUFFIXES as $suffix) {
        if (strlen($host) > strlen($suffix) && substr($host, -strlen($suffix)) === $suffix) {
            return true;
        }
    }

    return false;
}

/**
 * Validate an admin-supplied URL before credentials are sent to it.
 * @return array{ok:bool, host?:string, port?:int, ip?:string, code?:string}
 *   code: bad_url | not_https | unknown_host | private_ip | dns
 */
function sc_addons_check_outbound_url($url, $allowCustom = false)
{
    $parts = parse_url((string) $url);
    if (!is_array($parts) || empty($parts['host']) || isset($parts['user']) || isset($parts['pass'])) {
        return ['ok' => false, 'code' => 'bad_url'];
    }
    if (strtolower($parts['scheme'] ?? '') !== 'https') {
        return ['ok' => false, 'code' => 'not_https'];
    }
    $host = strtolower(trim($parts['host'], '[]'));
    $port = isset($parts['port']) ? (int) $parts['port'] : 443;
    if ($port < 1 || $port > 65535) {
        return ['ok' => false, 'code' => 'bad_url'];
    }
    if (filter_var($host, FILTER_VALIDATE_IP)) {
        $ips = [$host];
    } else {
        if (!preg_match('/^[a-z0-9.-]+$/', $host)) {
            return ['ok' => false, 'code' => 'bad_url'];
        }
        $ips = [];
        foreach (@gethostbynamel($host) ?: [] as $ip) {
            $ips[] = $ip;
        }
        foreach (@dns_get_record($host, DNS_AAAA) ?: [] as $rec) {
            if (!empty($rec['ipv6'])) {
                $ips[] = $rec['ipv6'];
            }
        }
        if (!$ips) {
            return ['ok' => false, 'code' => 'dns'];
        }
    }
    // Every resolved address must be public, otherwise a DNS answer could still steer us inside.
    foreach ($ips as $ip) {
        if (!sc_addons_ip_is_public($ip)) {
            return ['ok' => false, 'code' => 'private_ip'];
        }
    }
    if (!sc_addons_is_known_ai_host($host) && !$allowCustom) {
        return ['ok' => false, 'code' => 'unknown_host'];
    }
    // Prefer IPv4 for pinning (CURLOPT_RESOLVE).
    $pin = $ips[0];
    foreach ($ips as $ip) {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $pin = $ip;
            break;
        }
    }

    return ['ok' => true, 'host' => $host, 'port' => $port, 'ip' => $pin];
}

function sc_addons_outbound_message($code)
{
    $map = [
        'bad_url'      => ['sc_addons_url_bad', 'The base URL is not a valid URL.'],
        'not_https'    => ['sc_addons_url_not_https', 'The base URL must start with https://.'],
        'unknown_host' => ['sc_addons_url_unknown_host', 'This host is not a known AI provider. Tick "Allow custom host" to test it anyway.'],
        'private_ip'   => ['sc_addons_url_private', 'The base URL points to a private or internal address, which is blocked.'],
        'dns'          => ['sc_addons_url_dns', 'The base URL host could not be resolved.'],
    ];
    $m = $map[$code] ?? $map['bad_url'];

    return sc_addons_l($m[0], $m[1]);
}

/**
 * HTTPS-only request. $opts['guard'] = true for admin-supplied URLs: the host must resolve to a public IP
 * (pinned with CURLOPT_RESOLVE against DNS rebinding) and be a known provider unless $opts['allow_custom'].
 */
function sc_addons_http($method, $url, array $headers = [], $body = null, $userpwd = null, array $opts = [])
{
    $resolve = null;
    if (!empty($opts['guard'])) {
        $check = sc_addons_check_outbound_url($url, !empty($opts['allow_custom']));
        if (!$check['ok']) {
            return ['ok' => false, 'code' => 0, 'error' => 'blocked', 'blocked' => $check['code'], 'json' => null];
        }
        $ip      = strpos($check['ip'], ':') !== false ? '[' . $check['ip'] . ']' : $check['ip'];
        $resolve = [$check['host'] . ':' . $check['port'] . ':' . $ip];
    }
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 10, CURLOPT_CONNECTTIMEOUT => 6,
        CURLOPT_CUSTOMREQUEST  => $method, CURLOPT_HTTPHEADER => $headers, CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_MAXREDIRS      => 0, CURLOPT_PROTOCOLS => CURLPROTO_HTTPS, CURLOPT_REDIR_PROTOCOLS => CURLPROTO_HTTPS,
        CURLOPT_SSL_VERIFYPEER => true, CURLOPT_SSL_VERIFYHOST => 2,
    ]);
    if ($resolve) {
        curl_setopt($ch, CURLOPT_RESOLVE, $resolve);
    }
    if ($body !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    }
    if ($userpwd) {
        curl_setopt($ch, CURLOPT_USERPWD, $userpwd);
    }
    $resp  = curl_exec($ch);
    $code  = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_errno($ch) ? 'network' : '';
    curl_close($ch);
    $json = is_string($resp) ? json_decode($resp, true) : null;

    return ['ok' => $code >= 200 && $code < 300, 'code' => $code, 'error' => $error, 'json' => is_array($json) ? $json : null];
}

function sc_addons_http_verdict(array $r, $provider)
{
    if (($r['error'] ?? '') === 'blocked') {
        return [false, sc_addons_outbound_message($r['blocked'] ?? 'bad_url')];
    }
    if ($r['ok']) {
        return [true, sprintf(sc_addons_l('sc_addons_test_ok', '%s accepted the saved credentials.'), $provider)];
    }
    if ($r['error'] !== '' || $r['code'] === 0) {
        return [false, sprintf(sc_addons_l('sc_addons_test_unreachable', 'Could not reach %s. Check the server\'s internet access.'), $provider)];
    }
    if (in_array($r['code'], [401, 403], true)) {
        return [false, sprintf(sc_addons_l('sc_addons_test_rejected', '%s rejected the credentials (HTTP %d). Check the keys and try again.'), $provider, $r['code'])];
    }

    return [false, sprintf(sc_addons_l('sc_addons_test_http', '%s answered with HTTP %d.'), $provider, $r['code'])];
}
