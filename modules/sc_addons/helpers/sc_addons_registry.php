<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
 * Declarative registry of every integration shown on the Add-ons & Integrations page.
 *
 * Entry keys:
 *   id, name, category, icon, description, module (owning Perfex module or null = core),
 *   fields [{option, label, type text|password|textarea|secret_textarea|select|checkbox, required,
 *            encrypted false|true (CI encryption)|'scg' (Google helper, "scg1:" prefix), help, options}],
 *   required_for_connected [option names], required_any [option names, at least one],
 *   active_option string|array|null (array = enabled when any is on), active_on / active_off values,
 *   settings_url, test callable ($entry) => [bool ok, string message], checks callable ($entry) => [issues],
 *   exclusive_group (connecting one entry turns the others in the group off, e.g. SMS gateways),
 *   required_for_connected may also be a callable (fn ($val) => [options]) where $val($option) reads the
 *     prospective value, for entries whose required set depends on another field (QuickBooks test mode, Plaid env),
 *   enable_via_fields (true = the active_option checkboxes are part of the form; Connect never forces them on),
 *   guide (setup guide: steps lang prefix, docs URL, redirect/callback URLs to copy; see sc_addons_guides.php),
 *   field keys: recommended (shows a "recommended" hint), validate 'https_url'.
 *
 * Option names below were verified read-only against the production tbloptions names and the
 * owning modules' get_option()/update_option() calls. Never put secret values in this file.
 */

const SC_ADDONS_CATEGORIES = ['Payments', 'Communication', 'AI', 'Calendar & Meetings', 'Marketing & Analytics', 'Storage & Files', 'Social login', 'Security', 'Other'];

/** Raw (unescaped, no sprintf) language lookup; callers escape on output. */
function sc_addons_t($key)
{
    $line = get_instance()->lang->line($key, false);

    return is_string($line) ? $line : '';
}

function sc_addons_l($key, $fallback)
{
    $line = sc_addons_t($key);

    return $line === '' ? $fallback : $line;
}

function sc_addons_field($option, $labelKey, $type = 'text', array $extra = [])
{
    return array_merge([
        'option'    => $option,
        'label'     => sc_addons_l('sc_addons_f_' . $labelKey, ucwords(str_replace('_', ' ', $labelKey))),
        'type'      => $type,
        'required'  => false,
        'encrypted' => false,
        'help'      => '',
    ], $extra);
}

/** All integrations: curated entries + auto-discovered payment and SMS gateways. Cached per request. */
function sc_addons_registry($refresh = false)
{
    static $registry = null;
    if ($registry !== null && !$refresh) {
        return $registry;
    }
    $registry = [];
    foreach (array_merge(sc_addons_discover_payment_gateways(), sc_addons_discover_sms_gateways(), sc_addons_curated()) as $entry) {
        $entry = array_merge([
            'module' => null, 'fields' => [], 'required_for_connected' => [], 'required_any' => [],
            'active_option' => null, 'active_on' => '1', 'active_off' => '0', 'settings_url' => '',
            'test' => null, 'checks' => null, 'exclusive_group' => null, 'icon' => 'fa-solid fa-plug',
            'description' => '', 'source' => 'curated', 'enable_via_fields' => false, 'guide' => null,
            'oauth_start_url' => '', 'oauth_provider' => '', 'oauth_popup' => false,
        ], $entry);
        if ($entry['guide'] === null) {
            $entry['guide'] = sc_addons_guide_def($entry);
        }
        if (!preg_match('/^[A-Za-z0-9_-]+$/', $entry['id']) || isset($registry[$entry['id']])) {
            continue;
        }
        $registry[$entry['id']] = $entry;
    }

    return $registry;
}

function sc_addons_get($id)
{
    $all = sc_addons_registry();

    return $all[$id] ?? null;
}

/** Map a class to the module folder it was loaded from (null = core). */
function sc_addons_class_module($object)
{
    try {
        $file = (new ReflectionClass($object))->getFileName();
    } catch (Throwable $e) {
        return null;
    }
    $file = str_replace('\\', '/', (string) $file);
    $base = str_replace('\\', '/', APP_MODULES_PATH);
    if ($file !== '' && strpos($file, $base) === 0) {
        $rest = substr($file, strlen($base));

        return strtolower(strtok($rest, '/'));
    }

    return null;
}

function sc_addons_discover_payment_gateways()
{
    $CI = &get_instance();
    $CI->load->model('payment_modes_model');
    $entries = [];
    try {
        $gateways = $CI->payment_modes_model->get_payment_gateways(true);
    } catch (Throwable $e) {
        log_message('error', 'sc_addons: payment gateway discovery failed: ' . get_class($e));

        return [];
    }
    // Gateways declare no validation rules of their own, so only the primary credentials are required.
    // Webhook signature keys are optional (shown as "recommended").
    $credential = '/(^|_)(secret|key|token|client_id|login_id|application_id|merchant_id|merchant_code|location_id|username|password|signature|salt)($|_)/i';
    $skip       = ['active', 'label', 'fee_fixed', 'fee_percent', 'default_selected', 'currencies', 'initialized'];
    foreach ($gateways as $gateway) {
        if (empty($gateway['instance']) || !($gateway['instance'] instanceof App_gateway)) {
            continue;
        }
        $gw       = $gateway['instance'];
        $gid      = $gw->getId();
        $fields   = [];
        $required = [];
        $sandbox  = [];
        foreach ($gw->getSettings(false) as $setting) {
            $name = $setting['name'];
            if ($name === 'active' || $name === 'initialized') {
                continue;
            }
            $option    = 'paymentmethod_' . $gid . '_' . $name;
            $encrypted = !empty($setting['encrypted']);
            $stype     = $setting['type'] ?? 'input';
            $type      = $stype === 'yes_no' ? 'checkbox' : ($stype === 'textarea' ? 'textarea' : 'text');
            if ($encrypted || ($setting['input_type'] ?? '') === 'password') {
                $type = 'password';
            }
            $label = sc_addons_t($setting['label'] ?? $name);
            if ($label === '') {
                $label = ucwords(str_replace('_', ' ', $name));
            }
            $isWebhook  = (bool) preg_match('/webhook/i', $name);
            $isRequired = !$isWebhook && !in_array($name, $skip, true) && $type !== 'checkbox' && $type !== 'textarea'
                && preg_match($credential, $name);
            if ($isRequired) {
                $required[] = $option;
            }
            if ($type === 'checkbox' && preg_match('/(test_mode|sandbox)/i', $name)) {
                $sandbox[] = $option;
            }
            $fields[] = [
                'option' => $option, 'label' => strip_tags($label), 'type' => $type, 'required' => (bool) $isRequired,
                'encrypted' => $encrypted, 'recommended' => $isWebhook,
                'help' => $isWebhook ? sc_addons_l('sc_addons_h_webhook_recommended', 'Recommended: lets the CRM verify payment notifications from the provider.') : '',
            ];
        }
        $entries[] = [
            'id'                     => 'gw_' . $gid,
            'name'                   => $gw->getName() ?: $gid,
            'category'               => 'Payments',
            'icon'                   => sc_addons_gateway_icon($gid),
            'description'            => sc_addons_l('sc_addons_d_payment_gateway', 'Accept online invoice payments.'),
            'module'                 => sc_addons_class_module($gw),
            'fields'                 => $fields,
            'required_for_connected' => $required,
            'active_option'          => 'paymentmethod_' . $gid . '_active',
            'settings_url'           => admin_url('settings?group=payment_gateways'),
            'test'                   => sc_addons_gateway_test_callable($gid),
            'checks'                 => function ($entry) use ($sandbox, $gid) {
                $issues = [];
                foreach ($sandbox as $opt) {
                    if (get_option($opt) === '1') {
                        $issues[] = sc_addons_l('sc_addons_issue_test_mode', 'Test / sandbox mode is on, so real payments are not collected.');
                    }
                }
                if ($gid === 'stripe' && trim((string) get_option('stripe_webhook_id')) === '') {
                    $issues[] = sc_addons_l('sc_addons_issue_stripe_webhook', 'Stripe webhook is not created yet (Settings > Payment Gateways > Stripe).');
                }

                return $issues;
            },
            'source'                 => 'gateway',
        ];
    }

    return $entries;
}

