<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Openai_repair_client
{
    private const DEFAULT_BASE_URL = 'https://api.openai.com/v1';
    private const DEFAULT_MODEL = 'gpt-5-mini';

    public function request($system, $input)
    {
        $changeSchema = [
            'type' => 'object',
            'properties' => [
                'path' => ['type' => 'string'],
                'operation' => ['type' => 'string', 'enum' => ['replace', 'create', 'rename']],
                'content' => ['type' => 'string'],
                'new_path' => ['type' => 'string'],
                'reason' => ['type' => 'string'],
            ],
            'required' => ['path', 'operation', 'content', 'new_path', 'reason'],
            'additionalProperties' => false,
        ];

        $schema = [
            'type' => 'object',
            'properties' => [
                'summary' => ['type' => 'string'],
                'risk' => ['type' => 'string', 'enum' => ['low', 'medium', 'high']],
                'changes' => ['type' => 'array', 'items' => $changeSchema],
            ],
            'required' => ['summary', 'risk', 'changes'],
            'additionalProperties' => false,
        ];

        $payload = [
            'model' => $this->resolveModel(),
            'instructions' => $system,
            'input' => $input,
            'text' => [
                'format' => [
                    'type' => 'json_schema',
                    'name' => 'repair_plan',
                    'strict' => true,
                    'schema' => $schema,
                ],
            ],
        ];

        $json = $this->send($payload);
        $text = $this->extractOutputText($json);
        $plan = json_decode($text, true);
        if (!is_array($plan)) {
            throw new RuntimeException('OpenAI returned an invalid repair plan. Raw response did not contain valid JSON.');
        }
        return $plan;
    }

    public function rewriteRepairPrompt($input)
    {
        $payload = [
            'model' => $this->resolveModel(),
            'instructions' => 'Rewrite the user message as a precise professional software-repair instruction in English only. Preserve every technical fact, error message, filename, route, version, and requested behavior. Do not answer the request, do not suggest unrelated features, and do not add assumptions. Return only the improved repair instruction.',
            'input' => (string) $input,
        ];
        return trim($this->extractOutputText($this->send($payload)));
    }

    public function connectionInfo()
    {
        return [
            'key_source' => $this->resolveApiKey(true)['source'],
            'base_url' => $this->resolveBaseUrl(),
            'model' => $this->resolveModel(),
        ];
    }

    private function send(array $payload)
    {
        $credential = $this->resolveApiKey(false);
        $url = $this->resolveBaseUrl() . '/responses';
        $headers = [
            'Authorization: Bearer ' . $credential['value'],
            'Content-Type: application/json',
        ];
        $organization = trim((string) get_option('sc_ai_repair_organization'));
        if ($organization !== '') {
            $headers[] = 'OpenAI-Organization: ' . $organization;
        }

        $lastMessage = '';
        for ($attempt = 1; $attempt <= 3; $attempt++) {
            $ch = curl_init($url);
            if ($ch === false) {
                throw new RuntimeException('Unable to initialize the OpenAI connection.');
            }
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                CURLOPT_CONNECTTIMEOUT => 20,
                CURLOPT_TIMEOUT => 180,
                CURLOPT_FOLLOWLOCATION => false,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
            ]);
            $raw = curl_exec($ch);
            $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($raw !== false && $code >= 200 && $code < 300) {
                $decoded = json_decode($raw, true);
                if (!is_array($decoded)) {
                    throw new RuntimeException('OpenAI returned a response that was not valid JSON.');
                }
                return $decoded;
            }

            $lastMessage = $this->formatHttpError($code, $error, (string) $raw, $url);
            if (!in_array($code, [429, 500, 502, 503, 504], true) || $attempt === 3) {
                break;
            }
            usleep($attempt * 500000);
        }

        throw new RuntimeException($lastMessage);
    }

    private function resolveApiKey($allowEmpty)
    {
        $candidates = [
            'sc_ai_repair_api_key',
            'openai_api_key',
            'open_ai_api_key',
            'chatgpt_api_key',
            'ai_openai_api_key',
            'sammy_ai_api_key',
            'sc_ai_api_key',
        ];
        foreach ($candidates as $name) {
            $value = trim((string) get_option($name));
            if ($value !== '') {
                return ['value' => $value, 'source' => $name];
            }
        }

        $CI = &get_instance();
        if (isset($CI->db)) {
            $table = db_prefix() . 'options';
            $rows = $CI->db->select('name,value')
                ->from($table)
                ->group_start()
                ->like('name', 'openai')
                ->or_like('name', 'chatgpt')
                ->group_end()
                ->like('name', 'key')
                ->get()->result_array();
            foreach ($rows as $row) {
                $value = trim((string) ($row['value'] ?? ''));
                if ($value !== '' && strpos($value, 'sk-') === 0) {
                    return ['value' => $value, 'source' => (string) $row['name']];
                }
            }
        }

        if ($allowEmpty) {
            return ['value' => '', 'source' => 'not_found'];
        }
        throw new RuntimeException('OpenAI API key was not found. Save it in AI Repair Settings or configure it in the CRM OpenAI integration.');
    }

    private function resolveBaseUrl()
    {
        $candidates = [
            get_option('sc_ai_repair_base_url'),
            get_option('openai_base_url'),
            get_option('open_ai_base_url'),
            get_option('chatgpt_base_url'),
            self::DEFAULT_BASE_URL,
        ];
        foreach ($candidates as $candidate) {
            $base = trim((string) $candidate);
            if ($base === '') {
                continue;
            }
            if (!preg_match('~^https?://~i', $base)) {
                continue;
            }
            $parts = parse_url($base);
            if (!is_array($parts) || empty($parts['host'])) {
                continue;
            }
            $base = rtrim($base, '/');
            $base = preg_replace('~/responses$~i', '', $base);
            if (stripos($base, 'api.openai.com') !== false && !preg_match('~/v\d+$~', $base)) {
                $base .= '/v1';
            }
            return $base;
        }
        return self::DEFAULT_BASE_URL;
    }

    private function resolveModel()
    {
        foreach (['sc_ai_repair_model', 'openai_model', 'chatgpt_model'] as $name) {
            $model = trim((string) get_option($name));
            if ($model !== '') {
                return $model;
            }
        }
        return self::DEFAULT_MODEL;
    }

    private function extractOutputText(array $json)
    {
        if (!empty($json['output_text']) && is_string($json['output_text'])) {
            return $json['output_text'];
        }
        $text = '';
        foreach (($json['output'] ?? []) as $output) {
            foreach (($output['content'] ?? []) as $content) {
                if (($content['type'] ?? '') === 'output_text' && isset($content['text'])) {
                    $text .= (string) $content['text'];
                }
            }
        }
        if ($text === '') {
            throw new RuntimeException('OpenAI returned no text output.');
        }
        return $text;
    }

    private function formatHttpError($code, $curlError, $raw, $url)
    {
        if ($curlError !== '') {
            return 'OpenAI connection failed: ' . $curlError . '. Endpoint: ' . $url;
        }
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            $message = $decoded['error']['message'] ?? $decoded['message'] ?? '';
            if ($message !== '') {
                return 'OpenAI request failed (HTTP ' . $code . '): ' . $message;
            }
        }
        if ($code === 503) {
            return 'OpenAI request failed after 3 attempts with HTTP 503 Service Unavailable. The hosting server, firewall, reverse proxy, or AI provider temporarily refused the request. Verify outbound HTTPS access to api.openai.com and retry.';
        }
        $plain = trim(strip_tags($raw));
        if (strlen($plain) > 500) {
            $plain = substr($plain, 0, 500) . '...';
        }
        return 'OpenAI request failed (HTTP ' . $code . '): ' . ($plain !== '' ? $plain : 'No response body.') . ' Endpoint: ' . $url;
    }
}
