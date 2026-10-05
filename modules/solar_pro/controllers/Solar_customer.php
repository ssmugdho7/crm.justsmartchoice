<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Solar_customer extends ClientsController
{
    public function index()
    {
        if (!is_client_logged_in()) {
            redirect(site_url('authentication/login'));
            return;
        }

        // The parent constructor validates and loads the current contact.
        $contact = $GLOBALS['contact'] ?? null;
        $data = ['title' => _l('solar_pro_my_solar'), 'analyses' => []];
        if ($contact) {
            $this->db->group_start()->where('client_id', (int) $contact->userid);
            if (!empty($contact->email)) {
                $this->db->or_where('email', $contact->email);
            }
            $data['analyses'] = $this->db->group_end()->order_by('id', 'DESC')
                ->get(db_prefix() . 'solar_analyses')->result_array();
        }

        $this->data($data);
        $this->view('public/my');
        $this->layout();
    }
}
