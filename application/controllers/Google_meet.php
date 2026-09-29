<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Customer-safe Google Meet fallback.
 *
 * The full Google Meet module may override this route when installed. This
 * controller prevents a client-facing HTTP 500 when the module source is not
 * present or its customer controller cannot be loaded.
 */
class Google_meet extends ClientsController
{
    public function index()
    {
        $this->client();
    }

    public function client()
    {
        if (!is_client_logged_in()) {
            redirect(site_url('authentication/login'));
        }

        $data = [
            'title'    => 'Google Meet',
            'meetings' => [],
        ];

        foreach ([db_prefix() . 'google_meet_meetings', db_prefix() . 'google_meetings'] as $table) {
            if (!$this->db->table_exists($table)) {
                continue;
            }
            $fields = $this->db->list_fields($table);
            if (in_array('client_id', $fields, true)) {
                $this->db->where('client_id', get_client_user_id());
            } elseif (in_array('customer_id', $fields, true)) {
                $this->db->where('customer_id', get_client_user_id());
            } elseif (in_array('contact_id', $fields, true)) {
                $this->db->where('contact_id', get_contact_user_id());
            }
            foreach (['start_time', 'start_date', 'meeting_date', 'id'] as $orderField) {
                if (in_array($orderField, $fields, true)) {
                    $this->db->order_by($orderField, 'DESC');
                    break;
                }
            }
            $data['meetings'] = $this->db->get($table)->result_array();
            break;
        }

        $this->data($data);
        $this->view('google_meet_client');
        $this->layout();
    }
}
