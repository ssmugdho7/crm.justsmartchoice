<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Add-ons & Integrations hub. Administrators only. Every mutation is POST-only (Perfex CSRF enforces the token),
 * validated against the registry (only declared options can be written) and written to the activity log.
 * Secret values are never sent to the browser or written to the log.
 */
class Sc_addons extends AdminController
{
    /** Values written during this request; Perfex caches autoloaded options, so status checks read these first. */
    private $written = [];

    public function __construct()
    {
        parent::__construct();
        $this->load->library('encryption');
        hooks()->add_filter('get_option', function ($value, $name) {
            return array_key_exists($name, $this->written) ? $this->written[$name] : $value;
        }, 10, 2);
        if (!is_admin()) {
            if ($this->input->is_ajax_request()) {
                $this->json(['success' => false, 'message' => sc_addons_t('sc_addons_denied')], 403);
                exit;
            }
            access_denied('Add-ons & Integrations');
        }
    }

    public function index()
    {
        $data['title']      = sc_addons_t('sc_addons_title');
        $data['cards']      = $this->cards();
        $data['modules']    = $this->module_rows();
        $data['categories'] = SC_ADDONS_CATEGORIES;
        $this->load->view('index', $data);
    }

    /** GET (read-only): fresh cards + modules (used after every action). Cards never contain secret values. */
    public function data()
    {
        if ($this->input->method() !== 'get') {
            return $this->json(['success' => false, 'message' => 'Method not allowed'], 405);
        }
        $this->json(['success' => true, 'cards' => $this->cards(), 'modules' => $this->module_rows()]);
    }

