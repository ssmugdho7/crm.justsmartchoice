<?php

namespace PerfexChat\Neuron\Services;

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Translate a visitor question into the training language for RAG vector search only.
 */
class RagQueryTranslationService
{
    /**
     * Return a search query in the training language, or the original text on failure.
     */
    public function translateQuestionForSearch(
        string $question,
        string $fromLanguageSlug,
        string $toLanguageSlug
    ): string {
        $question = trim($question);
        if ($question === '' || $fromLanguageSlug === $toLanguageSlug) {
            return $question;
        }

        $fromName = $this->languageName($fromLanguageSlug);
        $toName = $this->languageName($toLanguageSlug);

        $apiKey = \chatbot_resolve_openai_key();
        if ($apiKey === '') {
            return $question;
        }

        try {
            $payload = [
                'model' => 'gpt-4o-mini',
                'temperature' => 0,
                'max_tokens' => 300,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You translate short customer support questions for semantic search. Return only the translated question text, no quotes or explanation. Preserve meaning and intent.',
                    ],
                    [
                        'role' => 'user',
                        'content' => "Translate from {$fromName} to {$toName}:\n\n{$question}",
                    ],
                ],
            ];

            $client = new \GuzzleHttp\Client([
                'base_uri' => 'https://api.openai.com/v1/',
                'verify' => false,
                'timeout' => 30,
                'headers' => [
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type' => 'application/json',
                ],
            ]);

            $response = $client->post('chat/completions', ['json' => $payload]);
            $body = json_decode((string) $response->getBody(), true);
            $translated = trim((string) ($body['choices'][0]['message']['content'] ?? ''));

            return $translated !== '' ? $translated : $question;
        } catch (\Throwable $e) {
            log_message('error', 'RagQueryTranslationService: ' . $e->getMessage());

            return $question;
        }
    }

    private function languageName(string $slug): string
    {
        if (function_exists('prchat_module_language_label')) {
            return prchat_module_language_label($slug);
        }

        return ucfirst(str_replace('_', ' ', $slug));
    }
}
