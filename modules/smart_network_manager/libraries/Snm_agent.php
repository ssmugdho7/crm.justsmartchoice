<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Snm_agent
{
    public function request($endpoint, $payload = [])
    {
        $base = trim((string)get_option('smart_network_manager_agent_url'));
        $token = trim((string)get_option('smart_network_manager_agent_token'));
        if ($base === '') { return ['success'=>false, 'message'=>'Windows Agent URL is not configured.']; }
        if ($token === '') { return ['success'=>false, 'message'=>'Windows Agent token is not configured.']; }
        $url = rtrim($base, '/') . '/' . ltrim($endpoint, '/');
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json','X-SNM-Token: '.$token],
            CURLOPT_POSTFIELDS => json_encode($payload),
        ]);
        $response = curl_exec($ch);
        $error = curl_error($ch);
        if (PHP_VERSION_ID < 80500) { curl_close($ch); }
        if ($response === false) { return ['success'=>false, 'message'=>'Agent request failed: '.$error]; }
        $json = json_decode($response, true);
        if (!is_array($json)) { return ['success'=>false, 'message'=>'Invalid agent response.', 'raw'=>$response]; }
        return $json;
    }
}
