<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
 * Setup guides shown in the Connect popup: ordered steps (language keys sc_addons_g_<steps>_<n>, EN+ES),
 * a link to the provider console, and the exact redirect / callback / webhook URLs the owning module uses
 * (copied from each module's code so the admin can paste them into the provider console).
 */

/** Guide definitions keyed by registry id. URLs are built lazily because they depend on base_url(). */
function sc_addons_guide_defs()
{
    $origin = function () {
        $p = parse_url(base_url());

        return ($p['scheme'] ?? 'https') . '://' . ($p['host'] ?? '') . (isset($p['port']) ? ':' . $p['port'] : '');
    };
    $google = 'https://console.cloud.google.com/apis/credentials';
    $entra  = 'https://entra.microsoft.com/#view/Microsoft_AAD_RegisteredApps/ApplicationsListBlade';
    $twilio = ['steps' => 'twilio', 'docs' => 'https://console.twilio.com/', 'docs_name' => 'Twilio Console'];
    $openai = ['steps' => 'openai', 'docs' => 'https://platform.openai.com/api-keys', 'docs_name' => 'OpenAI'];

    return [
        'smtp'             => ['steps' => 'smtp'],
        // application/controllers/admin/Smtp_oauth_google.php + Smtp_oauth_microsoft.php
        'google_mail'      => ['steps' => 'google_mail', 'docs' => $google, 'docs_name' => 'Google Cloud Console',
            'urls' => fn () => [['redirect', admin_url('smtp_oauth_google/token')]]],
        'microsoft_mail'   => ['steps' => 'microsoft_mail', 'docs' => $entra, 'docs_name' => 'Microsoft Entra',
            'urls' => fn () => [['redirect', admin_url('smtp_oauth_microsoft/token')]]],
        // modules/smart_choice_call_center/views/settings/index.php
        'call_center'      => $twilio + ['urls' => fn () => [['webhook', site_url('smart_choice_call_center/webhook/incoming')], ['webhook', site_url('smart_choice_call_center/webhook/browser_outgoing')]]],
        'chat_twilio'      => $twilio,
        'sms_twilio'       => $twilio,
        'telegram'         => ['steps' => 'telegram', 'docs' => 'https://t.me/BotFather', 'docs_name' => 'BotFather'],
        // modules/whatsbot/views/account_connected.php
        'whatsapp_cloud'   => ['steps' => 'whatsapp', 'docs' => 'https://developers.facebook.com/apps/', 'docs_name' => 'Meta for Developers',
            'urls' => fn () => [['webhook', site_url('whatsbot/whatsapp_webhook')]]],
        'pusher'           => ['steps' => 'pusher', 'docs' => 'https://dashboard.pusher.com/', 'docs_name' => 'Pusher'],
        'pushover'         => ['steps' => 'pushover', 'docs' => 'https://pushover.net/apps/build', 'docs_name' => 'Pushover'],
        'sc_ai'            => ['steps' => 'sc_ai', 'docs' => 'https://platform.openai.com/api-keys', 'docs_name' => 'OpenAI'],
        'openai'           => $openai,
        'chat_ai'          => $openai,
        'ai_repair'        => ['steps' => 'sc_ai', 'docs' => 'https://platform.openai.com/api-keys', 'docs_name' => 'OpenAI'],
        // modules/appointly/libraries/Googleplus.php + views/appointly_settings.php
        'appointly_google' => ['steps' => 'google_oauth', 'docs' => $google, 'docs_name' => 'Google Cloud Console',
            'urls' => fn () => [['redirect', base_url() . 'appointly/google/auth/oauth']]],
        'appointly_outlook' => ['steps' => 'appointly_outlook', 'docs' => $entra, 'docs_name' => 'Microsoft Entra',
            'urls' => fn () => [['redirect', base_url() . 'admin/appointly/appointments']]],
        'google_meet'      => ['steps' => 'google_meet', 'docs' => 'https://console.cloud.google.com/apis/library/calendar-json.googleapis.com', 'docs_name' => 'Google Cloud Console'],
        'google_analytics' => ['steps' => 'ga', 'docs' => 'https://analytics.google.com/', 'docs_name' => 'Google Analytics'],
        'facebook_pixel'   => ['steps' => 'pixel', 'docs' => 'https://business.facebook.com/events_manager2', 'docs_name' => 'Meta Events Manager'],
        'bitly'            => ['steps' => 'bitly', 'docs' => 'https://app.bitly.com/settings/api/', 'docs_name' => 'Bitly'],
        'google_apis'      => ['steps' => 'google_apis', 'docs' => 'https://console.cloud.google.com/google/maps-apis/credentials', 'docs_name' => 'Google Cloud Console',
            'urls' => fn () => [['js_origin', $origin()]]],
        'dropbox'          => ['steps' => 'dropbox', 'docs' => 'https://www.dropbox.com/developers/apps', 'docs_name' => 'Dropbox App Console',
            'urls' => fn () => [['domain', parse_url(base_url(), PHP_URL_HOST)]]],
        // modules/sc_social_login/views/manage.php (callback route) — Apple posts back to the same URL.
        'social_google'    => ['steps' => 'social_google', 'docs' => $google, 'docs_name' => 'Google Cloud Console',
            'urls' => fn () => [['redirect', site_url('sc_social_login/oauth/callback/google')]]],
        'social_facebook'  => ['steps' => 'social_facebook', 'docs' => 'https://developers.facebook.com/apps/', 'docs_name' => 'Meta for Developers',
            'urls' => fn () => [['redirect', site_url('sc_social_login/oauth/callback/facebook')]]],
        'social_apple'     => ['steps' => 'social_apple', 'docs' => 'https://developer.apple.com/account/resources/identifiers/list/serviceId', 'docs_name' => 'Apple Developer',
            'urls' => fn () => [['redirect', site_url('sc_social_login/oauth/callback/apple')], ['domain', parse_url(base_url(), PHP_URL_HOST)]]],
        'recaptcha'        => ['steps' => 'recaptcha', 'docs' => 'https://www.google.com/recaptcha/admin', 'docs_name' => 'reCAPTCHA admin',
            'urls' => fn () => [['domain', parse_url(base_url(), PHP_URL_HOST)]]],
        'plaid'            => ['steps' => 'plaid', 'docs' => 'https://dashboard.plaid.com/developers/keys', 'docs_name' => 'Plaid Dashboard'],
        // modules/quickbooks/controllers/Quickbooks_auth.php (redirectUri)
        'quickbooks'       => ['steps' => 'quickbooks', 'docs' => 'https://developer.intuit.com/app/developer/dashboard', 'docs_name' => 'Intuit Developer',
            'urls' => fn () => [['redirect', site_url('quickbooks/quickbooks_auth')]]],
        'network_agent'    => ['steps' => 'network_agent'],
        // application/libraries/gateways/Stripe_gateway.php ($webhookEndPoint, created automatically)
        'gw_stripe'        => ['steps' => 'stripe', 'docs' => 'https://dashboard.stripe.com/apikeys', 'docs_name' => 'Stripe Dashboard',
            'urls' => fn () => [['webhook_auto', site_url('gateways/stripe/webhook_endpoint')]]],
        'gw_paypal_checkout' => ['steps' => 'paypal', 'docs' => 'https://developer.paypal.com/dashboard/applications', 'docs_name' => 'PayPal Developer'],
        // modules/smart_square_payments/config/routes.php
        'gw_smart_square'  => ['steps' => 'square', 'docs' => 'https://developer.squareup.com/apps', 'docs_name' => 'Square Developer',
            'urls' => fn () => [['webhook', site_url('smart_square_payments/webhook')]]],
    ];
}

/** Guide definition for a registry entry (generic steps + settings link when no specific guide exists). */
function sc_addons_guide_def(array $entry)
{
    $defs = sc_addons_guide_defs();

    return $defs[$entry['id']] ?? ['steps' => 'generic', 'generic' => true];
}

/** JSON-able guide for the Connect popup. */
function sc_addons_guide(array $entry)
{
    $def   = is_array($entry['guide']) ? $entry['guide'] : sc_addons_guide_def($entry);
    $steps = [];
    for ($i = 1; $i <= 8; $i++) {
        $line = sc_addons_t('sc_addons_g_' . $def['steps'] . '_' . $i);
        if ($line === '') {
            break;
        }
        $steps[] = $line;
    }
    if (!$steps) {
        for ($i = 1; $i <= 3; $i++) {
            $steps[] = sc_addons_t('sc_addons_g_generic_' . $i);
        }
        $def['generic'] = true;
    }
    $labels = [
        'redirect'     => sc_addons_l('sc_addons_gu_redirect', 'Authorized redirect URI'),
        'webhook'      => sc_addons_l('sc_addons_gu_webhook', 'Webhook / callback URL'),
        'webhook_auto' => sc_addons_l('sc_addons_gu_webhook_auto', 'Webhook URL (created automatically)'),
        'js_origin'    => sc_addons_l('sc_addons_gu_js_origin', 'Authorized JavaScript origin'),
        'domain'       => sc_addons_l('sc_addons_gu_domain', 'Your domain'),
    ];
    $urls = [];
    if (isset($def['urls']) && is_callable($def['urls'])) {
        foreach ((array) call_user_func($def['urls']) as [$kind, $url]) {
            if (is_string($url) && $url !== '') {
                $urls[] = ['kind' => $kind, 'label' => $labels[$kind] ?? $kind, 'url' => $url];
            }
        }
    }

    return [
        'steps'   => $steps,
        'generic' => !empty($def['generic']),
        'docs'    => !empty($def['docs']) ? ['url' => $def['docs'], 'label' => sprintf(sc_addons_l('sc_addons_gu_docs', 'Open %s'), $def['docs_name'] ?? $entry['name'])] : null,
        'urls'    => $urls,
    ];
}
