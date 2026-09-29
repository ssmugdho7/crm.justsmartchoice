<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Client_tracking extends App_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('smart_installer_tracking/smart_installer_tracking_model');
        $this->load->helper('smart_installer_tracking/smart_installer_tracking');
    }

    public function view($token = ''): void
    {
        if (get_option('smart_installer_tracking_allow_client_live_map') !== '1') {
            show_404();
        }
        $data['trip'] = $this->smart_installer_tracking_model->get_trip_by_token((string) $token);
        if (!$data['trip']) {
            show_404();
        }
        $data['title'] = _l('smart_installer_tracking_client_tracking');
        $data['google_maps_api_key'] = get_option('smart_installer_tracking_google_maps_api_key');
        $this->load->view('client_tracking', $data);
    }

    public function data($token = ''): void
    {
        if (get_option('smart_installer_tracking_allow_client_live_map') !== '1') {
            show_404();
        }
        $trip = $this->smart_installer_tracking_model->get_trip_by_token((string) $token);
        if (!$trip) {
            show_404();
        }
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'installer_name' => trim((string) ($trip['firstname'] ?? '') . ' ' . (string) ($trip['lastname'] ?? '')),
            'status' => smart_installer_tracking_clean_text((string) $trip['status']),
            'lat' => $trip['last_lat'],
            'lng' => $trip['last_lng'],
            'eta_text' => $trip['eta_text'] ?: 'ETA pending',
            'distance_text' => $trip['distance_text'] ?: '',
            'last_location_at' => $trip['last_location_at'],
        ]);
    }
}
