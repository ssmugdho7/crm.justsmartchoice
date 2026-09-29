<?php

namespace PerfexChat\Neuron\Services;

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Translate training Q&A pairs via OpenAI (widget chip copy only; main row stays source for RAG).
 */
class QaTranslationService
{
    private static array $languageNames = [
        'english'       => 'English',
        'dutch'         => 'Dutch',
        'french'        => 'French',
        'german'        => 'German',
        'italian'       => 'Italian',
        'spanish'       => 'Spanish',
        'portuguese_br' => 'Portuguese (Brazil)',
        'turkish'       => 'Turkish',
        'ukrainian'     => 'Ukrainian',
        'russian'       => 'Russian',
        'romanian'      => 'Romanian',
        'bulgarian'     => 'Bulgarian',
    ];

    /**
     * @return array<string, array{question: string, answer: string}>
     */
    public function translateToAllLanguages(string $question, string $answer): array
    {
        $out = [];
        foreach (prchat_module_language_options() as $slug => $label) {
            $out[$slug] = $this->translatePair($question, $answer, $slug, $label);
        }

        return $out;
    }

    /**
     * @return array{question: string, answer: string}
     */
    public function translatePair(string $question, string $answer, string $languageSlug, ?string $languageLabel = null): array
    {
        $question = trim($question);
        $answer = trim($answer);
        $langName = $languageLabel ?: (self::$languageNames[$languageSlug] ?? $languageSlug);

        $apiKey = \chatbot_resolve_openai_key();
        if ($apiKey === '') {
            throw new \RuntimeException(_l('chatbot_training_no_api_key'));
        }

        $payload = [
            'model' => 'gpt-4o-mini',
            'temperature' => 0.2,
            'max_tokens' => 800,
            'response_format' => ['type' => 'json_object'],
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You translate short customer-support FAQ entries. Return only JSON: {"question":"...","answer":"..."}. Keep the same meaning. No extra keys.',
                ],
                [
                    'role' => 'user',
                    'content' => "Translate into {$langName}.\n\nQuestion:\n{$question}\n\nAnswer:\n{$answer}",
                ],
            ],
        ];

        $client = new \GuzzleHttp\Client([
            'base_uri' => 'https://api.openai.com/v1/',
            'verify' => false,
            'timeout' => 60,
            'headers' => [
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ],
        ]);

        $response = $client->post('chat/completions', ['json' => $payload]);
        $body = json_decode((string) $response->getBody(), true);
        $content = $body['choices'][0]['message']['content'] ?? '';
        $parsed = json_decode($content, true);

        if (!is_array($parsed) || empty($parsed['question'])) {
            throw new \RuntimeException(_l('chatbot_qa_translation_failed'));
        }

        return [
            'question' => trim((string) $parsed['question']),
            'answer'   => trim((string) ($parsed['answer'] ?? $answer)),
        ];
    }
}