function sc_addons_gateway_icon($gid)
{
    $map = ['stripe' => 'fa-brands fa-stripe', 'paypal' => 'fa-brands fa-paypal', 'paypal_checkout' => 'fa-brands fa-paypal',
        'smart_square' => 'fa-solid fa-square', 'two_checkout' => 'fa-solid fa-credit-card'];

    return $map[$gid] ?? 'fa-solid fa-credit-card';
}

function sc_addons_gateway_test_callable($gid)
{
    if ($gid === 'stripe') {
        return function () {
            $key = sc_addons_secret('paymentmethod_stripe_api_secret_key', true);
            if ($key === '') {
                return [false, sc_addons_l('sc_addons_test_missing', 'Required credentials are missing.')];
            }
            $r = sc_addons_http('GET', 'https://api.stripe.com/v1/balance', ['Authorization: Bearer ' . $key]);

            return sc_addons_http_verdict($r, 'Stripe');
        };
    }
    if ($gid === 'paypal_checkout') {
        return function () {
            $id     = trim((string) get_option('paymentmethod_paypal_checkout_client_id'));
            $secret = sc_addons_secret('paymentmethod_paypal_checkout_secret', true);
            if ($id === '' || $secret === '') {
                return [false, sc_addons_l('sc_addons_test_missing', 'Required credentials are missing.')];
            }
            $host = get_option('paymentmethod_paypal_checkout_test_mode_enabled') === '1' ? 'api-m.sandbox.paypal.com' : 'api-m.paypal.com';
            $r    = sc_addons_http('POST', 'https://' . $host . '/v1/oauth2/token', ['Content-Type: application/x-www-form-urlencoded'], 'grant_type=client_credentials', $id . ':' . $secret);

            return sc_addons_http_verdict($r, 'PayPal');
        };
    }
    if ($gid === 'smart_square') {
        return function () {
            $token = sc_addons_secret('paymentmethod_smart_square_access_token', true);
            if ($token === '') {
                return [false, sc_addons_l('sc_addons_test_missing', 'Required credentials are missing.')];
            }
            $host = get_option('paymentmethod_smart_square_sandbox') === '1' ? 'connect.squareupsandbox.com' : 'connect.squareup.com';
            $r    = sc_addons_http('GET', 'https://' . $host . '/v2/locations', ['Authorization: Bearer ' . $token, 'Square-Version: 2024-01-18']);
            if ($r['ok']) {
                $loc = trim((string) get_option('paymentmethod_smart_square_location_id'));
                $ids = array_column((array) ($r['json']['locations'] ?? []), 'id');
                if ($loc !== '' && $ids && !in_array($loc, $ids, true)) {
                    return [false, sc_addons_l('sc_addons_test_square_location', 'Square token works, but the saved Location ID is not on this account.')];
                }
            }

            return sc_addons_http_verdict($r, 'Square');
        };
    }

    return null;
}

/** Core SMS gateways (Setup > Settings > SMS); only one may be active at a time. */
function sc_addons_discover_sms_gateways()
{
    $CI = &get_instance();
    if (!isset($CI->app_sms)) {
        return [];
    }
    $entries = [];
    foreach ((array) $CI->app_sms->get_gateways() as $gateway) {
        $sid    = $gateway['id'];
        $fields = [];
        foreach ((array) ($gateway['options'] ?? []) as $opt) {
            $option = $CI->app_sms->option_name($sid, $opt['name']);
            $field  = [
                'option' => $option, 'label' => strip_tags((string) ($opt['label'] ?? $opt['name'])),
                'type' => preg_match('/(token|key|secret)/i', $opt['name']) ? 'password' : 'text',
                'required' => !in_array($opt['name'], ['sender_id', 'api_type'], true) && !($sid === 'twilio' && $opt['name'] === 'phone_number'),
                'encrypted' => false, 'help' => (string) ($opt['info'] ?? ''),
            ];
            if (($opt['field_type'] ?? '') === 'radio' && !empty($opt['options'])) {
                $field['type']    = 'select';
                $field['options'] = array_map(fn ($o) => ['value' => (string) $o['value'], 'label' => (string) $o['label']], $opt['options']);
            }
            $field['help'] = strip_tags($field['help']);
            $fields[]      = $field;
        }
        $entries[] = [
            'id'                     => 'sms_' . $sid,
            'name'                   => sprintf('%s SMS', $gateway['name'] ?? $sid),
            'category'               => 'Communication',
            'icon'                   => 'fa-regular fa-message',
            'description'            => sc_addons_l('sc_addons_d_sms', 'Send CRM SMS notifications. Only one SMS gateway can be active.'),
            'module'                 => null,
            'fields'                 => $fields,
            'required_for_connected' => array_column(array_filter($fields, fn ($f) => $f['required']), 'option'),
            // Twilio sends from either a phone number or an alphanumeric sender ID.
            'required_any'           => $sid === 'twilio' ? [$CI->app_sms->option_name($sid, 'phone_number'), $CI->app_sms->option_name($sid, 'sender_id')] : [],
            'active_option'          => $CI->app_sms->option_name($sid, 'active'),
            'settings_url'           => admin_url('settings?group=sms'),
            'exclusive_group'        => 'sms',
            'test'                   => $sid === 'twilio' ? fn () => sc_addons_twilio_test('sms_twilio_account_sid', 'sms_twilio_auth_token') : null,
            'source'                 => 'sms',
        ];
    }

    return $entries;
}

