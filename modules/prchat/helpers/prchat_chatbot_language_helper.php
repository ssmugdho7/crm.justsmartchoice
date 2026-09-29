<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Chatbot language helpers (safe for public widget API, no admin-only hooks).
 */

/**
 * Languages with a prchat pack under modules/prchat/language/.
 *
 * @return array<string, string> slug => label
 */
function prchat_module_language_options(): array
{
    static $options = null;

    if ($options !== null) {
        return $options;
    }

    $options = [];
    $langDir = dirname(__DIR__) . '/language';

    if (!is_dir($langDir)) {
        return ['english' => prchat_module_language_label('english')];
    }

    foreach (scandir($langDir) as $entry) {
        if ($entry === '.' || $entry === '..') {
            continue;
        }

        $packDir = $langDir . '/' . $entry;
        if (!is_dir($packDir)) {
            continue;
        }

        $langFile = $packDir . '/chat_lang.php';
        if (!is_file($langFile)) {
            continue;
        }

        $options[$entry] = prchat_module_language_label($entry);
    }

    if ($options === []) {
        $options['english'] = prchat_module_language_label('english');
    }

    return prchat_sort_module_language_options($options);
}

/**
 * Preferred order for language pickers (widget + admin).
 *
 * @return string[]
 */
function prchat_module_language_display_order(): array
{
    return [
        'english',
        'german',
        'french',
        'dutch',
        'italian',
        'russian',
        'spanish',
    ];
}

/**
 * @param array<string, string> $options slug => label
 * @return array<string, string>
 */
function prchat_sort_module_language_options(array $options): array
{
    $sorted = [];

    foreach (prchat_module_language_display_order() as $slug) {
        if (isset($options[$slug])) {
            $sorted[$slug] = $options[$slug];
            unset($options[$slug]);
        }
    }

    uasort($options, static function ($a, $b) {
        return strcasecmp($a, $b);
    });

    foreach ($options as $slug => $label) {
        $sorted[$slug] = $label;
    }

    return $sorted;
}

/**
 * @return array<string, true>
 */
function prchat_chatbot_known_language_slugs(): array
{
    static $slugs = null;

    if ($slugs !== null) {
        return $slugs;
    }

    $slugs = [];
    foreach (array_keys(prchat_module_language_options()) as $key) {
        $slugs[$key] = true;
    }

    return $slugs;
}

/**
 * Normalize a language slug or browser locale to a module language key.
 */
function prchat_normalize_chatbot_language(?string $code): ?string
{
    if ($code === null) {
        return null;
    }

    $raw = strtolower(trim($code));
    if ($raw === '' || $raw === 'any') {
        return null;
    }

    $known = prchat_chatbot_known_language_slugs();

    // Module packs use folder slugs with underscores (e.g. portuguese_br).
    $folderSlug = str_replace('-', '_', $raw);
    if (isset($known[$folderSlug])) {
        return $folderSlug;
    }

    $code = str_replace('_', '-', $raw);

    $aliases = [
        'en' => 'english', 'en-us' => 'english', 'en-gb' => 'english',
        'nl' => 'dutch', 'nl-nl' => 'dutch',
        'fr' => 'french', 'fr-fr' => 'french',
        'de' => 'german', 'de-de' => 'german',
        'it' => 'italian', 'it-it' => 'italian',
        'es' => 'spanish', 'es-es' => 'spanish',
        'pt' => 'portuguese_br', 'pt-br' => 'portuguese_br', 'portuguese-br' => 'portuguese_br',
        'tr' => 'turkish', 'tr-tr' => 'turkish',
        'uk' => 'ukrainian', 'uk-ua' => 'ukrainian',
        'ru' => 'russian', 'ru-ru' => 'russian',
        'ro' => 'romanian', 'ro-ro' => 'romanian',
        'bg' => 'bulgarian', 'bg-bg' => 'bulgarian',
    ];

    if (isset($aliases[$code])) {
        $code = $aliases[$code];
    }

    if (isset($known[$code])) {
        return $code;
    }

    $short = explode('-', $code)[0];
    if (isset($aliases[$short])) {
        return $aliases[$short];
    }

    return isset($known[$short]) ? $short : null;
}