    /** GET: form definition (no secret values). POST: save (+ connect). */
    public function configure($id = '')
    {
        $entry = $this->entry($id);
        if (!$entry) {
            return;
        }
        if ($this->input->method() !== 'post') {
            return $this->json(['success' => true, 'entry' => [
                'id' => $entry['id'], 'name' => $entry['name'], 'description' => $entry['description'],
                'settings_url' => $entry['settings_url'], 'toggleable' => $entry['active_option'] !== null,
                'enable_via_fields' => (bool) $entry['enable_via_fields'],
                'exclusive_with' => $this->exclusive_names($entry),
                // Same-origin route of the owning module that starts the provider sign-in (empty = no OAuth step).
                'oauth_start_url' => (string) $entry['oauth_start_url'], 'oauth_provider' => (string) $entry['oauth_provider'],
                'oauth_popup' => (bool) $entry['oauth_popup'],
            ], 'fields' => $this->form_fields($entry), 'guide' => sc_addons_guide($entry), 'card' => sc_addons_card($entry)]);
        }

        // Raw input (no global XSS filter): secrets and pixel snippets must be stored exactly as typed.
        // Values are escaped on output; secrets are never echoed back.
        $posted  = $this->input->post('fields', false);
        $posted  = is_array($posted) ? $posted : [];
        $clear   = $this->input->post('clear', false);
        $clear   = is_array($clear) ? array_keys(array_filter($clear, fn ($v) => $v === '1')) : [];
        $connect = $this->input->post('connect', false) === '1';
        $errors  = [];
        $writes  = [];
        $cleared = [];
        foreach ($entry['fields'] as $f) {
            $opt     = $f['option'];
            $present = array_key_exists($opt, $posted);
            $raw     = $present && is_scalar($posted[$opt]) ? (string) $posted[$opt] : '';
            $secret  = in_array($f['type'], ['password', 'secret_textarea'], true) || $f['encrypted'];
            if ($secret && in_array($opt, $clear, true) && trim($raw) === '') {
                // "Clear saved value" (a blank secret otherwise keeps the stored one).
                if ($f['required']) {
                    $errors[$opt] = sc_addons_t('sc_addons_err_required');
                    continue;
                }
                if (sc_addons_option_filled($opt)) {
                    $writes[$opt] = '';
                    $cleared[]    = $opt;
                }
                continue;
            }
            if ($f['type'] === 'checkbox') {
                $writes[$opt] = $raw === '1' ? '1' : '0';
                continue;
            }
            $raw = trim($raw);
            if ($f['type'] === 'select') {
                $allowed = array_column($f['options'] ?? [], 'value');
                if ($present && $raw !== '' && !in_array($raw, $allowed, true)) {
                    $errors[$opt] = sc_addons_t('sc_addons_err_invalid');
                    continue;
                }
                if (!$present || $raw === '') {
                    if ($f['required'] && !sc_addons_option_filled($opt)) {
                        $errors[$opt] = sc_addons_t('sc_addons_err_required');
                    }
                    continue;
                }
                $writes[$opt] = $raw;
                continue;
            }
            if (mb_strlen($raw) > ($f['type'] === 'text' || $secret ? 5000 : 20000)) {
                $errors[$opt] = sc_addons_t('sc_addons_err_too_long');
                continue;
            }
            if ($raw !== '' && !$secret && $f['type'] === 'text' && ($f['validate'] ?? '') !== 'https_url' && !preg_match('/^[\p{L}\p{N} ._:\/@+=,#()&\'-]*$/u', $raw)) {
                $errors[$opt] = sc_addons_t('sc_addons_err_invalid');
                continue;
            }
            if (($f['validate'] ?? '') === 'https_url' && $raw !== '' && (!filter_var($raw, FILTER_VALIDATE_URL) || stripos($raw, 'https://') !== 0)) {
                $errors[$opt] = sc_addons_t('sc_addons_url_not_https');
                continue;
            }
            if ($raw === '' && !$present && !$secret) {
                if ($f['required'] && !sc_addons_option_filled($opt)) {
                    $errors[$opt] = sc_addons_t('sc_addons_err_required');
                }
                continue;
            }
            if ($raw === '') {
                if ($secret) {
                    // Blank secret keeps the saved one.
                    if ($f['required'] && !sc_addons_option_filled($opt)) {
                        $errors[$opt] = sc_addons_t('sc_addons_err_required');
                    }
                    continue;
                }
                if ($f['required']) {
                    $errors[$opt] = sc_addons_t('sc_addons_err_required');
                    continue;
                }
                if ($present) {
                    $writes[$opt] = '';
                }
                continue;
            }
            if ($f['encrypted'] === 'scg') {
                $enc = $this->scg_encrypt($raw);
                if (!is_string($enc) || $enc === '') {
                    $errors[$opt] = sc_addons_t('sc_addons_err_encrypt');
                    continue;
                }
                $writes[$opt] = $enc;
            } elseif ($f['encrypted']) {
                $enc = $this->encryption->encrypt($raw);
                if (!is_string($enc) || $enc === '') {
                    $errors[$opt] = sc_addons_t('sc_addons_err_encrypt');
                    continue;
                }
                $writes[$opt] = $enc;
            } else {
                $writes[$opt] = $raw;
            }
        }
        // At-least-one-of rule (e.g. Meta Pixel snippets).
        if ($entry['required_any']) {
            $any = false;
            foreach ($entry['required_any'] as $opt) {
                $any = $any || (array_key_exists($opt, $writes) ? $writes[$opt] !== '' : sc_addons_option_filled($opt));
            }
            if (!$any) {
                $errors[$entry['required_any'][0]] = sc_addons_t('sc_addons_err_required_any');
            }
        }
        // Fields required by a dynamic rule (QuickBooks test mode, Plaid environment) that would end up empty.
        $prospective = fn ($o) => array_key_exists($o, $writes) ? (string) $writes[$o] : (string) get_option($o);
        if (is_callable($entry['required_for_connected']) && ($connect || $entry['active_option'] === null)) {
            foreach (sc_addons_required_options($entry, $prospective) as $opt) {
                if (!isset($errors[$opt]) && trim($prospective($opt)) === '' && in_array($opt, array_column($entry['fields'], 'option'), true)) {
                    $errors[$opt] = sc_addons_t('sc_addons_err_required');
                }
            }
        }
        // Entries switched on through their own checkboxes (social login audiences): Connect needs at least one ticked.
        if ($connect && $entry['enable_via_fields']) {
            $on = false;
            foreach ((array) $entry['active_option'] as $o) {
                $on = $on || $prospective($o) === (string) $entry['active_on'];
            }
            if (!$on) {
                $errors[((array) $entry['active_option'])[0]] = sc_addons_t('sc_addons_err_audience');
            }
        }
        if ($errors) {
            return $this->json(['success' => false, 'message' => sc_addons_t('sc_addons_err_fix_fields'), 'errors' => $errors], 422);
        }

        $changed = [];
        foreach ($writes as $opt => $value) {
            if ((string) get_option($opt) !== (string) $value) {
                $changed[] = $opt;
            }
            $this->write_option($opt, $value);
        }
        if ($changed) {
            // Credentials changed: an earlier test result no longer describes them.
            sc_addons_clear_test($entry['id']);
        }

        if ($connect && !$entry['enable_via_fields']) {
            $this->set_enabled($entry, true);
        } elseif ($connect) {
            $this->set_enabled_exclusive($entry);
        }
        log_activity(sprintf('Add-ons: %s %s (%d fields changed%s)', $entry['name'], $connect ? 'connected' : 'settings saved', count($changed),
            $cleared ? ', ' . count($cleared) . ' saved values cleared' : ''));

        $test = null;
        if ($connect) {
            $test = sc_addons_run_test($entry);
        }

        return $this->json(['success' => true, 'message' => sprintf(sc_addons_t($connect ? 'sc_addons_saved_connected' : 'sc_addons_saved'), $entry['name']),
            'test' => $test ? ['ok' => $test[0], 'message' => $test[1]] : null, 'card' => sc_addons_card($entry)]);
    }

