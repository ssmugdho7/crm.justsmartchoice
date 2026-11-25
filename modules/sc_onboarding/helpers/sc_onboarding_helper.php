<?php

defined('BASEPATH') or exit('No direct script access allowed');

/** Idempotent: creates the per-staff state table when it is missing (activation and first save). */
function sc_onb_ensure_table($force = false)
{
    static $done = false;
    if ($done && !$force) {
        return;
    }
    $CI    = &get_instance();
    $table = db_prefix() . 'sc_onboarding_state';
    if ($force || !$CI->db->table_exists($table)) {
        $CI->db->query('CREATE TABLE IF NOT EXISTS `' . $table . '` (
            `staff_id` INT(11) NOT NULL,
            `state` TEXT NULL,
            `updated_at` DATETIME NULL,
            PRIMARY KEY (`staff_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
    }
    $done = true;
}

/**
 * Same shape as BuildCRM's OnboardingController::normalize(), plus checklist_hidden. Older versions stored the
 * hidden checklist as dismissed: ['checklist']; that entry is moved to checklist_hidden.
 */
function sc_onb_normalize($state)
{
    $state = is_array($state) ? $state : [];
    $list  = function ($v) {
        if (!is_array($v)) {
            return [];
        }
        $out = [];
        foreach ($v as $id) {
            if (is_scalar($id)) {
                $id = (string) $id;
                if (sc_onb_valid_id($id) && !in_array($id, $out, true)) {
                    $out[] = $id;
                }
            }
        }
        return array_values(array_slice($out, -50));
    };
    $dismissed = $list($state['dismissed'] ?? []);
    $legacy    = array_search('checklist', $dismissed, true);
    if ($legacy !== false) {
        unset($dismissed[$legacy]);
        $dismissed = array_values($dismissed);
    }
    return [
        'completed'        => $list($state['completed'] ?? []),
        'dismissed'        => $dismissed,
        'welcome_seen'     => !empty($state['welcome_seen']),
        'checklist_hidden' => !empty($state['checklist_hidden']) || $legacy !== false,
    ];
}

function sc_onb_valid_id($id)
{
    return is_string($id) && strlen($id) <= 60 && preg_match('/^[a-z0-9_-]+$/', $id) === 1;
}

function sc_onb_get_state($staffId)
{
    $staffId = (int) $staffId;
    if ($staffId <= 0) {
        return sc_onb_normalize([]);
    }
    // Runs on every admin page, so no table_exists() round trip: the table is created on activation and on the
    // first save. If it is missing anyway, the read fails quietly and the default state is used.
    $db    = get_instance()->db;
    $debug = $db->db_debug;
    $db->db_debug = false;
    $row = null;
    try {
        $query = $db->select('state')->where('staff_id', $staffId)->get(db_prefix() . 'sc_onboarding_state');
        $row   = $query ? $query->row() : null;
    } catch (Throwable $e) {
        $db->reset_query();
        $row = null;
    }
    $db->db_debug = $debug;
    $data = $row ? json_decode((string) $row->state, true) : [];
    return sc_onb_normalize(is_array($data) ? $data : []);
}

function sc_onb_put_state($staffId, array $state)
{
    $staffId = (int) $staffId;
    $state   = sc_onb_normalize($state);
    sc_onb_ensure_table();
    $CI    = &get_instance();
    $table = db_prefix() . 'sc_onboarding_state';
    $json  = json_encode($state, JSON_UNESCAPED_UNICODE);
    // Upsert keyed by the staff primary key.
    $CI->db->query(
        'INSERT INTO `' . $table . '` (`staff_id`, `state`, `updated_at`) VALUES (?, ?, ?)
         ON DUPLICATE KEY UPDATE `state` = VALUES(`state`), `updated_at` = VALUES(`updated_at`)',
        [$staffId, $json, date('Y-m-d H:i:s')]
    );
    return $state;
}

/**
 * Validates a delta patch: completed/dismissed arrays (max 50) of ids matching /^[a-z0-9_-]+$/
 * (max 60 chars), welcome_seen boolean, checklist_hidden boolean, reset boolean. Unknown keys are ignored.
 * Returns [patch, error|null].
 */
function sc_onb_validate_patch($input)
{
    if (!is_array($input)) {
        return [null, 'invalid'];
    }
    $bool = function ($v, &$ok) {
        $ok = true;
        if (is_bool($v)) {
            return $v;
        }
        if (in_array($v, [1, 0, '1', '0', 'true', 'false'], true)) {
            return in_array($v, [1, '1', 'true'], true);
        }
        $ok = false;
        return false;
    };
    $patch = [];
    foreach (['completed', 'dismissed'] as $key) {
        if (!array_key_exists($key, $input)) {
            continue;
        }
        $value = $input[$key];
        if ($value === '' || $value === null) {
            $value = [];
        }
        if (!is_array($value) || count($value) > 50) {
            return [null, $key];
        }
        foreach ($value as $id) {
            if (!is_string($id) || !sc_onb_valid_id($id)) {
                return [null, $key];
            }
        }
        $patch[$key] = array_values($value);
    }
    foreach (['welcome_seen', 'checklist_hidden', 'reset'] as $key) {
        if (array_key_exists($key, $input)) {
            $patch[$key] = $bool($input[$key], $ok);
            if (!$ok) {
                return [null, $key];
            }
        }
    }
    return [$patch, null];
}

/**
 * Merges a validated delta into the stored state. completed/dismissed are unioned (so two open tabs
 * never wipe each other's progress); welcome_seen only ever turns on; checklist_hidden is set to the value
 * sent (Help page "Show the Getting started checklist" sends false). `reset: true` clears everything
 * first (Help page "Reset all tours"). Lists keep the newest 50 ids.
 */
function sc_onb_merge_state(array $stored, array $patch)
{
    $stored = sc_onb_normalize($stored);
    if (!empty($patch['reset'])) {
        $stored = ['completed' => [], 'dismissed' => [], 'welcome_seen' => false, 'checklist_hidden' => false];
    }
    foreach (['completed', 'dismissed'] as $key) {
        $list = $stored[$key];
        foreach ($patch[$key] ?? [] as $id) {
            if (sc_onb_valid_id($id) && !in_array($id, $list, true)) {
                $list[] = $id;
            }
        }
        $stored[$key] = array_values(array_slice($list, -50));
    }
    if (!empty($patch['welcome_seen'])) {
        $stored['welcome_seen'] = true;
    }
    if (array_key_exists('checklist_hidden', $patch) && is_bool($patch['checklist_hidden'])) {
        $stored['checklist_hidden'] = $patch['checklist_hidden'];
    }
    return $stored;
}

/** Admin language of the current staff member, reduced to 'spanish' or 'english' for the tour copy. */
function sc_onb_language()
{
    $lang = isset($GLOBALS['language']) ? (string) $GLOBALS['language'] : '';
    if ($lang === '' && function_exists('get_staff_default_language')) {
        $lang = (string) get_staff_default_language();
    }
    if ($lang === '') {
        $lang = (string) get_option('active_language');
    }
    return stripos($lang, 'spanish') === 0 ? 'spanish' : 'english';
}

/**
 * Which areas the current staff member can see; tours/steps declare a matching `perm`.
 * Mirrors the sidebar rules in application/helpers/menu_helper.php so a tour is offered exactly when its menu is.
 */
function sc_onb_perms()
{
    $can = function ($feature) {
        return staff_can('view', $feature) || staff_can('view_own', $feature);
    };
    // Proposals/estimates/invoices: also visible when assigned and the matching "allow staff to view assigned" option is on.
    $assigned = function ($fn, $option) {
        return function_exists($fn) && $fn() && ($option === null || get_option($option) == 1);
    };
    $staffMember = is_staff_member();
    return [
        'admin'     => is_admin(),
        'projects'  => true, // Projects menu is shown to all staff; membership rules apply inside.
        'tasks'     => true,
        'invoices'  => $can('invoices') || $assigned('staff_has_assigned_invoices', 'allow_staff_view_invoices_assigned'),
        'estimates' => $can('estimates') || $assigned('staff_has_assigned_estimates', 'allow_staff_view_estimates_assigned'),
        'proposals' => $can('proposals') || $assigned('staff_has_assigned_proposals', 'allow_staff_view_proposals_assigned'),
        'payments'  => staff_can('view', 'payments') || staff_can('view_own', 'invoices')
            || $assigned('staff_has_assigned_invoices', 'allow_staff_view_invoices_assigned'),
        'expenses'  => $can('expenses'),
        'contracts' => $can('contracts'),
        // menu_helper.php: view, or assigned customers, or create.
        'customers' => staff_can('view', 'customers') || staff_can('create', 'customers')
            || $assigned('have_assigned_customers', null),
        'leads'     => $staffMember,
        'tickets'   => $staffMember || get_option('access_tickets_to_none_staff_members') == 1,
        'staff'     => staff_can('view', 'staff'),
        'reports'   => staff_can('view', 'reports'),
        'settings'  => staff_can('view', 'settings'),
        'knowledge_base' => staff_can('view', 'knowledge_base'),
    ];
}

/** UI strings for the JavaScript (English/Spanish come from the module language files). */
function sc_onb_js_strings()
{
    $keys = [
        'step_of', 'next', 'back', 'finish', 'skip_tour', 'skip_step', 'close_tour', 'try_it', 'tour_complete',
        'quick_tour', 'welcome_title', 'welcome_hi', 'welcome_intro', 'welcome_cta_text', 'welcome_tour',
        'welcome_explore', 'flow_lead', 'flow_lead_d', 'flow_quote', 'flow_quote_d', 'flow_project', 'flow_project_d',
        'flow_tasks', 'flow_tasks_d', 'flow_invoice', 'flow_invoice_d', 'flow_paid', 'flow_paid_d',
        'checklist_title', 'checklist_done_of', 'checklist_dismiss', 'checklist_collapse', 'checklist_expand',
        'checklist_all', 'help_kicker', 'help_title', 'help_lead', 'help_of_tours', 'help_msg_none', 'help_msg_all',
        'help_msg_start', 'help_msg_progress', 'help_continue', 'help_start', 'help_reset', 'help_reset_confirm',
        'help_reset_done', 'help_search', 'help_search_empty', 'help_done', 'help_not_started', 'help_minutes',
        'help_steps', 'help_replay', 'help_start_btn', 'help_group_done', 'help_flow_title', 'help_no_tours',
        'help_no_tours_hint', 'help_load_error', 'welcome_hi_anon', 'loading', 'group_basics', 'group_sell', 'group_run_jobs', 'group_customers',
        'group_support', 'group_team', 'group_basics_hint', 'group_sell_hint', 'group_run_jobs_hint',
        'group_customers_hint', 'group_support_hint', 'group_team_hint', 'help_shortcuts', 'help_ring_label',
        'help_show_checklist', 'help_checklist_shown',
    ];
    $out = [];
    foreach ($keys as $key) {
        $out[$key] = _l('sc_onb_js_' . $key, '', false);
    }
    return $out;
}