function sc_addons_twilio_test($sidOption, $tokenOption)
{
    $sid   = trim((string) get_option($sidOption));
    $token = trim((string) get_option($tokenOption));
    if ($sid === '' || $token === '') {
        return [false, sc_addons_l('sc_addons_test_missing', 'Required credentials are missing.')];
    }
    $r = sc_addons_http('GET', 'https://api.twilio.com/2010-04-01/Accounts/' . rawurlencode($sid) . '.json', [], null, $sid . ':' . $token);
    if ($r['ok'] && ($r['json']['status'] ?? '') !== 'active') {
        return [false, sprintf(sc_addons_l('sc_addons_test_twilio_status', 'Twilio account status is "%s".'), (string) ($r['json']['status'] ?? '?'))];
    }

    return sc_addons_http_verdict($r, 'Twilio');
}

function sc_addons_openai_compatible_test($baseUrl, $key, $label = 'OpenAI')
{
    if ($key === '') {
        return [false, sc_addons_l('sc_addons_test_missing', 'Required credentials are missing.')];
    }
    $opts = [];
    if ($baseUrl !== '') {
        // Admin-supplied host: never send the key to private/loopback/metadata addresses or unknown hosts.
        $opts = ['guard' => true, 'allow_custom' => get_option(SC_ADDONS_ALLOW_CUSTOM_HOST_OPTION) === '1'];
    }
    $r = sc_addons_http('GET', rtrim($baseUrl ?: 'https://api.openai.com/v1', '/') . '/models', ['Authorization: Bearer ' . $key], null, null, $opts);

    return sc_addons_http_verdict($r, $label);
}

function sc_addons_ai_provider_test($provider, $key, $baseUrl)
{
    if ($key === '') {
        return [false, sc_addons_l('sc_addons_test_missing', 'Required credentials are missing.')];
    }
    if ($provider === 'anthropic') {
        $r = sc_addons_http('GET', 'https://api.anthropic.com/v1/models', ['x-api-key: ' . $key, 'anthropic-version: 2023-06-01']);

        return sc_addons_http_verdict($r, 'Anthropic');
    }
    if ($provider === 'gemini') {
        $r = sc_addons_http('GET', 'https://generativelanguage.googleapis.com/v1beta/models?key=' . rawurlencode($key));

        return sc_addons_http_verdict($r, 'Gemini');
    }
    if ($provider === 'azure_openai') {
        return [true, sc_addons_l('sc_addons_test_structural', 'Settings look complete (no live check available for this provider).')];
    }

    return sc_addons_openai_compatible_test($provider === 'custom' ? $baseUrl : '', $key, $provider === 'custom' ? sc_addons_l('sc_addons_ai_provider', 'AI provider') : 'OpenAI');
}

