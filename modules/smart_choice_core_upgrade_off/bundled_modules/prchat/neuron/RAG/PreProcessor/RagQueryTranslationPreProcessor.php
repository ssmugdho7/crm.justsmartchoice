<?php

namespace PerfexChat\Neuron\RAG\PreProcessor;

use NeuronAI\Chat\Messages\Message;
use NeuronAI\Chat\Messages\UserMessage;
use NeuronAI\RAG\PreProcessor\PreProcessorInterface;
use PerfexChat\Neuron\Models\Chatbot;
use PerfexChat\Neuron\Services\RagQueryTranslationService;

/**
 * Rewrites the visitor question into the training language before embedding search.
 */
class RagQueryTranslationPreProcessor implements PreProcessorInterface
{
    public function __construct(
        private Chatbot $chatbot,
        private ?string $visitorLanguage,
        private RagQueryTranslationService $translator = new RagQueryTranslationService()
    ) {
    }

    public function process(Message $question): Message
    {
        $searchLanguage = $this->chatbot->getRagSearchLanguage();
        $visitorLanguage = $this->resolveVisitorLanguage();

        if ($visitorLanguage === $searchLanguage) {
            return $question;
        }

        $content = trim((string) $question->getContent());
        if ($content === '') {
            return $question;
        }

        $searchQuery = $this->translator->translateQuestionForSearch(
            $content,
            $visitorLanguage,
            $searchLanguage
        );

        if ($searchQuery === $content) {
            return $question;
        }

        return new UserMessage($searchQuery);
    }

    private function resolveVisitorLanguage(): string
    {
        $slug = $this->visitorLanguage;
        if (function_exists('prchat_normalize_chatbot_language')) {
            $slug = prchat_normalize_chatbot_language($slug);
        }

        if (is_string($slug) && $slug !== '') {
            return $slug;
        }

        return $this->chatbot->getRagSearchLanguage();
    }
}
