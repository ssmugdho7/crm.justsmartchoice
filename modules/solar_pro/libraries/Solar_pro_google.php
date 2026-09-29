<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Solar_pro_google
{
    private $CI;

    public function __construct()
    {
        $this->CI = &get_instance();
        $this->CI->load->helper('solar_pro/solar_pro');
    }

    public function geocode($address)
    {
        $key = solar_pro_setting('solar_pro_google_geocoding_api_key', solar_pro_setting('solar_pro_google_api_key', ''));
        if (!$key) {
            return ['success' => false, 'error' => 'missing_key'];
        }
        $url = 'https://maps.googleapis.com/maps/api/geocode/json?address=' . rawurlencode($address) . '&key=' . rawurlencode($key);
        $result = $this->request($url, 'google_geocoding');
        if (!$result['success'] || empty($result['data']['results'][0]['geometry']['location'])) {
            return ['success' => false, 'error' => $result['error'] ?? 'not_found'];
        }
        $location = $result['data']['results'][0]['geometry']['location'];
        return [
            'success' => true,
            'latitude' => (float) $location['lat'],
            'longitude' => (float) $location['lng'],
            'formatted_address' => $result['data']['results'][0]['formatted_address'] ?? $address,
        ];
    }

    public function buildingInsights($latitude, $longitude)
    {
        $key = solar_pro_setting('solar_pro_google_api_key', '');
        if (!$key) {
            return ['success' => false, 'error' => 'missing_key'];
        }
        $quality = solar_pro_setting('solar_pro_google_required_quality', 'BASE');
        if (!in_array($quality, ['BASE', 'MEDIUM', 'HIGH'], true)) {
            $quality = 'BASE';
        }
        $url = 'https://solar.googleapis.com/v1/buildingInsights:findClosest?location.latitude=' . rawurlencode((string) $latitude)
            . '&location.longitude=' . rawurlencode((string) $longitude)
            . '&requiredQuality=' . rawurlencode($quality)
            . '&key=' . rawurlencode($key);
        return $this->request($url, 'google_solar');
    }

    private function request($url, $provider)
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 8,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_HTTPHEADER => ['Accept: application/json'],
        ]);
        $body = curl_exec($ch);
        $errno = curl_errno($ch);
        $error = curl_error($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $data = json_decode((string) $body, true);
        $success = $errno === 0 && $status >= 200 && $status < 300 && is_array($data);

        $this->CI->db->insert(db_prefix() . 'solar_api_logs', [
            'analysis_id' => null,
            'provider' => $provider,
            'endpoint' => strtok($url, '?'),
            'http_status' => $status,
            'success' => $success ? 1 : 0,
            'request_summary' => preg_replace('/key=[^&]+/', 'key=REDACTED', $url),
            'response_summary' => substr((string) $body, 0, 10000),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return $success
            ? ['success' => true, 'data' => $data, 'status' => $status]
            : ['success' => false, 'error' => $error ?: ($data['error']['message'] ?? 'HTTP ' . $status), 'status' => $status, 'data' => $data];
    }
}