    public function disconnect($id = '')
    {
        $entry = $this->post_entry($id);
        if (!$entry) {
            return;
        }
        $block = sc_addons_disconnect_block($entry);
        if ($block !== '') {
            return $this->json(['success' => false, 'message' => $block], 409);
        }
        if ($entry['active_option'] === null) {
            // No on/off switch: disconnecting means erasing the saved credentials (the UI confirms and lists them).
            $erased = [];
            foreach (sc_addons_erase_options($entry) as $opt) {
                if (sc_addons_option_filled($opt)) {
                    $this->write_option($opt, '');
                    $erased[] = $opt;
                }
            }
            sc_addons_clear_test($entry['id']);
            log_activity(sprintf('Add-ons: %s disconnected (%d saved credentials erased: %s)', $entry['name'], count($erased), implode(', ', $erased)));

            return $this->json(['success' => true, 'message' => sprintf(sc_addons_t('sc_addons_disconnected_erased'), $entry['name']), 'card' => sc_addons_card($entry)]);
        }
        $this->set_enabled($entry, false);
        log_activity('Add-ons: ' . $entry['name'] . ' disconnected (credentials kept)');

        return $this->json(['success' => true, 'message' => sprintf(sc_addons_t('sc_addons_disconnected'), $entry['name']), 'card' => sc_addons_card($entry)]);
    }

    public function test($id = '')
    {
        $entry = $this->post_entry($id);
        if (!$entry) {
            return;
        }
        $r = sc_addons_run_test($entry);
        log_activity('Add-ons: ' . $entry['name'] . ' tested (' . ($r[0] ? 'passed' : 'failed') . ')');

        return $this->json(['success' => true, 'ok' => $r[0], 'message' => $r[1], 'card' => sc_addons_card($entry)]);
    }

    // ---------------- Module lifecycle ----------------

    public function install($name = '')
    {
        $m = $this->post_module($name);
        if (!$m) {
            return;
        }
        if ($m['installed_version'] !== false) {
            return $this->json(['success' => false, 'message' => sc_addons_t('sc_addons_mod_already_installed')], 422);
        }

        return $this->module_action('install', $name, 'sc_addons_mod_installed');
    }

    public function activate($name = '')
    {
        $m = $this->post_module($name);
        if (!$m) {
            return;
        }
        if ((int) $m['activated'] === 1) {
            return $this->json(['success' => false, 'message' => sc_addons_t('sc_addons_mod_already_active')], 422);
        }

        return $this->module_action('activate', $name, 'sc_addons_mod_activated');
    }

    public function deactivate($name = '')
    {
        $m = $this->post_module($name);
        if (!$m) {
            return;
        }
        if (sc_addons_is_protected_module($name)) {
            return $this->json(['success' => false, 'message' => sc_addons_t('sc_addons_mod_protected')], 403);
        }
        if ((int) $m['activated'] !== 1) {
            return $this->json(['success' => false, 'message' => sc_addons_t('sc_addons_mod_not_active')], 422);
        }

        return $this->module_action('deactivate', $name, 'sc_addons_mod_deactivated');
    }

