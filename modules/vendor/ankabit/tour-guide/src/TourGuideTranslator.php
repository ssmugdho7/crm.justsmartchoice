<?php

namespace TourGuide;

/**
 * Class TourGuideTranslator
 *
 * A class to handle translation requests using multiple translation services.
 * This class prioritizes translation with Lingva, then uses MyMemory, and falls back to Google Translate if necessary.
 *
 * Methods:
 * - translate($text, $sourceLang, $targetLang): Translates the given text from sourceLang to targetLang.
 * - translateWithLingva($text, $sourceLang, $targetLang): Uses the Lingva API to perform the translation.
 * - translateWithMyMemory($text, $sourceLang, $targetLang): Uses the MyMemory API to perform the translation.
 * - translateWithGoogle($text, $sourceLang, $targetLang): Uses the Google Translate API to perform the translation.
 * - fetchTranslation($url): Fetches translation data from the specified URL using cURL.
 */
class TourGuideTranslator
{
    // API endpoints
    private $lingvaApiUrl = 'https://lingva.ml/translate';
    private $mymemoryApiUrl = 'https://api.mymemory.translated.net/get';
    private $googleApiUrl = 'https://translate.googleapis.com/translate_a/single';

    /**
     * Translates the given text from sourceLang to targetLang using prioritized APIs.
     *
     * @param string $text The text to be translated.
     * @param string $sourceLang The source language code (e.g., 'en' for English).
     * @param string $targetLang The target language code (e.g., 'fr' for French).
     * @return string|null The translated text or null if translation fails.
     */
    public function translate($text, $sourceLang, $targetLang)
    {
        // Attempt translation with Lingva
        $translation = $this->translateWithLingva($text, $sourceLang, $targetLang);
        if ($translation) {
            return $translation;
        }

        // If Lingva fails, try MyMemory
        $translation = $this->translateWithMyMemory($text, $sourceLang, $targetLang);
        if ($translation) {
            return $translation;
        }

        // If MyMemory also fails, use Google Translate
        return $this->translateWithGoogle($text, $sourceLang, $targetLang);
    }

    /**
     * Translates the given text using the Lingva API.
     *
     * @param string $text The text to be translated.
     * @param string $sourceLang The source language code.
     * @param string $targetLang The target language code.
     * @return string|null The translated text or null if translation fails.
     */
    private function translateWithLingva($text, $sourceLang, $targetLang)
    {
        $url = "{$this->lingvaApiUrl}?q=" . urlencode($text) . "&source={$sourceLang}&target={$targetLang}";
        return $this->fetchTranslation($url);
    }

    /**
     * Translates the given text using the MyMemory API.
     *
     * @param string $text The text to be translated.
     * @param string $sourceLang The source language code.
     * @param string $targetLang The target language code.
     * @return string|null The translated text or null if translation fails.
     */
    private function translateWithMyMemory($text, $sourceLang, $targetLang)
    {
        $url = "{$this->mymemoryApiUrl}?q=" . urlencode($text) . "&langpair={$sourceLang}|{$targetLang}";
        $response = $this->fetchTranslation($url);
        if ($response && isset($response['responseData']['translatedText'])) {
            return $response['responseData']['translatedText'];
        }
        return null;
    }

    /**
     * Translates the given text using the Google Translate API.
     *
     * @param string $text The text to be translated.
     * @param string $sourceLang The source language code.
     * @param string $targetLang The target language code.
     * @return string|null The translated text or null if translation fails.
     */
    private function translateWithGoogle($text, $sourceLang, $targetLang)
    {
        $url = "{$this->googleApiUrl}?client=gtx&sl={$sourceLang}&tl={$targetLang}&dt=t&q=" . urlencode($text);
        $response = $this->fetchTranslation($url);
        if ($response && isset($response[0][0][0])) {
            return $response[0][0][0];
        }
        return null;
    }

