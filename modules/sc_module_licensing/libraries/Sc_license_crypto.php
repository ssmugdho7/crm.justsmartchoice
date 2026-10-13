<?php
defined('BASEPATH') or exit('No direct script access allowed');

/** Signed leases contain no activation key. Only the owner server has the private key. */
class Sc_license_crypto
{
    public static function encode($bytes)
    {
        return rtrim(strtr(base64_encode($bytes), '+/', '-_'), '=');
    }

    public static function decode($text)
    {
        if (!is_string($text) || !preg_match('/^[A-Za-z0-9_-]+$/D', $text)) {
            throw new RuntimeException('scl_invalid');
        }
        $bytes = base64_decode(strtr($text, '-_', '+/'), true);
        if ($bytes === false) { throw new RuntimeException('scl_invalid'); }
        return $bytes;
    }

    public static function site($url)
    {
        $p = parse_url($url);
        if (!$p || ($p['scheme'] ?? '') !== 'https' || empty($p['host'])
            || isset($p['user']) || isset($p['pass']) || isset($p['query']) || isset($p['fragment'])
            || (isset($p['port']) && (int) $p['port'] !== 443)
            || !preg_match('/^[a-z0-9.-]+$/iD', $p['host'])) {
            throw new RuntimeException('scl_invalid_site');
        }
        return 'https://' . strtolower($p['host']) . rtrim($p['path'] ?? '', '/');
    }

    public static function sign(array $claims, $privateKey)
    {
        $body = self::encode(json_encode($claims, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
        if (!openssl_sign($body, $signature, $privateKey, OPENSSL_ALGO_SHA256)) {
            throw new RuntimeException('scl_unavailable');
        }
        return $body . '.' . self::encode($signature);
    }

    public static function verify($certificate, $publicKey, $module, $site, $installation, $now = null)
    {
        $now = $now === null ? time() : $now;
        if (!is_string($certificate) || strlen($certificate) > 12000 || substr_count($certificate, '.') !== 1) {
            throw new RuntimeException('scl_invalid');
        }
        list($body, $sig) = explode('.', $certificate);
        if (openssl_verify($body, self::decode($sig), $publicKey, OPENSSL_ALGO_SHA256) !== 1) {
            throw new RuntimeException('scl_invalid');
        }
        $c = json_decode(self::decode($body), true, 32, JSON_THROW_ON_ERROR);
        if (!is_array($c) || ($c['v'] ?? null) !== 1 || ($c['module'] ?? '') !== $module
            || ($c['site'] ?? '') !== self::site($site) || ($c['installation'] ?? '') !== $installation
            || !is_int($c['issued'] ?? null) || !is_int($c['expires'] ?? null) || !is_int($c['license_expires'] ?? null)
            || $c['issued'] > $now + 60 || $c['expires'] <= $now || $c['license_expires'] <= $now
            || $c['expires'] > $c['issued'] + 3600 || $c['expires'] > $c['license_expires']
            || empty($c['company']) || empty($c['id'])) {
            throw new RuntimeException('scl_invalid');
        }
        return $c;
    }
}