    public function uninstall($name = '')
    {
        $m = $this->post_module($name);
        if (!$m) {
            return;
        }
        if (sc_addons_is_protected_module($name)) {
            return $this->json(['success' => false, 'message' => sc_addons_t('sc_addons_mod_protected')], 403);
        }
        $confirm  = $this->input->post('confirm');
        $combined = $this->input->post('deactivate_first') === '1';
        if ((int) $m['activated'] === 1 && !$combined) {
            return $this->json(['success' => false, 'message' => sc_addons_t('sc_addons_mod_deactivate_first')], 422);
        }
        if (!is_string($confirm) || !hash_equals((string) $name, $confirm)) {
            return $this->json(['success' => false, 'message' => sc_addons_t('sc_addons_mod_confirm_mismatch')], 422);
        }
        $real = realpath($m['path']);
        $base = realpath(APP_MODULES_PATH);
        if (!$real || !$base || strpos($real, $base . DIRECTORY_SEPARATOR) !== 0 || $real === $base) {
            return $this->json(['success' => false, 'message' => sc_addons_t('sc_addons_mod_protected')], 403);
        }
        if ((int) $m['activated'] === 1) {
            // "Deactivate & uninstall": the typed confirmation above covers both steps.
            try {
                $ok = $this->app_modules->deactivate($name);
            } catch (Throwable $e) {
                log_message('error', 'sc_addons module deactivate failed for ' . $name . ': ' . get_class($e));
                $ok = false;
            }
            log_activity(sprintf('Add-ons: module %s deactivate %s (before uninstall)', $name, $ok ? 'done' : 'failed'));
            if (!$ok) {
                return $this->json(['success' => false, 'message' => sc_addons_t('sc_addons_mod_failed')], 422);
            }
            $this->app_modules = new App_modules();
        }

        return $this->module_action('uninstall', $name, 'sc_addons_mod_uninstalled');
    }

    /**
     * sc_module_licensing refuses activation from its pre_activate_module hook with show_error(403) + exit.
     * Detect that up front (same rule as the hook) so the admin gets a clear JSON reason.
     * @return string|null refusal message, null when activation may proceed
     */
    private function licensing_refusal(array $m)
    {
        $manifest = rtrim((string) $m['path'], '/\\') . DIRECTORY_SEPARATOR . 'sc_license.php';
        if (!is_file($manifest) || !function_exists('scl_licensed')) {
            return null;
        }
        $licensed = false;
        try {
            $declared = (function ($f) {
                return require $f;
            })($manifest);
            $licensed = $declared === $m['system_name'] && scl_licensed($m['system_name']);
        } catch (Throwable $e) {
            log_message('error', 'sc_addons licensing pre-check failed for ' . $m['system_name'] . ': ' . get_class($e));
        }
        if ($licensed) {
            return null;
        }
        $reason = sc_addons_t('scl_activation_required');

        return sprintf(sc_addons_t('sc_addons_mod_license_blocked'), $m['system_name']) . ($reason !== '' ? ' ' . $reason : '');
    }

    // ---------------- Internals ----------------

    private function module_action($action, $name, $okKey)
    {
        if ($action === 'install' || $action === 'activate') {
            $m      = $this->app_modules->get($name);
            $refuse = $m ? $this->licensing_refusal($m) : null;
            if ($refuse !== null) {
                log_activity(sprintf('Add-ons: module %s %s blocked by module licensing', $name, $action));

                return $this->json(['success' => false, 'code' => 'license', 'message' => $refuse], 403);
            }
            // Safety net: if any activation hook still calls show_error()+exit, turn that page into a JSON reason.
            $this->guard_exit($name, $action);
        }
        try {
            $ok = $action === 'install' || $action === 'activate'
                ? $this->app_modules->activate($name)
                : ($action === 'deactivate' ? $this->app_modules->deactivate($name) : $this->app_modules->uninstall($name));
        } catch (Throwable $e) {
            $this->exit_guard = false;
            log_message('error', 'sc_addons module ' . $action . ' failed for ' . $name . ': ' . get_class($e));
            log_activity(sprintf('Add-ons: module %s %s failed', $name, $action));

            return $this->json(['success' => false, 'message' => sc_addons_t('sc_addons_mod_failed')], 500);
        }
        $this->exit_guard = false;
        log_activity(sprintf('Add-ons: module %s %s %s', $name, $action, $ok ? 'done' : 'refused'));
        if (!$ok) {
            return $this->json(['success' => false, 'message' => sc_addons_t('sc_addons_mod_failed')], 422);
        }
        // Reload so cards/modules reflect the new state in this response.
        $this->app_modules = new App_modules();

        return $this->json(['success' => true, 'message' => sprintf(sc_addons_t($okKey), $name), 'cards' => $this->cards(), 'modules' => $this->module_rows()]);
    }

