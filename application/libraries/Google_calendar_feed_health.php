<?php
defined('BASEPATH') or exit('No direct script access allowed');

/** Check public calendar access before handing a feed to FullCalendar's browser loader. */
class Google_calendar_feed_health
{
    private $cacheDriver;

    public function check(array $ids, string $apiKey): array
    {
        $ids = array_values(array_unique(array_filter(array_map('trim', $ids))));
        if (!$ids) { return ['ids'=>[], 'unavailable'=>0]; }
        if ($apiKey === '') { return ['ids'=>[], 'unavailable'=>count($ids)]; }

        $statuses = [];
        $pending = [];
        foreach ($ids as $id) {
            $key = 'google_calendar_health_' . hash('sha256', $apiKey . "\0" . $id);
            $status = $this->cachedStatus($key);
            if ($status === false) { $pending[$id] = $key; }
            else { $statuses[$id] = (int) $status; }
        }
        foreach ($this->probe(array_keys($pending), $apiKey) as $id=>$status) {
            $statuses[$id] = $status;
            $this->saveStatus($pending[$id], $status, $status === 0 || $status >= 500 ? 60 : 300);
        }
        $available = array_values(array_filter($ids, function ($id) use ($statuses) {
            return ($statuses[$id] ?? 0) === 200;
        }));
        return ['ids'=>$available, 'unavailable'=>count($ids)-count($available)];
    }

    protected function cachedStatus(string $key)
    {
        return $this->cache()->get($key);
    }

    protected function saveStatus(string $key, int $status, int $ttl): void
    {
        $this->cache()->save($key, $status, $ttl);
    }

    private function cache()
    {
        if (!$this->cacheDriver) {
            $CI = &get_instance();
            $CI->load->driver('cache');
            $this->cacheDriver = $CI->cache->file;
        }
        return $this->cacheDriver;
    }

    protected function probe(array $ids, string $apiKey): array
    {
        if (!$ids) { return []; }
        if (!function_exists('curl_multi_init')) { return array_fill_keys($ids, 0); }
        $multi = curl_multi_init();
        $handles = [];
        foreach ($ids as $id) {
            $url = 'https://www.googleapis.com/calendar/v3/calendars/' . rawurlencode($id) . '/events?'
                . http_build_query(['key'=>$apiKey, 'maxResults'=>1, 'fields'=>'kind']);
            $handle = curl_init($url);
            curl_setopt_array($handle, [CURLOPT_RETURNTRANSFER=>true, CURLOPT_FOLLOWLOCATION=>false,
                CURLOPT_CONNECTTIMEOUT=>2, CURLOPT_TIMEOUT=>4, CURLOPT_PROTOCOLS=>CURLPROTO_HTTPS,
                CURLOPT_SSL_VERIFYPEER=>true, CURLOPT_SSL_VERIFYHOST=>2, CURLOPT_REFERER=>site_url(),
                // Only status is needed. Do not retain calendar contents or log credential-bearing URLs.
                CURLOPT_WRITEFUNCTION=>static function ($curl, $chunk) { return strlen($chunk); }]);
            curl_multi_add_handle($multi, $handle);
            $handles[$id] = $handle;
        }
        do {
            $result = curl_multi_exec($multi, $running);
            if ($running) { curl_multi_select($multi, 0.25); }
        } while ($running && $result === CURLM_OK);
        $statuses = [];
        foreach ($handles as $id=>$handle) {
            $statuses[$id] = curl_errno($handle) ? 0 : (int) curl_getinfo($handle, CURLINFO_HTTP_CODE);
            curl_multi_remove_handle($multi, $handle);
            curl_close($handle);
        }
        curl_multi_close($multi);
        return $statuses;
    }
}
