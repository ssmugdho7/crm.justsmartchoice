<?php

defined('BASEPATH') or exit('No direct script access allowed');

/** Report-scoped proxy: credentials and provider URLs never reach the browser. */
class Solar_pro_layers
{
    private $CI;
    public function __construct() { $this->CI = &get_instance(); }

    public static function validRasterUrl($url)
    {
        $parts = parse_url((string) $url);
        return $parts && ($parts['scheme'] ?? '') === 'https'
            && ($parts['host'] ?? '') === 'solar.googleapis.com'
            && ($parts['path'] ?? '') === '/v1/geoTiff:get'
            && !isset($parts['user']) && !isset($parts['pass']) && !isset($parts['port']);
    }

    private function json($data, $status = 200)
    {
        return $this->CI->output->set_status_header($status)->set_content_type('application/json')
            ->set_header('Cache-Control: private, no-store')->set_output(json_encode($data));
    }

    public function serve(array $analysis, $layer)
    {
        $this->CI->output->set_header('Cache-Control: private, no-store');
        $lat = $analysis['latitude'] ?? null;
        $lng = $analysis['longitude'] ?? null;
        if (!is_numeric($lat) || !is_numeric($lng) || abs((float)$lat) > 90 || abs((float)$lng) > 180) {
            return $this->json(['error' => 'location_unavailable'], 422);
        }
        if (!preg_match('/^(manifest|rgb|mask|annual|shade-(?:[1-9]|1[0-2]))$/D', $layer)) {
            return $this->json(['error' => 'invalid_layer'], 400);
        }
        $sessionKey = 'solar_layers_' . (int)$analysis['id'];
        $manifest = $this->CI->session->userdata($sessionKey);
        if (!$manifest || ($manifest['expires'] ?? 0) < time()) {
            // Only an explicit user load starts a paid data-layers request.
            if ($layer !== 'manifest') return $this->json(['error' => 'reload_layers'], 410);
            $last = (int)$this->CI->session->userdata('solar_layers_last_request');
            if (time() - $last < 30) return $this->json(['error' => 'try_again_shortly'], 429);
            $this->CI->session->set_userdata('solar_layers_last_request', time());
            $response = $this->CI->solar_pro_google->dataLayers((float)$lat, (float)$lng);
            if (empty($response['success'])) return $this->json(['error' => 'imagery_unavailable'], 502);
            $data = $response['data'];
            $urls = ['rgb' => $data['rgbUrl'] ?? '', 'mask' => $data['maskUrl'] ?? '', 'annual' => $data['annualFluxUrl'] ?? ''];
            foreach (($data['hourlyShadeUrls'] ?? []) as $index => $url) {
                if ($index < 12) $urls['shade-' . ($index + 1)] = $url;
            }
            foreach ($urls as $url) {
                if (!self::validRasterUrl($url)) return $this->json(['error' => 'imagery_unavailable'], 502);
            }
            $manifest = ['expires' => time() + 3000, 'urls' => $urls,
                'quality' => $data['imageryQuality'] ?? '', 'date' => $data['imageryDate'] ?? []];
            $this->CI->session->set_userdata($sessionKey, $manifest);
        }
        if ($layer === 'manifest') return $this->json([
            'quality' => $manifest['quality'], 'date' => $manifest['date'],
            'shadeMonths' => array_values(array_map('intval', array_map(function ($key) { return substr($key, 6); },
                array_filter(array_keys($manifest['urls']), function ($key) { return strpos($key, 'shade-') === 0; })))),
        ]);
        $url = $manifest['urls'][$layer] ?? '';
        if (!self::validRasterUrl($url)) return $this->json(['error' => 'layer_unavailable'], 404);
        $key = solar_pro_setting('solar_pro_google_api_key', '');
        $body = '';
        $ch = curl_init($url . '&key=' . rawurlencode($key));
        curl_setopt_array($ch, [CURLOPT_FOLLOWLOCATION => false, CURLOPT_CONNECTTIMEOUT => 8,
            CURLOPT_TIMEOUT => 40, CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_WRITEFUNCTION => function ($ch, $chunk) use (&$body) {
                if (strlen($body) + strlen($chunk) > 24 * 1024 * 1024) return 0;
                $body .= $chunk; return strlen($chunk);
            }]);
        $ok = curl_exec($ch);
        $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($ok === false || $status !== 200 || !in_array(substr($body, 0, 4), ["II\x2a\x00", "MM\x00\x2a"], true)) {
            return $this->json(['error' => 'layer_unavailable'], 502);
        }
        return $this->CI->output->set_content_type('image/tiff')->set_header('X-Content-Type-Options: nosniff')->set_output($body);
    }
}