    /** True while a module hook runs; a shutdown during that time means a hook exited (e.g. licensing refusal). */
    private $exit_guard = false;

    private function guard_exit($name, $action)
    {
        $this->exit_guard = true;
        $level            = ob_get_level();
        ob_start();
        register_shutdown_function(function () use ($name, $action, $level) {
            if (!$this->exit_guard) {
                return;
            }
            $page = '';
            while (ob_get_level() > $level) {
                $page .= (string) ob_get_clean();
            }
            $text = trim(preg_replace('/\s+/', ' ', strip_tags(html_entity_decode(preg_replace('#<(script|style|title|h1)[^>]*>.*?</\1>#si', ' ', $page), ENT_QUOTES, 'UTF-8'))));
            $licensing = stripos($page, 'licen') !== false;
            log_activity(sprintf('Add-ons: module %s %s stopped by a module hook%s', $name, $action, $licensing ? ' (module licensing)' : ''));
            if (!headers_sent()) {
                http_response_code($licensing ? 403 : 422);
                header('Content-Type: application/json; charset=utf-8');
                header('Cache-Control: no-store');
                header('X-Content-Type-Options: nosniff');
            }
            $msg = $licensing ? sprintf(sc_addons_t('sc_addons_mod_license_blocked'), $name) : sc_addons_t('sc_addons_mod_hook_stopped');
            echo json_encode(['success' => false, 'code' => $licensing ? 'license' : 'hook',
                'message' => trim($msg . ' ' . mb_substr($text, 0, 300))], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP);
        });
    }

    private function exclusive_names(array $entry)
    {
        if (!$entry['exclusive_group']) {
            return [];
        }
        $names = [];
        foreach (sc_addons_registry() as $other) {
            if ($other['id'] !== $entry['id'] && $other['exclusive_group'] === $entry['exclusive_group']) {
                $names[] = $other['name'];
            }
        }

        return $names;
    }

    private function cards()
    {
        $cards = [];
        foreach (sc_addons_registry(true) as $entry) {
            $cards[] = sc_addons_card($entry);
        }

        return $cards;
    }

    private function module_rows()
    {
        $rows = [];
        foreach ($this->app_modules->get() as $m) {
            $name = $m['system_name'];
            $desc   = sc_addons_clean_description($m['headers']['description'] ?? '');
            $rows[] = [
                'name' => $name, 'title' => $m['headers']['module_name'] ?? $name,
                'description' => $desc['short'], 'description_full' => $desc['full'],
                'version' => $m['headers']['version'] ?? '', 'author' => $m['headers']['author'] ?? '',
                'active' => (int) $m['activated'] === 1, 'installed' => $m['installed_version'] !== false,
                'protected' => sc_addons_is_protected_module($name),
            ];
        }

        return $rows;
    }

    private function form_fields(array $entry)
    {
        $out = [];
        foreach ($entry['fields'] as $f) {
            $secret = in_array($f['type'], ['password', 'secret_textarea'], true) || $f['encrypted'];
            $row    = ['option' => $f['option'], 'label' => $f['label'], 'type' => $f['type'], 'required' => (bool) $f['required'],
                'recommended' => !empty($f['recommended']),
                'help' => $f['help'], 'options' => $f['options'] ?? [], 'secret' => $secret, 'has_value' => sc_addons_option_filled($f['option'])];
            if (!$secret) {
                $row['value'] = (string) get_option($f['option']);
            }
            $out[] = $row;
        }

        return $out;
    }

    private function write_option($name, $value)
    {
        update_option($name, $value);
        $this->written[$name] = $value;
    }

