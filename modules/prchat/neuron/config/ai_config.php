<?php

/**
 * AI Configuration
 * 
 * Centralized OpenAI configuration for the chatbot module.
 * Checks for the openai module's key first, then falls back to the chatbot's own key.
 */

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Resolve the OpenAI API key.
 * 
 * Priority:
 * 1. chatbot-specific key (chatbot_openai_api_key option)
 * 2. openai module key (openai_api_key option) as fallback
 *
 * @return string The API key, or empty string if none found.
 */
function chatbot_resolve_openai_key(): string
{
    $possibleOptions = [
        'chatbot_openai_api_key',
        'openai_api_key',
        'smart_choice_openai_api_key',
        'smart_choice_ai_openai_api_key',
        'smart_choice_ai_agent_openai_api_key',
        'ai_openai_api_key',
        'open_ai_api_key',
        'openai_secret_key',
    ];

    foreach ($possibleOptions as $optionName) {
        $value = trim((string) get_option($optionName));
        if ($value !== '') {
            return $value;
        }
    }

    return '';
}
