<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * JSON response for chatbot training AJAX (discards accidental PHP output).
 */
function prchat_training_send_json(array $payload, int $httpCode = 200): void
{
    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    $CI = &get_instance();
    $CI->output
        ->set_status_header($httpCode)
        ->set_content_type('application/json', 'utf-8')
        ->set_output(json_encode($payload, JSON_UNESCAPED_UNICODE));
}

/**
 * Turn exceptions (OpenAI HTTP, missing key, network) into staff-facing messages.
 */
function prchat_training_format_error(Throwable $e): string
{
    $message = trim($e->getMessage());

    if (stripos($message, 'OpenAI API key not configured') !== false) {
        return _l('chatbot_training_no_api_key');
    }

    if (class_exists(\GuzzleHttp\Exception\ConnectException::class) && $e instanceof \GuzzleHttp\Exception\ConnectException) {
        return _l('chatbot_training_openai_connection');
    }

    if (class_exists(\GuzzleHttp\Exception\ClientException::class) && $e instanceof \GuzzleHttp\Exception\ClientException) {
        $response = $e->getResponse();
        $status = $response ? $response->getStatusCode() : 0;
        $apiMessage = prchat_training_parse_openai_error_body($response);

        if ($status === 401) {
            return $apiMessage ?: _l('chatbot_training_openai_auth_failed');
        }
        if ($status === 429) {
            return $apiMessage ?: _l('chatbot_training_openai_rate_limit');
        }
        if ($status >= 500) {
            return $apiMessage ?: _l('chatbot_training_openai_server_error');
        }

        return $apiMessage ?: sprintf(_l('chatbot_training_openai_http_error'), $status);
    }

    if (class_exists(\GuzzleHttp\Exception\RequestException::class) && $e instanceof \GuzzleHttp\Exception\RequestException) {
        if ($message !== '') {
            return $message;
        }

        return _l('chatbot_training_openai_connection');
    }

    if (stripos($message, 'Invalid response from OpenAI embeddings API') !== false) {
        return _l('chatbot_training_openai_bad_response');
    }

    if (stripos($message, 'Could not resolve host') !== false || stripos($message, 'Connection timed out') !== false) {
        return _l('chatbot_training_openai_connection');
    }

    return $message !== '' ? $message : _l('chatbot_unknown_error');
}

/**
 * @param \Psr\Http\Message\ResponseInterface|null $response
 */
function prchat_training_parse_openai_error_body($response): string
{
    if (!$response || !method_exists($response, 'getBody')) {
        return '';
    }

    try {
        $raw = (string) $response->getBody();
        $data = json_decode($raw, true);
        if (is_array($data) && !empty($data['error']['message'])) {
            return (string) $data['error']['message'];
        }
    } catch (Throwable $ignored) {
        // ignore
    }

    return '';
}