function sc_addons_curated()
{
    $req  = ['required' => true];
    $pw   = ['required' => true];
    $allowCustom = sc_addons_field(SC_ADDONS_ALLOW_CUSTOM_HOST_OPTION, 'allow_custom_host', 'checkbox', [
        'help' => sc_addons_l('sc_addons_h_allow_custom_host', 'Warning: the API key is sent to this host when testing. Only tick this if you trust the custom base URL.'),
    ]);

    $entries = [];

    // ---------------- Communication ----------------
    $entries[] = [
        'id' => 'smtp', 'name' => 'Email (SMTP)', 'category' => 'Communication', 'icon' => 'fa-regular fa-envelope',
        'description' => sc_addons_l('sc_addons_d_smtp', 'Outgoing email server used for every CRM email.'),
        'fields' => [
            sc_addons_field('smtp_host', 'smtp_host', 'text', $req),
            sc_addons_field('smtp_port', 'smtp_port', 'text', $req),
            sc_addons_field('smtp_encryption', 'smtp_encryption', 'select', ['options' => [['value' => '', 'label' => sc_addons_l('sc_addons_opt_none', 'None')], ['value' => 'ssl', 'label' => 'SSL'], ['value' => 'tls', 'label' => 'TLS']]]),
            sc_addons_field('smtp_email', 'smtp_email', 'text', $req),
            sc_addons_field('smtp_username', 'smtp_username'),
            sc_addons_field('smtp_password', 'smtp_password', 'password', ['encrypted' => true]),
        ],
        'required_for_connected' => ['smtp_host', 'smtp_port', 'smtp_email'],
        'erase_options' => ['smtp_host', 'smtp_username', 'smtp_password'],
        'settings_url' => admin_url('settings?group=email'),
        'checks' => function () {
            $protocol = (string) get_option('email_protocol');
            if (in_array($protocol, ['microsoft', 'google'], true)) {
                return [sprintf(sc_addons_l('sc_addons_issue_mail_protocol', 'Email is currently sent with the "%s" provider, not SMTP.'), $protocol)];
            }

            return [];
        },
        'test' => function () {
            $host = trim((string) get_option('smtp_host'));
            $port = (int) get_option('smtp_port');
            if ($host === '' || $port <= 0) {
                return [false, sc_addons_l('sc_addons_test_missing', 'Required credentials are missing.')];
            }
            if (!preg_match('/^[a-z0-9.-]+$/i', $host) || $port > 65535) {
                return [false, sc_addons_l('sc_addons_test_smtp_host', 'The SMTP host must be a plain host name such as mail.example.com.')];
            }
            $ips = filter_var($host, FILTER_VALIDATE_IP) ? [$host] : (@gethostbynamel($host) ?: []);
            foreach ($ips as $ip) {
                if (!sc_addons_ip_is_public($ip)) {
                    return [false, sc_addons_outbound_message('private_ip')];
                }
            }
            $target = (get_option('smtp_encryption') === 'ssl' ? 'ssl://' : '') . $host;
            $errno  = 0;
            $errstr = '';
            $fp     = @fsockopen($target, $port, $errno, $errstr, 8);
            if (!$fp) {
                return [false, sprintf(sc_addons_l('sc_addons_test_smtp_fail', 'Could not reach %s:%d (%s).'), $host, $port, 'connection failed')];
            }
            stream_set_timeout($fp, 8);
            $banner = (string) fgets($fp, 512);
            fclose($fp);
            if (strpos($banner, '220') !== 0) {
                return [false, sc_addons_l('sc_addons_test_smtp_banner', 'Server answered, but not as an SMTP server.')];
            }

            return [true, sc_addons_l('sc_addons_test_smtp_ok', 'SMTP server is reachable (login is checked when the next email is sent).')];
        },
    ];

    $entries[] = [
        'id' => 'microsoft_mail', 'name' => 'Microsoft 365 / Outlook mail', 'category' => 'Communication', 'icon' => 'fa-brands fa-microsoft',
        'description' => sc_addons_l('sc_addons_d_microsoft_mail', 'Send CRM email through Microsoft 365 (OAuth).'),
        'fields' => [
            sc_addons_field('microsoft_mail_client_id', 'client_id', 'text', $req),
            sc_addons_field('microsoft_mail_client_secret', 'client_secret', 'password', ['required' => true, 'encrypted' => true]),
            sc_addons_field('microsoft_mail_azure_tenant_id', 'tenant_id'),
        ],
        'required_for_connected' => ['microsoft_mail_client_id', 'microsoft_mail_client_secret'],
        // application/controllers/admin/Smtp_oauth_microsoft.php::token() starts the sign-in.
        'oauth_start_url' => admin_url('smtp_oauth_microsoft/token'), 'oauth_provider' => 'Microsoft',
        'settings_url' => admin_url('settings?group=email'),
        'checks' => fn () => trim((string) get_option('microsoft_mail_refresh_token')) === ''
            ? [sc_addons_l('sc_addons_issue_oauth', 'Not authorized yet: open the settings page and sign in to grant access.')] : [],
    ];

    $entries[] = [
        'id' => 'google_mail', 'name' => 'Gmail / Google Workspace mail', 'category' => 'Communication', 'icon' => 'fa-brands fa-google',
        'description' => sc_addons_l('sc_addons_d_google_mail', 'Send CRM email through Gmail (OAuth).'),
        'fields' => [
            sc_addons_field('google_mail_client_id', 'client_id', 'text', $req),
            sc_addons_field('google_mail_client_secret', 'client_secret', 'password', ['required' => true, 'encrypted' => true]),
        ],
        'required_for_connected' => ['google_mail_client_id', 'google_mail_client_secret'],
        // application/controllers/admin/Smtp_oauth_google.php::token() starts the sign-in.
        'oauth_start_url' => admin_url('smtp_oauth_google/token'), 'oauth_provider' => 'Google',
        'settings_url' => admin_url('settings?group=email'),
        'checks' => fn () => trim((string) get_option('google_mail_refresh_token')) === ''
            ? [sc_addons_l('sc_addons_issue_oauth', 'Not authorized yet: open the settings page and sign in to grant access.')] : [],
    ];

    $entries[] = [
        'id' => 'call_center', 'name' => 'Call Center (Twilio Voice)', 'category' => 'Communication', 'icon' => 'fa-solid fa-headset',
        'module' => 'smart_choice_call_center',
        'description' => sc_addons_l('sc_addons_d_call_center', 'Browser dialer, IVR and call recording through Twilio.'),
        'fields' => [
            sc_addons_field('sccc_twilio_account_sid', 'account_sid', 'text', $req),
            sc_addons_field('sccc_twilio_auth_token', 'auth_token', 'password', $pw),
            sc_addons_field('sccc_twiml_app_sid', 'twiml_app_sid'),
            sc_addons_field('sccc_api_key_sid', 'api_key_sid'),
            sc_addons_field('sccc_api_key_secret', 'api_key_secret', 'password'),
            sc_addons_field('sccc_main_number', 'phone_number'),
        ],
        'required_for_connected' => ['sccc_twilio_account_sid', 'sccc_twilio_auth_token'],
        'settings_url' => admin_url('settings?group=smart_choice_call_center'),
        'checks' => function () {
            foreach (['sccc_twiml_app_sid', 'sccc_api_key_sid', 'sccc_api_key_secret'] as $o) {
                if (trim((string) get_option($o)) === '') {
                    return [sc_addons_l('sc_addons_issue_sccc_browser', 'Browser calling is off: TwiML App SID and API key SID/secret are needed.')];
                }
            }

            return [];
        },
        'test' => fn () => sc_addons_twilio_test('sccc_twilio_account_sid', 'sccc_twilio_auth_token'),
    ];

    $entries[] = [
        'id' => 'chat_twilio', 'name' => 'Team Chat calls & SMS (Twilio)', 'category' => 'Communication', 'icon' => 'fa-solid fa-phone-volume',
        'module' => 'prchat',
        'description' => sc_addons_l('sc_addons_d_chat_twilio', 'Voice/video calls and SMS from the team chat.'),
        'fields' => [
            sc_addons_field('prchat_twilio_account_sid', 'account_sid', 'text', $req),
            sc_addons_field('prchat_twilio_auth_token', 'auth_token', 'password', $pw),
            sc_addons_field('prchat_twilio_phone_number', 'phone_number'),
        ],
        'required_for_connected' => ['prchat_twilio_account_sid', 'prchat_twilio_auth_token'],
        'active_option' => 'prchat_twilio_enabled',
        'settings_url' => admin_url('settings?group=perfex_chat_settings'),
        'test' => fn () => sc_addons_twilio_test('prchat_twilio_account_sid', 'prchat_twilio_auth_token'),
    ];

    $entries[] = [
        'id' => 'telegram', 'name' => 'Telegram CRM alerts', 'category' => 'Communication', 'icon' => 'fa-brands fa-telegram',
        'module' => 'telegram_chat',
        'description' => sc_addons_l('sc_addons_d_telegram', 'Send lead, project, payment and ticket alerts to Telegram.'),
        // Alerts go through telegram_chat_api_request(): bot token + chat from the module's own tbltelegram_info row,
        // so this card reads that row and sends admins to the module's page to change it.
        'fields' => [],
        'required_for_connected' => [],
        'settings_url' => admin_url('telegram_chat'),
        'status_override' => function () {
            $bot = sc_addons_telegram_account();
            if (!$bot || trim((string) $bot->bot_token) === '') {
                return ['not_configured', sc_addons_l('sc_addons_reason_telegram_bot', 'No bot connected yet. Open Telegram settings and save the bot token.')];
            }
            if (trim((string) $bot->chat_id) === '' && trim((string) get_option('telegram_chat_default_chat_id')) === '') {
                return ['needs_attention', sc_addons_l('sc_addons_issue_telegram_chat', 'No chat ID is set, so alerts have nowhere to go.')];
            }

            return null;
        },
        'test' => function () {
            $bot = sc_addons_telegram_account();
            if (!$bot || trim((string) $bot->bot_token) === '') {
                return [false, sc_addons_l('sc_addons_test_missing', 'Required credentials are missing.')];
            }
            $r = sc_addons_http('GET', 'https://api.telegram.org/bot' . rawurlencode(trim((string) $bot->bot_token)) . '/getMe');
            if ($r['ok'] && !empty($r['json']['ok'])) {
                return [true, sprintf(sc_addons_l('sc_addons_test_telegram_ok', 'Connected as @%s.'), (string) ($r['json']['result']['username'] ?? 'bot'))];
            }

            return sc_addons_http_verdict($r, 'Telegram');
        },
    ];

    $entries[] = [
        'id' => 'whatsapp_cloud', 'name' => 'WhatsApp Cloud API', 'category' => 'Communication', 'icon' => 'fa-brands fa-whatsapp',
        'module' => 'whatsbot',
        'description' => sc_addons_l('sc_addons_d_whatsapp', 'WhatsApp chats, bots and campaigns through Meta Cloud API.'),
        'fields' => [
            sc_addons_field('wac_business_account_id', 'business_account_id', 'text', $req),
            sc_addons_field('wac_access_token', 'access_token', 'password', $pw),
            sc_addons_field('wac_phone_number_id', 'phone_number_id'),
            sc_addons_field('wac_verify_token', 'webhook_verify_token', 'password'),
        ],
        'required_for_connected' => ['wac_business_account_id', 'wac_access_token'],
        'settings_url' => admin_url('whatsbot/connect'),
        'checks' => fn () => trim((string) get_option('wac_verify_token')) === ''
            ? [sc_addons_l('sc_addons_issue_webhook_token', 'Webhook verify token is missing, so incoming messages are not received.')] : [],
        'test' => function () {
            $token = trim((string) get_option('wac_access_token'));
            $waba  = trim((string) get_option('wac_business_account_id'));
            if ($token === '' || $waba === '') {
                return [false, sc_addons_l('sc_addons_test_missing', 'Required credentials are missing.')];
            }
            $r = sc_addons_http('GET', 'https://graph.facebook.com/v19.0/' . rawurlencode($waba) . '/phone_numbers', ['Authorization: Bearer ' . $token]);

            return sc_addons_http_verdict($r, 'Meta');
        },
    ];

    $entries[] = [
        'id' => 'pusher', 'name' => 'Pusher (real-time)', 'category' => 'Communication', 'icon' => 'fa-solid fa-bolt',
        'description' => sc_addons_l('sc_addons_d_pusher', 'Real-time notifications and live chat updates.'),
        'fields' => [
            sc_addons_field('pusher_app_id', 'app_id', 'text', $req),
            sc_addons_field('pusher_app_key', 'app_key', 'text', $req),
            sc_addons_field('pusher_app_secret', 'app_secret', 'password', $pw),
            sc_addons_field('pusher_cluster', 'cluster', 'text', ['help' => sc_addons_l('sc_addons_h_pusher_cluster', 'For example mt1, us2 or eu.')]),
        ],
        'required_for_connected' => ['pusher_app_id', 'pusher_app_key', 'pusher_app_secret'],
        'active_option' => 'pusher_realtime_notifications',
        'settings_url' => admin_url('settings?group=pusher'),
        'test' => function () {
            $id      = trim((string) get_option('pusher_app_id'));
            $key     = trim((string) get_option('pusher_app_key'));
            $secret  = trim((string) get_option('pusher_app_secret'));
            $cluster = trim((string) get_option('pusher_cluster')) ?: 'mt1';
            if ($id === '' || $key === '' || $secret === '' || !preg_match('/^[a-z0-9-]+$/', $cluster)) {
                return [false, sc_addons_l('sc_addons_test_missing', 'Required credentials are missing.')];
            }
            $path  = '/apps/' . rawurlencode($id) . '/channels';
            $query = 'auth_key=' . rawurlencode($key) . '&auth_timestamp=' . time() . '&auth_version=1.0';
            $sig   = hash_hmac('sha256', "GET\n" . $path . "\n" . $query, $secret);
            $r     = sc_addons_http('GET', 'https://api-' . $cluster . '.pusher.com' . $path . '?' . $query . '&auth_signature=' . $sig);

            return sc_addons_http_verdict($r, 'Pusher');
        },
    ];

    $entries[] = [
        'id' => 'pushover', 'name' => 'Pushover', 'category' => 'Communication', 'icon' => 'fa-solid fa-mobile-screen',
        'module' => 'pushover',
        'description' => sc_addons_l('sc_addons_d_pushover', 'Push notifications to phones through Pushover.'),
        'fields' => [
            sc_addons_field('pushover_token', 'app_token', 'password', $pw),
            sc_addons_field('pushover_key', 'user_key', 'password', $pw),
        ],
        'required_for_connected' => ['pushover_token', 'pushover_key'],
        'settings_url' => admin_url('settings?group=pushover'),
        'test' => function () {
            $token = trim((string) get_option('pushover_token'));
            $user  = trim((string) get_option('pushover_key'));
            if ($token === '' || $user === '') {
                return [false, sc_addons_l('sc_addons_test_missing', 'Required credentials are missing.')];
            }
            $r = sc_addons_http('POST', 'https://api.pushover.net/1/users/validate.json', ['Content-Type: application/x-www-form-urlencoded'], http_build_query(['token' => $token, 'user' => $user]));

            return sc_addons_http_verdict($r, 'Pushover');
        },
    ];

    // ---------------- AI ----------------
    $entries[] = [
        'id' => 'sc_ai', 'name' => 'CRM AI assistant', 'category' => 'AI', 'icon' => 'fa-solid fa-wand-magic-sparkles',
        'description' => sc_addons_l('sc_addons_d_sc_ai', 'AI provider used by CRM AI features (Setup > Settings > AI).'),
        'fields' => [
            sc_addons_field('sc_ai_provider', 'provider', 'select', ['required' => true, 'options' => [
                ['value' => 'openai', 'label' => 'OpenAI'], ['value' => 'anthropic', 'label' => 'Anthropic Claude'],
                ['value' => 'gemini', 'label' => 'Google Gemini'], ['value' => 'azure_openai', 'label' => 'Azure OpenAI'],
                ['value' => 'custom', 'label' => sc_addons_l('sc_addons_opt_ai_custom', 'Custom / OpenAI-compatible')],
            ]]),
            sc_addons_field('sc_ai_api_key', 'api_key', 'password', $pw),
            sc_addons_field('sc_ai_model', 'model', 'text', $req),
            sc_addons_field('sc_ai_base_url', 'base_url', 'text', ['validate' => 'https_url', 'help' => sc_addons_l('sc_addons_h_base_url', 'Only for Azure or custom providers.')]),
            $allowCustom,
        ],
        'required_for_connected' => ['sc_ai_provider', 'sc_ai_api_key', 'sc_ai_model'],
        'settings_url' => admin_url('settings?group=ai'),
        'checks' => fn () => (in_array(get_option('sc_ai_provider'), ['custom', 'azure_openai'], true) && trim((string) get_option('sc_ai_base_url')) === '')
            ? [sc_addons_l('sc_addons_issue_base_url', 'This provider needs an API base URL.')] : [],
        'test' => fn () => sc_addons_ai_provider_test((string) get_option('sc_ai_provider'), trim((string) get_option('sc_ai_api_key')), trim((string) get_option('sc_ai_base_url'))),
    ];

    $entries[] = [
        'id' => 'openai', 'name' => 'OpenAI', 'category' => 'AI', 'icon' => 'fa-solid fa-robot',
        'module' => 'openai',
        'description' => sc_addons_l('sc_addons_d_openai', 'OpenAI key shared by AI writing, estimating and chat features.'),
        'fields' => [
            sc_addons_field('openai_api_key', 'api_key', 'password', $pw),
            sc_addons_field('openai_model', 'model'),
            sc_addons_field('openai_max_token', 'max_tokens'),
        ],
        'required_for_connected' => ['openai_api_key'],
        'settings_url' => admin_url('settings?group=openai'),
        'test' => fn () => sc_addons_openai_compatible_test('', trim((string) get_option('openai_api_key'))),
    ];

    $entries[] = [
        'id' => 'ai_repair', 'name' => 'AI Repair assistant', 'category' => 'AI', 'icon' => 'fa-solid fa-screwdriver-wrench',
        'module' => 'sc_ai_repair',
        'description' => sc_addons_l('sc_addons_d_ai_repair', 'AI provider for the CRM repair assistant.'),
        'fields' => [
            sc_addons_field('sc_ai_repair_api_key', 'api_key', 'password', $pw),
            sc_addons_field('sc_ai_repair_model', 'model'),
            sc_addons_field('sc_ai_repair_base_url', 'base_url', 'text', ['validate' => 'https_url']),
            $allowCustom,
        ],
        'required_for_connected' => ['sc_ai_repair_api_key'],
        'settings_url' => admin_url('sc_ai_repair/settings'),
        'test' => fn () => sc_addons_openai_compatible_test(trim((string) get_option('sc_ai_repair_base_url')), trim((string) get_option('sc_ai_repair_api_key')), sc_addons_l('sc_addons_ai_provider', 'AI provider')),
    ];

    $entries[] = [
        'id' => 'chat_ai', 'name' => 'Chatbot AI (OpenAI)', 'category' => 'AI', 'icon' => 'fa-regular fa-comments',
        'module' => 'prchat',
        'description' => sc_addons_l('sc_addons_d_chat_ai', 'OpenAI key used by the website/portal chatbot.'),
        'fields' => [sc_addons_field('chatbot_openai_api_key', 'api_key', 'password', $pw)],
        'required_for_connected' => ['chatbot_openai_api_key'],
        'settings_url' => admin_url('prchat/Chatbot_Admin'),
        'test' => fn () => sc_addons_openai_compatible_test('', trim((string) get_option('chatbot_openai_api_key'))),
    ];

    // ---------------- Calendar & Meetings ----------------
    $entries[] = [
        'id' => 'appointly_google', 'name' => 'Google Calendar (Appointments)', 'category' => 'Calendar & Meetings', 'icon' => 'fa-regular fa-calendar',
        'module' => 'appointly',
        'description' => sc_addons_l('sc_addons_d_appointly_google', 'Sync appointments with Google Calendar.'),
        'fields' => [
            sc_addons_field('appointly_google_client_id', 'client_id', 'text', $req),
            sc_addons_field('appointly_google_client_secret', 'client_secret', 'password', ['required' => true, 'encrypted' => 'scg']),
            sc_addons_field('appointly_google_calendar_id', 'calendar_id'),
        ],
        'required_for_connected' => ['appointly_google_client_id', 'appointly_google_client_secret'],
        // modules/appointly/controllers/google/Auth.php::login()
        'oauth_start_url' => site_url('appointly/google/auth/login'), 'oauth_provider' => 'Google',
        'settings_url' => admin_url('settings?group=appointly_settings'),
    ];

    $entries[] = [
        'id' => 'appointly_outlook', 'name' => 'Outlook Calendar (Appointments)', 'category' => 'Calendar & Meetings', 'icon' => 'fa-brands fa-microsoft',
        'module' => 'appointly',
        'description' => sc_addons_l('sc_addons_d_appointly_outlook', 'Add appointments to Outlook calendars.'),
        'fields' => [sc_addons_field('appointly_outlook_client_id', 'client_id', 'text', $req)],
        'required_for_connected' => ['appointly_outlook_client_id'],
        'settings_url' => admin_url('settings?group=appointly_settings'),
    ];

    $entries[] = [
        'id' => 'google_meet', 'name' => 'Google Meet', 'category' => 'Calendar & Meetings', 'icon' => 'fa-solid fa-video',
        'module' => 'google_meet',
        'description' => sc_addons_l('sc_addons_d_google_meet', 'Create Meet links for meetings and appointments.'),
        'fields' => [
            sc_addons_field('google_meet_use_google_calendar_api', 'use_calendar_api', 'checkbox'),
            sc_addons_field('google_meet_google_api_key', 'api_key', 'password'),
            sc_addons_field('google_meet_google_access_token', 'access_token', 'password'),
            sc_addons_field('google_meet_calendar_id', 'calendar_id'),
        ],
        'settings_url' => admin_url('google_meet/settings'),
        'checks' => fn () => (get_option('google_meet_use_google_calendar_api') === '1' && trim((string) get_option('google_meet_google_access_token')) === '')
            ? [sc_addons_l('sc_addons_issue_meet_token', 'Google Calendar API is on but no access token is saved, so links fall back to placeholders.')] : [],
    ];

    // ---------------- Marketing & Analytics ----------------
    $entries[] = [
        'id' => 'google_analytics', 'name' => 'Google Analytics 4', 'category' => 'Marketing & Analytics', 'icon' => 'fa-solid fa-chart-line',
        'module' => 'google_analytics',
        'description' => sc_addons_l('sc_addons_d_ga', 'Track CRM/portal visits and show GA4 reports.'),
        'fields' => [
            sc_addons_field('ga_crm_measurement_id', 'measurement_id', 'text', ['required' => true, 'help' => sc_addons_l('sc_addons_h_ga_id', 'Looks like G-XXXXXXX.')]),
            sc_addons_field('ga_default_property_id', 'property_id'),
            sc_addons_field('ga_service_account_json', 'service_account_json', 'secret_textarea', ['encrypted' => 'scg']),
        ],
        'required_for_connected' => ['ga_crm_measurement_id'],
        'active_option' => 'ga_enabled',
        'settings_url' => admin_url('google_analytics/settings'),
        'checks' => function () {
            $issues = [];
            if (!preg_match('/^G-[A-Z0-9]+$/i', trim((string) get_option('ga_crm_measurement_id')))) {
                $issues[] = sc_addons_l('sc_addons_issue_ga_id', 'Measurement ID should look like G-XXXXXXX.');
            }
            if (trim((string) get_option('ga_default_property_id')) !== '' && trim((string) get_option('ga_service_account_json')) === '') {
                $issues[] = sc_addons_l('sc_addons_issue_ga_reports', 'Reports need a service account JSON.');
            }

            return $issues;
        },
    ];

    $entries[] = [
        'id' => 'facebook_pixel', 'name' => 'Facebook (Meta) Pixel', 'category' => 'Marketing & Analytics', 'icon' => 'fa-brands fa-facebook',
        'module' => 'facebook_pixel',
        'description' => sc_addons_l('sc_addons_d_pixel', 'Add the Meta Pixel code to the client portal and/or admin area.'),
        'fields' => [
            sc_addons_field('facebook_pixel_clients_area', 'pixel_clients', 'textarea'),
            sc_addons_field('facebook_pixel_admin_area', 'pixel_admin', 'textarea'),
            sc_addons_field('facebook_pixel_clients_and_admin_area', 'pixel_both', 'textarea'),
        ],
        'required_any' => ['facebook_pixel_clients_area', 'facebook_pixel_admin_area', 'facebook_pixel_clients_and_admin_area'],
        'active_option' => 'facebook_pixel', 'active_on' => 'enable', 'active_off' => 'disable',
        'settings_url' => admin_url('facebook_pixel'),
    ];

    $entries[] = [
        'id' => 'bitly', 'name' => 'Bitly links', 'category' => 'Marketing & Analytics', 'icon' => 'fa-solid fa-link',
        'description' => sc_addons_l('sc_addons_d_bitly', 'Shorten invoice/estimate links in SMS.'),
        'fields' => [sc_addons_field('bitly_access_token', 'access_token', 'password', $pw)],
        'required_for_connected' => ['bitly_access_token'],
        'settings_url' => admin_url('settings?group=sms'),
        'test' => function () {
            $token = trim((string) get_option('bitly_access_token'));
            if ($token === '') {
                return [false, sc_addons_l('sc_addons_test_missing', 'Required credentials are missing.')];
            }

            return sc_addons_http_verdict(sc_addons_http('GET', 'https://api-ssl.bitly.com/v4/user', ['Authorization: Bearer ' . $token]), 'Bitly');
        },
    ];

    // ---------------- Storage & Files ----------------
    $entries[] = [
        'id' => 'google_apis', 'name' => 'Google Maps & Drive Picker', 'category' => 'Storage & Files', 'icon' => 'fa-brands fa-google-drive',
        'description' => sc_addons_l('sc_addons_d_google_apis', 'Google API key for maps and the Drive file picker.'),
        'fields' => [
            sc_addons_field('google_api_key', 'api_key', 'password', $pw),
            sc_addons_field('google_client_id', 'client_id'),
            sc_addons_field('enable_google_picker', 'enable_picker', 'checkbox'),
        ],
        'required_for_connected' => ['google_api_key'],
        'settings_url' => admin_url('settings?group=google'),
        'checks' => fn () => (get_option('enable_google_picker') === '1' && trim((string) get_option('google_client_id')) === '')
            ? [sc_addons_l('sc_addons_issue_picker', 'Drive picker is enabled but the Google client ID is missing.')] : [],
    ];

    $entries[] = [
        'id' => 'dropbox', 'name' => 'Dropbox chooser', 'category' => 'Storage & Files', 'icon' => 'fa-brands fa-dropbox',
        'description' => sc_addons_l('sc_addons_d_dropbox', 'Attach files from Dropbox.'),
        'fields' => [sc_addons_field('dropbox_app_key', 'app_key', 'text', $req)],
        'required_for_connected' => ['dropbox_app_key'],
        'settings_url' => admin_url('settings?group=misc'),
    ];

    // ---------------- Social login ----------------
    foreach (['google' => ['Google', 'fa-brands fa-google'], 'facebook' => ['Facebook', 'fa-brands fa-facebook'], 'apple' => ['Apple', 'fa-brands fa-apple']] as $p => [$label, $icon]) {
        $entries[] = [
            'id' => 'social_' . $p, 'name' => sprintf(sc_addons_l('sc_addons_social_name', 'Sign in with %s'), $label), 'category' => 'Social login', 'icon' => $icon,
            'module' => 'sc_social_login',
            'description' => sc_addons_l('sc_addons_d_social', 'Let staff and/or customers sign in with this provider.'),
            'fields' => [
                sc_addons_field('scsl_' . $p . '_client_id', 'client_id', 'text', $req),
                sc_addons_field('scsl_' . $p . '_client_secret', 'client_secret', 'password', ['required' => true, 'encrypted' => true]),
                sc_addons_field('scsl_' . $p . '_staff', 'for_staff', 'checkbox'),
                sc_addons_field('scsl_' . $p . '_client', 'for_customers', 'checkbox'),
            ],
            'required_for_connected' => ['scsl_' . $p . '_client_id', 'scsl_' . $p . '_client_secret'],
            'active_option' => ['scsl_' . $p . '_staff', 'scsl_' . $p . '_client'],
            'enable_via_fields' => true,
            'settings_url' => admin_url('sc_social_login/settings'),
            'checks' => function () {
                $issues = [];
                if (function_exists('is_https') && !is_https()) {
                    $issues[] = sc_addons_l('sc_addons_issue_https', 'Social sign-in only works over HTTPS.');
                }
                if (function_exists('scsl_license') && !scsl_license()) {
                    $issues[] = sc_addons_l('sc_addons_issue_license', 'Module license is missing or inactive.');
                }

                return $issues;
            },
        ];
    }

    // ---------------- Security ----------------
    $entries[] = [
        'id' => 'recaptcha', 'name' => 'Google reCAPTCHA', 'category' => 'Security', 'icon' => 'fa-solid fa-shield-halved',
        'description' => sc_addons_l('sc_addons_d_recaptcha', 'Protect login, registration and web forms from bots.'),
        'fields' => [
            sc_addons_field('recaptcha_site_key', 'site_key', 'text', $req),
            sc_addons_field('recaptcha_secret_key', 'secret_key', 'password', $pw),
        ],
        'required_for_connected' => ['recaptcha_site_key', 'recaptcha_secret_key'],
        'settings_url' => admin_url('settings?group=google'),
        'test' => function () {
            $secret = trim((string) get_option('recaptcha_secret_key'));
            if ($secret === '') {
                return [false, sc_addons_l('sc_addons_test_missing', 'Required credentials are missing.')];
            }
            $r     = sc_addons_http('POST', 'https://www.google.com/recaptcha/api/siteverify', ['Content-Type: application/x-www-form-urlencoded'], http_build_query(['secret' => $secret, 'response' => 'sc-addons-probe']));
            $codes = (array) ($r['json']['error-codes'] ?? []);
            if (!$r['json']) {
                return sc_addons_http_verdict($r, 'Google');
            }
            if (in_array('invalid-input-secret', $codes, true)) {
                return [false, sc_addons_l('sc_addons_test_recaptcha_bad', 'Google rejected the secret key.')];
            }

            return [true, sc_addons_l('sc_addons_test_recaptcha_ok', 'Google accepted the secret key.')];
        },
    ];

    // ---------------- Other ----------------
    $entries[] = [
        'id' => 'plaid', 'name' => 'Plaid bank feeds (Accounting)', 'category' => 'Other', 'icon' => 'fa-solid fa-building-columns',
        'module' => 'accounting',
        'description' => sc_addons_l('sc_addons_d_plaid', 'Import bank transactions into Accounting through Plaid.'),
        'fields' => [
            sc_addons_field('acc_plaid_client_id', 'client_id', 'text', $req),
            // Empty environment = production (same default as the Accounting settings page).
            sc_addons_field('acc_plaid_environment', 'environment', 'select', ['options' => [
                ['value' => 'production', 'label' => sc_addons_l('sc_addons_opt_production', 'Production')],
                ['value' => 'sandbox', 'label' => sc_addons_l('sc_addons_opt_sandbox', 'Sandbox')],
            ]]),
            sc_addons_field('acc_live_secret', 'live_secret', 'password'),
            sc_addons_field('acc_sandbox_secret', 'sandbox_secret', 'password'),
        ],
        'required_for_connected' => fn ($val) => ['acc_plaid_client_id', ($val('acc_plaid_environment') === 'sandbox' ? 'acc_sandbox_secret' : 'acc_live_secret')],
        'settings_url' => admin_url('accounting/setting?group=plaid_environment'),
        'checks' => fn () => (string) get_option('acc_plaid_environment') === 'sandbox'
            ? [sc_addons_l('sc_addons_issue_plaid_sandbox', 'Plaid is in sandbox mode, so real bank transactions are not imported.')] : [],
    ];

    $entries[] = [
        'id' => 'quickbooks', 'name' => 'QuickBooks Online', 'category' => 'Other', 'icon' => 'fa-solid fa-book',
        'module' => 'quickbooks',
        'description' => sc_addons_l('sc_addons_d_quickbooks', 'Sync customers, invoices and payments with QuickBooks.'),
        'fields' => [
            sc_addons_field('qb_client_id', 'client_id'),
            sc_addons_field('qb_client_secret', 'client_secret', 'password'),
            sc_addons_field('qb_client_id_demo', 'client_id_demo'),
            sc_addons_field('qb_client_secret_demo', 'client_secret_demo', 'password'),
            sc_addons_field('qb_test_mode_enabled', 'test_mode', 'checkbox'),
        ],
        // Test mode uses the sandbox ("demo") app keys, like get_clientID()/get_clientSecret() in the module.
        'required_for_connected' => fn ($val) => $val('qb_test_mode_enabled') === '1'
            ? ['qb_client_id_demo', 'qb_client_secret_demo'] : ['qb_client_id', 'qb_client_secret'],
        'active_option' => 'qb_active',
        // modules/quickbooks/controllers/Quickbooks_auth.php::index() — ends by reloading window.opener, so it runs in a popup.
        'oauth_start_url' => site_url('quickbooks/quickbooks_auth'), 'oauth_provider' => 'Intuit', 'oauth_popup' => true,
        'settings_url' => admin_url('settings?group=quickbooks'),
        'checks' => function () {
            $issues = [];
            if (trim((string) get_option('qb_refresh_token')) === '') {
                $issues[] = sc_addons_l('sc_addons_issue_oauth', 'Not authorized yet: open the settings page and sign in to grant access.');
            }
            if (get_option('qb_test_mode_enabled') === '1') {
                $issues[] = sc_addons_l('sc_addons_issue_test_mode', 'Test / sandbox mode is on, so real payments are not collected.');
            }

            return $issues;
        },
    ];

    $entries[] = [
        'id' => 'network_agent', 'name' => 'Smart Network agent', 'category' => 'Other', 'icon' => 'fa-solid fa-network-wired',
        'module' => 'smart_network_manager',
        'description' => sc_addons_l('sc_addons_d_network', 'Windows agent used for network scans and device actions.'),
        'fields' => [
            sc_addons_field('smart_network_manager_agent_url', 'agent_url', 'text', $req),
            sc_addons_field('smart_network_manager_agent_token', 'agent_token', 'password', $pw),
        ],
        'required_for_connected' => ['smart_network_manager_agent_url', 'smart_network_manager_agent_token'],
        'settings_url' => admin_url('smart_network_manager'),
    ];

    return $entries;
}
