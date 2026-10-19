<?php
defined('BASEPATH') or exit('No direct script access allowed');
/** Provider endpoints are fixed; no user-supplied fetch URL or unverified JWT is accepted. */
class Sc_oauth
{
    private $settings;
    private $transport;
    public function __construct(array $settings, $transport = null)
    {
        $this->settings = $settings;
        $this->transport = $transport;
    }
    public static function b64($bytes) { return rtrim(strtr(base64_encode($bytes), '+/', '-_'), '='); }
    private static function unb64($value)
    {
        if (!is_string($value) || !preg_match('/^[A-Za-z0-9_-]+$/D', $value)) { throw new RuntimeException('scsl_failed'); }
        $decoded = base64_decode(strtr($value, '-_', '+/'), true);
        if ($decoded === false) { throw new RuntimeException('scsl_failed'); }
        return $decoded;
    }
    public function authorization($provider, array $tx, $callback)
    {
        $p = ['client_id'=>$this->settings['client_id'], 'redirect_uri'=>$callback,
            'response_type'=>'code', 'state'=>$tx['state']];
        if ($provider === 'google') {
            $url = 'https://accounts.google.com/o/oauth2/v2/auth';
            $p += ['scope'=>'openid email profile', 'nonce'=>$tx['nonce'],
                'code_challenge'=>self::b64(hash('sha256', $tx['verifier'], true)), 'code_challenge_method'=>'S256'];
        } elseif ($provider === 'facebook') {
            $url = 'https://www.facebook.com/' . $this->version() . '/dialog/oauth';
            $p += ['scope'=>'email,public_profile'];
        } elseif ($provider === 'apple') {
            $url = 'https://appleid.apple.com/auth/authorize';
            $p += ['scope'=>'name email', 'nonce'=>$tx['nonce'], 'response_mode'=>'form_post'];
        } else { throw new RuntimeException('scsl_failed'); }
        return $url . '?' . http_build_query($p, '', '&', PHP_QUERY_RFC3986);
    }
    private function version()
    {
        $version = $this->settings['graph_version'] ?? 'v26.0';
        if (!preg_match('/^v[0-9]{1,2}\.0$/D', $version)) { throw new RuntimeException('scsl_failed'); }
        return $version;
    }
    public function exchange($provider, $code, array $tx, $callback)
    {
        if (!is_string($code) || $code === '' || strlen($code) > 8000) { throw new RuntimeException('scsl_failed'); }
        $data = ['client_id'=>$this->settings['client_id'], 'client_secret'=>$this->settings['client_secret'],
            'code'=>$code, 'redirect_uri'=>$callback, 'grant_type'=>'authorization_code'];
        if ($provider === 'facebook') {
            $root = 'https://graph.facebook.com/' . $this->version();
            $tokens = $this->request($root . '/oauth/access_token', $data);
            $token = $tokens['access_token'] ?? '';
            if (!is_string($token) || $token === '' || strlen($token) > 8000) { throw new RuntimeException('scsl_failed'); }
            $debug = $this->request($root . '/debug_token?' . http_build_query(['input_token'=>$token,
                'access_token'=>$this->settings['client_id'] . '|' . $this->settings['client_secret']]))['data'] ?? [];
            if (($debug['is_valid'] ?? false) !== true || (string) ($debug['app_id'] ?? '') !== (string) $this->settings['client_id']
                || empty($debug['user_id']) || (int) ($debug['expires_at'] ?? 0) <= time()) { throw new RuntimeException('scsl_failed'); }
            $profile = $this->request($root . '/me?' . http_build_query(['fields'=>'id,name,email',
                'access_token'=>$token, 'appsecret_proof'=>hash_hmac('sha256', $token, $this->settings['client_secret'])]));
            if ((string) ($profile['id'] ?? '') !== (string) $debug['user_id']) { throw new RuntimeException('scsl_failed'); }
            return ['subject'=>(string) $profile['id'], 'email'=>$profile['email'] ?? '', 'verified'=>false];
        }
        if ($provider === 'google') {
            $data['code_verifier'] = $tx['verifier'];
            $tokens = $this->request('https://oauth2.googleapis.com/token', $data);
            $keys = $this->request('https://www.googleapis.com/oauth2/v3/certs');
            $issuers = ['https://accounts.google.com','accounts.google.com'];
        } elseif ($provider === 'apple') {
            $tokens = $this->request('https://appleid.apple.com/auth/token', $data);
            $keys = $this->request('https://appleid.apple.com/auth/keys');
            $issuers = ['https://appleid.apple.com'];
        } else { throw new RuntimeException('scsl_failed'); }
        $claims = self::verifyIdToken($tokens['id_token'] ?? '', $keys['keys'] ?? [], $issuers,
            $this->settings['client_id'], $tx['nonce']);
        return ['subject'=>$claims['sub'], 'email'=>$claims['email'] ?? '',
            'verified'=>in_array($claims['email_verified'] ?? false, [true,'true'], true)];
    }
    public static function verifyIdToken($jwt, array $keys, array $issuers, $audience, $nonce, $now = null)
    {
        $now = $now === null ? time() : $now;
        if (!is_string($jwt) || strlen($jwt) > 20000 || substr_count($jwt, '.') !== 2) { throw new RuntimeException('scsl_failed'); }
        list($h,$b,$sig) = explode('.', $jwt);
        $header = json_decode(self::unb64($h), true, 16, JSON_THROW_ON_ERROR);
        $claims = json_decode(self::unb64($b), true, 32, JSON_THROW_ON_ERROR);
        if (!is_array($header) || !is_array($claims) || ($header['alg'] ?? '') !== 'RS256'
            || !is_string($header['kid'] ?? null) || !is_string($claims['sub'] ?? null) || $claims['sub'] === ''
            || strlen($claims['sub']) > 255 || !in_array($claims['iss'] ?? '', $issuers, true)
            || ($claims['aud'] ?? '') !== $audience || (isset($claims['azp']) && $claims['azp'] !== $audience)
            || !is_int($claims['exp'] ?? null) || $claims['exp'] <= $now
            || !is_int($claims['iat'] ?? null) || $claims['iat'] > $now + 60
            || (isset($claims['nbf']) && (!is_int($claims['nbf']) || $claims['nbf'] > $now + 60))
            || !is_string($claims['nonce'] ?? null) || !hash_equals($nonce, $claims['nonce'])) {
            throw new RuntimeException('scsl_failed');
        }
        foreach ($keys as $jwk) {
            if (($jwk['kid'] ?? '') !== $header['kid'] || ($jwk['kty'] ?? '') !== 'RSA'
                || (isset($jwk['use']) && $jwk['use'] !== 'sig') || (isset($jwk['alg']) && $jwk['alg'] !== 'RS256')) { continue; }
            $n = self::unb64($jwk['n'] ?? ''); $e = self::unb64($jwk['e'] ?? '');
            if (strlen($n) < 256 || strlen($n) > 1024 || strlen($e) > 8) { continue; }
            $rsa = self::der(0x30, self::integer($n) . self::integer($e));
            $spki = self::der(0x30, hex2bin('300d06092a864886f70d0101010500') . self::der(3, "\0" . $rsa));
            $pem = "-----BEGIN PUBLIC KEY-----\n" . chunk_split(base64_encode($spki),64,"\n") . "-----END PUBLIC KEY-----\n";
            if (openssl_verify($h . '.' . $b, self::unb64($sig), $pem, OPENSSL_ALGO_SHA256) === 1) { return $claims; }
        }
        throw new RuntimeException('scsl_failed');
    }
    private static function integer($bytes)
    {
        $bytes = ltrim($bytes, "\0");
        if ($bytes === '') { $bytes = "\0"; }
        if (ord($bytes[0]) & 0x80) { $bytes = "\0" . $bytes; }
        return self::der(2, $bytes);
    }
    private static function der($tag, $bytes)
    {
        $length = strlen($bytes);
        if ($length < 128) { $size = chr($length); }
        else {
            $size = ''; for ($n=$length; $n>0; $n>>=8) { $size = chr($n & 255) . $size; }
            $size = chr(0x80 | strlen($size)) . $size;
        }
        return chr($tag) . $size . $bytes;
    }
    private function request($url, $form = null)
    {
        if ($this->transport) { return call_user_func($this->transport, $url, $form); }
        $response = '';
        $curl = curl_init($url);
        $options = [CURLOPT_RETURNTRANSFER=>true, CURLOPT_FOLLOWLOCATION=>false,
            CURLOPT_CONNECTTIMEOUT=>3, CURLOPT_TIMEOUT=>10, CURLOPT_PROTOCOLS=>CURLPROTO_HTTPS,
            CURLOPT_SSL_VERIFYPEER=>true, CURLOPT_SSL_VERIFYHOST=>2,
            CURLOPT_WRITEFUNCTION=>function ($ch,$chunk) use (&$response) {
                $response .= $chunk; return strlen($response) <= 65536 ? strlen($chunk) : 0;
            }];
        if ($form !== null) { $options += [CURLOPT_POST=>true, CURLOPT_POSTFIELDS=>http_build_query($form)]; }
        curl_setopt_array($curl, $options);
        $ok = curl_exec($curl); $status = curl_getinfo($curl, CURLINFO_HTTP_CODE); curl_close($curl);
        if ($ok === false || $status !== 200) { throw new RuntimeException('scsl_failed'); }
        $json = json_decode($response, true, 32, JSON_THROW_ON_ERROR);
        if (!is_array($json) || isset($json['error'])) { throw new RuntimeException('scsl_failed'); }
        return $json;
    }
}
