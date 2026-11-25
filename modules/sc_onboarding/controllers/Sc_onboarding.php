<?php

defined('BASEPATH') or exit('No direct script access allowed');

/** Help & Tours page and per-staff tour progress. Any logged-in staff member; own state only. */
class Sc_onboarding extends AdminController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        redirect(admin_url('sc_onboarding/help'));
    }

    public function help()
    {
        $data['title'] = _l('sc_onb_help_title');
        $this->load->view('help', $data);
    }

    /** GET: {completed: [], dismissed: [], welcome_seen: bool} */
    public function state()
    {
        $this->json(sc_onb_get_state(get_staff_user_id()));
    }

    /**
     * POST (normal Perfex CSRF field). Accepts a delta either as a JSON string in `state` or as plain
     * fields (completed[], dismissed[], welcome_seen, checklist_hidden, reset). completed/dismissed are
     * unioned with the stored lists; reset=true clears everything first. Returns the merged state.
     */
    public function save_state()
    {
        if ($this->input->method() !== 'post') {
            $this->json(['error' => 'method_not_allowed'], 405);
            return;
        }
        $raw = $this->input->post('state');
        if (is_string($raw) && $raw !== '') {
            $input = json_decode($raw, true);
            if (!is_array($input)) {
                $this->json(['error' => 'invalid_json'], 422);
                return;
            }
        } else {
            $input = [];
            foreach (['completed', 'dismissed', 'welcome_seen', 'checklist_hidden', 'reset'] as $key) {
                $value = $this->input->post($key);
                if ($value !== null) {
                    $input[$key] = $value;
                }
            }
        }

        [$patch, $error] = sc_onb_validate_patch($input);
        if ($error !== null) {
            $this->json(['error' => 'invalid', 'field' => $error], 422);
            return;
        }
        $staffId = get_staff_user_id();
        $state   = sc_onb_put_state($staffId, sc_onb_merge_state(sc_onb_get_state($staffId), $patch));
        $this->json($state);
    }

    private function json($data, $status = 200)
    {
        $this->output
            ->set_status_header($status)
            ->set_header('Cache-Control: no-store')
            ->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode($data, JSON_UNESCAPED_UNICODE));
    }
}