    private function scg_encrypt($raw)
    {
        if (function_exists('sc_google_secret_encrypt')) {
            return sc_google_secret_encrypt($raw);
        }

        $enc = $this->encryption->encrypt($raw);

        return is_string($enc) && $enc !== '' ? 'scg1:' . $enc : false;
    }

    private function set_enabled(array $entry, $on)
    {
        if ($entry['active_option'] === null) {
            return;
        }
        $opts = (array) $entry['active_option'];
        if ($on) {
            $already = false;
            foreach ($opts as $o) {
                $already = $already || (string) get_option($o) === (string) $entry['active_on'];
            }
            if (!$already) {
                foreach ($opts as $o) {
                    $this->write_option($o, $entry['active_on']);
                }
            }
            if ($entry['exclusive_group']) {
                foreach (sc_addons_registry() as $other) {
                    if ($other['id'] !== $entry['id'] && $other['exclusive_group'] === $entry['exclusive_group']) {
                        foreach ((array) $other['active_option'] as $o) {
                            $this->write_option($o, $other['active_off']);
                        }
                    }
                }
            }
        } else {
            foreach ($opts as $o) {
                $this->write_option($o, $entry['active_off']);
            }
        }
    }

    /** Exclusive-group side effect of Connect for entries whose switch lives in the form fields. */
    private function set_enabled_exclusive(array $entry)
    {
        if (!$entry['exclusive_group']) {
            return;
        }
        foreach (sc_addons_registry() as $other) {
            if ($other['id'] !== $entry['id'] && $other['exclusive_group'] === $entry['exclusive_group']) {
                foreach ((array) $other['active_option'] as $o) {
                    $this->write_option($o, $other['active_off']);
                }
            }
        }
    }

    private function entry($id)
    {
        $entry = is_string($id) ? sc_addons_get($id) : null;
        if (!$entry) {
            $this->json(['success' => false, 'message' => sc_addons_t('sc_addons_unknown')], 404);
        }

        return $entry;
    }

    private function post_entry($id)
    {
        if (!$this->require_post()) {
            return null;
        }

        return $this->entry($id);
    }

    private function post_module($name)
    {
        if (!$this->require_post()) {
            return null;
        }
        $m = is_string($name) && preg_match('/^[A-Za-z0-9_. -]+$/', $name) && strpos($name, '..') === false ? $this->app_modules->get($name) : null;
        if (!$m) {
            $this->json(['success' => false, 'message' => sc_addons_t('sc_addons_unknown')], 404);

            return null;
        }

        return $m;
    }

    private function require_post()
    {
        if ($this->input->method() !== 'post') {
            $this->json(['success' => false, 'message' => 'Method not allowed'], 405);

            return false;
        }
        // Core turns CSRF off for any URI containing "gateways/" (config.php), so check the token here too,
        // and accept only same-origin AJAX requests.
        $name   = (string) $this->config->item('csrf_token_name');
        $cookie = (string) $this->input->cookie($this->config->item('cookie_prefix') . $this->config->item('csrf_cookie_name'), false);
        $token  = $this->input->post($name, false);
        $site   = strtolower((string) $this->input->server('HTTP_SEC_FETCH_SITE'));
        $origin = (string) $this->input->server('HTTP_ORIGIN');
        $own    = parse_url(base_url(), PHP_URL_SCHEME) . '://' . parse_url(base_url(), PHP_URL_HOST) . (parse_url(base_url(), PHP_URL_PORT) ? ':' . parse_url(base_url(), PHP_URL_PORT) : '');
        // When core CSRF ran, it already verified (and removed) the token; when it was switched off, verify it here.
        $tokenOk = $this->config->item('csrf_protection') ? true : ($cookie !== '' && is_string($token) && hash_equals($cookie, $token));
        if (!$this->input->is_ajax_request() || !$tokenOk
            || ($site !== '' && $site !== 'same-origin') || ($origin !== '' && strcasecmp(rtrim($origin, '/'), $own) !== 0)) {
            $this->json(['success' => false, 'message' => sc_addons_l('sc_addons_err_request', 'Request refused. Reload the page and try again.')], 403);

            return false;
        }

        return true;
    }

    private function json($data, $status = 200)
    {
        $this->output->set_status_header($status)->set_header('Cache-Control: no-store')->set_header('X-Content-Type-Options: nosniff')
            ->set_content_type('application/json', 'utf-8')->set_output(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP));
    }
}
