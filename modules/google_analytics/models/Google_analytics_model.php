<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Google_analytics_model extends App_Model
{
    public function get_websites($activeOnly = false)
    {
        if ($activeOnly) $this->db->where('active', 1);
        return $this->db->order_by('is_default', 'DESC')->order_by('name', 'ASC')->get(db_prefix() . 'ga_websites')->result();
    }

    public function get_website($id)
    {
        return $this->db->where('id', (int) $id)->get(db_prefix() . 'ga_websites')->row();
    }

    public function save_website($data, $id = 0)
    {
        $record = [
            'name'           => trim((string) ($data['name'] ?? '')),
            'website_url'    => ga_normalize_url($data['website_url'] ?? ''),
            'measurement_id' => strtoupper(trim((string) ($data['measurement_id'] ?? ''))),
            'property_id'    => trim((string) ($data['property_id'] ?? '')) ?: null,
            'stream_id'      => trim((string) ($data['stream_id'] ?? '')) ?: null,
            'timezone'       => trim((string) ($data['timezone'] ?? 'America/New_York')),
            'active'         => !empty($data['active']) ? 1 : 0,
            'is_default'     => !empty($data['is_default']) ? 1 : 0,
            'date_updated'   => date('Y-m-d H:i:s'),
        ];
        if (!empty($data['api_secret'])) $record['api_secret'] = ga_encrypt_secret($data['api_secret']);
        if ($record['is_default']) $this->db->update(db_prefix() . 'ga_websites', ['is_default' => 0]);
        if ($id > 0) {
            $this->db->where('id', $id)->update(db_prefix() . 'ga_websites', $record);
            return $id;
        }
        $record['created_by'] = get_staff_user_id();
        $record['date_created'] = date('Y-m-d H:i:s');
        $this->db->insert(db_prefix() . 'ga_websites', $record);
        return $this->db->insert_id();
    }

    public function delete_website($id)
    {
        $this->db->where('website_id', (int) $id)->delete(db_prefix() . 'ga_report_cache');
        return $this->db->where('id', (int) $id)->delete(db_prefix() . 'ga_websites');
    }

    public function clear_cache($websiteId = null)
    {
        if ($websiteId !== null) $this->db->where('website_id', (int) $websiteId);
        return $this->db->delete(db_prefix() . 'ga_report_cache');
    }

    public function get_cached($key)
    {
        $row = $this->db->where('cache_key', $key)->where('expires_at >=', date('Y-m-d H:i:s'))->get(db_prefix() . 'ga_report_cache')->row();
        return $row ? json_decode($row->payload, true) : null;
    }

    public function set_cached($websiteId, $key, $type, array $payload)
    {
        $minutes = max(5, min(1440, (int) get_option('ga_report_cache_minutes')));
        $record = [
            'website_id' => $websiteId ?: null,
            'cache_key'   => $key,
            'report_type' => $type,
            'payload'     => json_encode($payload),
            'date_created'=> date('Y-m-d H:i:s'),
            'expires_at'  => date('Y-m-d H:i:s', time() + ($minutes * 60)),
        ];
        $existing = $this->db->where('cache_key', $key)->get(db_prefix() . 'ga_report_cache')->row();
        if ($existing) return $this->db->where('id', $existing->id)->update(db_prefix() . 'ga_report_cache', $record);
        return $this->db->insert(db_prefix() . 'ga_report_cache', $record);
    }

    public function get_access_token()
    {
        $json = ga_decrypt_secret(get_option('ga_service_account_json'));
        $service = json_decode($json, true);
        if (!is_array($service) || empty($service['client_email']) || empty($service['private_key']) || empty($service['token_uri'])) {
            throw new RuntimeException('A valid Google service-account JSON credential is required.');
        }
        $now = time();
        $header = $this->base64url(json_encode(['alg'=>'RS256','typ'=>'JWT']));
        $claims = $this->base64url(json_encode([
            'iss'   => $service['client_email'],
            'scope' => 'https://www.googleapis.com/auth/analytics.readonly',
            'aud'   => $service['token_uri'],
            'iat'   => $now,
            'exp'   => $now + 3600,
        ]));
        $input = $header . '.' . $claims;
        $signature = '';
        if (!openssl_sign($input, $signature, $service['private_key'], OPENSSL_ALGO_SHA256)) {
            throw new RuntimeException('Unable to sign the Google service-account request.');
        }
        $jwt = $input . '.' . $this->base64url($signature);
        $response = $this->http($service['token_uri'], [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion'  => $jwt,
        ], ['Content-Type: application/x-www-form-urlencoded'], false);
        if (empty($response['access_token'])) throw new RuntimeException($response['error_description'] ?? 'Google access token request failed.');
        return $response['access_token'];
    }

    public function run_report($propertyId, array $body, $realtime = false)
    {
        if (!ga_valid_property_id($propertyId)) throw new InvalidArgumentException('Invalid GA4 property ID.');
        $method = $realtime ? 'runRealtimeReport' : 'runReport';
        $url = 'https://analyticsdata.googleapis.com/v1beta/properties/' . rawurlencode($propertyId) . ':' . $method;
        return $this->http($url, json_encode($body), [
            'Authorization: Bearer ' . $this->get_access_token(),
            'Content-Type: application/json',
        ], true);
    }

    public function dashboard_report($website, $startDate = '30daysAgo', $endDate = 'today', $force = false)
    {
        $key = sha1('dashboard|' . $website->id . '|' . $startDate . '|' . $endDate);
        if (!$force && ($cached = $this->get_cached($key))) return $cached;
        $summary = $this->run_report($website->property_id, [
            'dateRanges' => [['startDate'=>$startDate,'endDate'=>$endDate]],
            'metrics' => [
                ['name'=>'activeUsers'], ['name'=>'sessions'], ['name'=>'engagedSessions'],
                ['name'=>'screenPageViews'], ['name'=>'eventCount'], ['name'=>'keyEvents'],
            ],
        ]);
        $pages = $this->run_report($website->property_id, [
            'dateRanges' => [['startDate'=>$startDate,'endDate'=>$endDate]],
            'dimensions' => [['name'=>'pageTitle'],['name'=>'pagePath']],
            'metrics' => [['name'=>'screenPageViews'],['name'=>'activeUsers'],['name'=>'averageSessionDuration']],
            'orderBys' => [['metric'=>['metricName'=>'screenPageViews'],'desc'=>true]],
            'limit' => 10,
        ]);
        $sources = $this->run_report($website->property_id, [
            'dateRanges' => [['startDate'=>$startDate,'endDate'=>$endDate]],
            'dimensions' => [['name'=>'sessionSource'],['name'=>'sessionMedium']],
            'metrics' => [['name'=>'sessions'],['name'=>'activeUsers'],['name'=>'keyEvents']],
            'orderBys' => [['metric'=>['metricName'=>'sessions'],'desc'=>true]],
            'limit' => 10,
        ]);
        $result = ['summary'=>$summary,'pages'=>$pages,'sources'=>$sources,'generated_at'=>date('c')];
        $this->set_cached($website->id, $key, 'dashboard', $result);
        return $result;
    }

    public function realtime_report($website)
    {
        return $this->run_report($website->property_id, [
            'dimensions' => [['name'=>'unifiedScreenName'],['name'=>'country']],
            'metrics' => [['name'=>'activeUsers'],['name'=>'eventCount']],
            'limit' => 20,
        ], true);
    }

    private function http($url, $data, array $headers, $json)
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        $raw = curl_exec($ch);
        $error = curl_error($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($raw === false) throw new RuntimeException('Google API connection failed: ' . $error);
        $decoded = json_decode($raw, true);
        if ($status < 200 || $status >= 300) {
            $message = $decoded['error']['message'] ?? $decoded['error_description'] ?? ('Google API returned HTTP ' . $status);
            throw new RuntimeException($message);
        }
        return is_array($decoded) ? $decoded : [];
    }

    private function base64url($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