/**
 * Widget language from chatbot appearance (admin default for UI strings).
 */
function prchat_chatbot_widget_language(?array $appearance = null): string
{
    $widgetLang = is_array($appearance) ? ($appearance['widget_language'] ?? null) : null;
    $widgetLang = prchat_normalize_chatbot_language($widgetLang);

    return $widgetLang ?: 'english';
}

/**
 * Display label for an installed module language pack (admin + widget picker).
 */
function prchat_module_language_label(string $slug): string
{
    if (function_exists('_l')) {
        $key = 'chatbot_language_' . $slug;
        $label = _l($key);
        if ($label !== $key && $label !== '') {
            return $label;
        }
    }

    static $endonyms = [
        'english'       => 'English',
        'dutch'         => 'Nederlands',
        'french'        => 'Français',
        'german'        => 'Deutsch',
        'italian'       => 'Italiano',
        'spanish'       => 'Español',
        'portuguese_br' => 'Português (BR)',
        'turkish'       => 'Türkçe',
        'ukrainian'     => 'Українська',
        'russian'       => 'Русский',
        'romanian'      => 'Română',
        'bulgarian'     => 'Български',
    ];

    return $endonyms[$slug] ?? ucfirst(str_replace('_', ' ', $slug));
}

/**
 * Two-letter display codes for the widget language tab (ISO 639-1 where applicable).
 *
 * @return array<string, string> slug => code
 */
function prchat_module_language_display_codes(): array
{
    static $codes = null;

    if ($codes !== null) {
        return $codes;
    }

    $codes = [
        'english'       => 'EN',
        'dutch'         => 'NL',
        'french'        => 'FR',
        'german'        => 'DE',
        'italian'       => 'IT',
        'spanish'       => 'ES',
        'portuguese_br' => 'BR',
        'turkish'       => 'TR',
        'ukrainian'     => 'UA',
        'russian'       => 'RU',
        'romanian'      => 'RO',
        'bulgarian'     => 'BG',
    ];

    return $codes;
}

/**
 * @param string|null $mode hybrid|fixed|visitor_first
 */
function prchat_normalize_visitor_language_mode(?string $mode): string
{
    $mode = strtolower(trim((string) $mode));
    $mode = str_replace('-', '_', $mode);

    if (in_array($mode, ['fixed', 'visitor_first', 'visitor'], true)) {
        return $mode === 'visitor' ? 'visitor_first' : $mode;
    }

    return 'hybrid';
}

/**
 * Resolve UI, chip content, and AI reply languages from admin mode + detected visitor locale.
 *
 * @return array{
 *   mode: string,
 *   widget: string,
 *   visitor: string,
 *   ui: string,
 *   content: string,
 *   ai: string
 * }
 */
function prchat_resolve_widget_languages(?array $appearance, ?string $detectedVisitorLanguage): array
{
    $appearance = is_array($appearance) ? $appearance : [];
    $mode = prchat_normalize_visitor_language_mode($appearance['visitor_language_mode'] ?? 'hybrid');
    $widgetLang = prchat_chatbot_widget_language($appearance);
    $visitorLang = prchat_normalize_chatbot_language($detectedVisitorLanguage) ?: $widgetLang;

    if ($mode === 'fixed') {
        return [
            'mode'    => 'fixed',
            'widget'  => $widgetLang,
            'visitor' => $widgetLang,
            'ui'      => $widgetLang,
            'content' => $widgetLang,
            'ai'      => $widgetLang,
        ];
    }

    if ($mode === 'visitor_first') {
        return [
            'mode'    => 'visitor_first',
            'widget'  => $widgetLang,
            'visitor' => $visitorLang,
            'ui'      => $visitorLang,
            'content' => $visitorLang,
            'ai'      => $visitorLang,
        ];
    }

    return [
        'mode'    => 'hybrid',
        'widget'  => $widgetLang,
        'visitor' => $visitorLang,
        'ui'      => $widgetLang,
        'content' => $visitorLang,
        'ai'      => $visitorLang,
    ];
}
