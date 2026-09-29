<?php defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Call the Cloudflare Calls TURN API to generate short-lived credentials.
 *
 * @param  string $tokenId   Cloudflare Turn Token ID
 * @param  string $apiToken  Cloudflare API Token
 * @return array|null        iceServers array on success, null on failure
 */
function prchat_cloudflare_turn_credentials($tokenId, $apiToken)
{
  $endpoint = 'https://rtc.live.cloudflare.com/v1/turn/keys/'
            . urlencode($tokenId)
            . '/credentials/generate-ice-servers';

  $ch = curl_init($endpoint);
  curl_setopt($ch, CURLOPT_POST, true);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_TIMEOUT, 5);
  curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $apiToken,
    'Content-Type: application/json',
  ]);
  curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['ttl' => 86400]));

  $response = curl_exec($ch);
  $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);

  if ($httpCode < 200 || $httpCode >= 300 || empty($response)) {
    log_message('error', '[prchat] Cloudflare TURN API failed: HTTP ' . $httpCode);
    return null;
  }

  $data = json_decode($response, true);

  if (!empty($data['iceServers'])) {
    $servers = $data['iceServers'];

    // Cloudflare may return a single object or an array of objects.
    // Normalize: always return a sequential indexed array.
    if (isset($servers['urls'])) {
      $servers = [$servers];
    } else {
      $servers = array_values($servers);
    }

    return $servers;
  }

  log_message('error', '[prchat] Cloudflare TURN API: unexpected response structure');
  return null;
}
