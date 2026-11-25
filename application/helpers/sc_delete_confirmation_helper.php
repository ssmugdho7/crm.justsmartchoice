<?php

defined('BASEPATH') or exit('No direct script access allowed');

const SC_DELETE_ACTION_PATTERN = '(?:^|_)(?:delete|remove|destroy|purge|trash)(?:_|$)|^clear_(?:acceptance_info|signature|queued_emails|activity_log|pipe_log|sessions)$';

function sc_delete_action_name(string $name): bool
{
    $name = preg_replace('/([a-z0-9])([A-Z])/', '$1_$2', $name);

    return (bool) preg_match('~' . SC_DELETE_ACTION_PATTERN . '~i', str_replace('-', '_', $name));
}

function sc_delete_confirmation_assets(): void
{
    static $loaded = false;
    if ($loaded) {
        return;
    }
    $loaded = true;

    $asset = 'assets/js/sc-delete-confirmation.js';
    $config = [
        'basePath' => parse_url(site_url(), PHP_URL_PATH) ?: '/',
        'actionPattern' => SC_DELETE_ACTION_PATTERN,
        'title' => _l('delete'),
        'cancel' => _l('cancel'),
        'instruction' => _l('sc_type_delete_to_confirm'),
    ];
    echo '<script id="sc-delete-config" type="application/json">' . json_encode($config, JSON_HEX_TAG) . '</script>';
    echo '<script src="' . e(base_url($asset) . '?v=' . filemtime(FCPATH . $asset)) . '"></script>';
}

hooks()->add_action('app_admin_footer', 'sc_delete_confirmation_assets', 1);
hooks()->add_action('app_customers_footer', 'sc_delete_confirmation_assets', 1);

function sc_delete_confirmation_guard(): void
{
    $ci = get_instance();
    if (is_cli() || !($ci instanceof AdminController || ($ci instanceof ClientsController && is_client_logged_in()))) {
        return;
    }

    $method = $ci->input->method(true);
    $action = $ci->router->fetch_method();
    $operation = $ci->input->post('action');
    if ($method !== 'DELETE' && !sc_delete_action_name($action) && !$ci->input->post('mass_delete')
        && !in_array($operation, ['delete', 'remove', 'destroy', 'purge', 'trash'], true)) {
        return;
    }

    if ($method === 'POST' && $ci->input->post('delete_confirmation') === 'DELETE') {
        return;
    }

    // A custom header allows legacy AJAX deletes to retain their request method.
    // A query parameter never authorizes a delete.
    if ($ci->input->get_request_header('X-CRM-Delete-Confirmation') === 'DELETE'
        && in_array($ci->input->get_request_header('Sec-Fetch-Site'), [null, 'same-origin'], true)) {
        return;
    }

    $instruction = _l('sc_type_delete_to_confirm');
    $ci->output->set_header('Cache-Control: no-store');
    if ($method === 'GET' && !$ci->input->is_ajax_request()
        && in_array($ci->input->get_request_header('Sec-Fetch-Dest'), [null, 'document', 'iframe'], true)) {
        $query = $ci->input->server('QUERY_STRING');
        $page = $ci->load->view('delete_confirmation', [
            'action' => site_url($ci->uri->uri_string()) . ($query ? '?' . $query : ''),
            'cancelUrl' => $ci instanceof AdminController ? admin_url() : site_url(),
            'instruction' => $instruction,
        ], true);
        $ci->output->set_output($page)->_display();
        exit;
    }

    $ci->output->set_status_header(422)->set_content_type('application/json')
        ->set_output(json_encode(['success' => false, 'message' => $instruction]))->_display();
    exit;
}