    /**
     * Fetches translation data from the specified URL using cURL.
     *
     * @param string $url The URL to fetch data from.
     * @return array|null The decoded response data or null if request fails.
     */
    private function fetchTranslation($url)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            // Handle cURL error
            curl_close($ch);
            return null;
        }

        curl_close($ch);

        $decoded = json_decode($response, true);
        return $decoded;
    }

    /**
     * Function to get a list of common languages based on RFC 3066, including their common names and local names.
     *
     * @return array An associative array where each entry is a language code, its common English name, and its local name.
     */
    static function getLanguagesRFC3066()
    {
        return [
            ['code' => 'en-US', 'name' => 'English', 'local_name' => 'English (United States)'],
            ['code' => 'en-GB', 'name' => 'English', 'local_name' => 'English (United Kingdom)'],
            ['code' => 'fr-FR', 'name' => 'French', 'local_name' => 'Français (France)'],
            ['code' => 'fr-CA', 'name' => 'French', 'local_name' => 'Français (Canada)'],
            ['code' => 'es-ES', 'name' => 'Spanish', 'local_name' => 'Español (España)'],
            ['code' => 'es-MX', 'name' => 'Spanish', 'local_name' => 'Español (México)'],
            ['code' => 'de-DE', 'name' => 'German', 'local_name' => 'Deutsch (Deutschland)'],
            ['code' => 'de-AT', 'name' => 'German', 'local_name' => 'Deutsch (Österreich)'],
            ['code' => 'it-IT', 'name' => 'Italian', 'local_name' => 'Italiano (Italia)'],
            ['code' => 'pt-PT', 'name' => 'Portuguese', 'local_name' => 'Português (Portugal)'],
            ['code' => 'pt-BR', 'name' => 'Portuguese', 'local_name' => 'Português (Brasil)'],
            ['code' => 'ru-RU', 'name' => 'Russian', 'local_name' => 'Русский (Россия)'],
            ['code' => 'zh-CN', 'name' => 'Chinese', 'local_name' => '简体中文 (中国)'],
            ['code' => 'zh-TW', 'name' => 'Chinese', 'local_name' => '繁體中文 (台灣)'],
            ['code' => 'ja-JP', 'name' => 'Japanese', 'local_name' => '日本語 (日本)'],
            ['code' => 'ko-KR', 'name' => 'Korean', 'local_name' => '한국어 (대한민국)'],
            ['code' => 'ar-SA', 'name' => 'Arabic', 'local_name' => 'العربية (المملكة العربية السعودية)'],
            ['code' => 'he-IL', 'name' => 'Hebrew', 'local_name' => 'עברית (ישראל)'],
            ['code' => 'nl-NL', 'name' => 'Dutch', 'local_name' => 'Nederlands (Nederland)'],
            ['code' => 'sv-SE', 'name' => 'Swedish', 'local_name' => 'Svenska (Sverige)'],
            ['code' => 'da-DK', 'name' => 'Danish', 'local_name' => 'Dansk (Danmark)'],
            ['code' => 'fi-FI', 'name' => 'Finnish', 'local_name' => 'Suomi (Suomi)'],
            ['code' => 'no-NO', 'name' => 'Norwegian', 'local_name' => 'Norsk (Norge)'],
            ['code' => 'pl-PL', 'name' => 'Polish', 'local_name' => 'Polski (Polska)'],
            ['code' => 'cs-CZ', 'name' => 'Czech', 'local_name' => 'Čeština (Česká republika)'],
            ['code' => 'el-GR', 'name' => 'Greek', 'local_name' => 'Ελληνικά (Ελλάδα)'],
            ['code' => 'tr-TR', 'name' => 'Turkish', 'local_name' => 'Türkçe (Türkiye)'],
            ['code' => 'bg-BG', 'name' => 'Bulgarian', 'local_name' => 'Български (България)'],
            ['code' => 'ca-ES', 'name' => 'Catalan', 'local_name' => 'Català (Catalunya)'],
            ['code' => 'id-ID', 'name' => 'Indonesian', 'local_name' => 'Bahasa Indonesia'],
            ['code' => 'vi-VN', 'name' => 'Vietnamese', 'local_name' => 'Tiếng Việt'],
            ['code' => 'ms-MY', 'name' => 'Malay', 'local_name' => 'Bahasa Melayu'],
            ['code' => 'sl-SI', 'name' => 'Slovenian', 'local_name' => 'Slovenščina (Slovenija)'],
            ['code' => 'lt-LT', 'name' => 'Lithuanian', 'local_name' => 'Lietuvių (Lietuva)'],
            ['code' => 'lv-LV', 'name' => 'Latvian', 'local_name' => 'Latviešu (Latvija)'],
            ['code' => 'et-EE', 'name' => 'Estonian', 'local_name' => 'Eesti (Eesti)'],
            ['code' => 'hr-HR', 'name' => 'Croatian', 'local_name' => 'Hrvatski (Hrvatska)'],
            ['code' => 'ro-RO', 'name' => 'Romanian', 'local_name' => 'Română (România)'],
            ['code' => 'sk-SK', 'name' => 'Slovak', 'local_name' => 'Slovenčina (Slovensko)'],
            ['code' => 'uk-UA', 'name' => 'Ukrainian', 'local_name' => 'Українська (Україна)'],
            ['code' => 'tl-PH', 'name' => 'Tagalog', 'local_name' => 'Tagalog (Pilipinas)'],
            ['code' => 'ml-IN', 'name' => 'Malayalam', 'local_name' => 'മലയാളം (ഇന്ത്യ)'],
            ['code' => 'sw-KE', 'name' => 'Swahili', 'local_name' => 'Kiswahili (Kenya)'],
            ['code' => 'eu-ES', 'name' => 'Basque', 'local_name' => 'Euskara (Euskadi)'],
            ['code' => 'gl-ES', 'name' => 'Galician', 'local_name' => 'Galego (Galicia)'],
        ];
    }

    /**
     * Function to get a list of common languages with their 2-letter codes and common names.
     *
     * @return array An associative array where each entry includes a language code and its common name.
     */
    static function getLanguages()
    {
        return [
            ['code' => 'en', 'name' => 'English'],
            ['code' => 'fr', 'name' => 'French'],
            ['code' => 'es', 'name' => 'Spanish'],
            ['code' => 'de', 'name' => 'German'],
            ['code' => 'it', 'name' => 'Italian'],
            ['code' => 'pt', 'name' => 'Portuguese'],
            ['code' => 'ru', 'name' => 'Russian'],
            ['code' => 'zh', 'name' => 'Chinese'],
            ['code' => 'ja', 'name' => 'Japanese'],
            ['code' => 'ko', 'name' => 'Korean'],
            ['code' => 'ar', 'name' => 'Arabic'],
            ['code' => 'he', 'name' => 'Hebrew'],
            ['code' => 'nl', 'name' => 'Dutch'],
            ['code' => 'sv', 'name' => 'Swedish'],
            ['code' => 'da', 'name' => 'Danish'],
            ['code' => 'fi', 'name' => 'Finnish'],
            ['code' => 'no', 'name' => 'Norwegian'],
            ['code' => 'pl', 'name' => 'Polish'],
            ['code' => 'cs', 'name' => 'Czech'],
            ['code' => 'el', 'name' => 'Greek'],
            ['code' => 'tr', 'name' => 'Turkish'],
            ['code' => 'bg', 'name' => 'Bulgarian'],
            ['code' => 'ca', 'name' => 'Catalan'],
            ['code' => 'vi', 'name' => 'Vietnamese'],
            ['code' => 'sl', 'name' => 'Slovak'],
            ['code' => 'ro', 'name' => 'Romanian'],
            ['code' => 'hr', 'name' => 'Croatian'],
            ['code' => 'sr', 'name' => 'Serbian'],
            ['code' => 'hu', 'name' => 'Hungarian'],
            ['code' => 'lt', 'name' => 'Lithuanian'],
            ['code' => 'lv', 'name' => 'Latvian'],
            ['code' => 'et', 'name' => 'Estonian'],
            ['code' => 'mk', 'name' => 'Macedonian'],
            ['code' => 'sq', 'name' => 'Albanian'],
            ['code' => 'gl', 'name' => 'Galician'],
            ['code' => 'eu', 'name' => 'Basque'],
            ['code' => 'sw', 'name' => 'Swahili'],
            ['code' => 'pa', 'name' => 'Punjabi'],
            ['code' => 'ta', 'name' => 'Tamil'],
            ['code' => 'te', 'name' => 'Telugu'],
            ['code' => 'mr', 'name' => 'Marathi'],
            ['code' => 'kn', 'name' => 'Kannada'],
            ['code' => 'ml', 'name' => 'Malayalam'],
            ['code' => 'my', 'name' => 'Burmese'],
        ];
    }
}