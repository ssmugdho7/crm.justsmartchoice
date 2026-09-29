<?php

namespace TourGuide\Helper;

use TourGuide\TourGuideDB;
use TourGuide\TourGuideTranslator;

/**
 * Default implementation of the TourGuideHelperInterface.
 */
class TourGuideDefaultHelper implements TourGuideHelperInterface
{
    public static function dbInstance(): ?TourGuideDB
    {
        // Return a default database instance or null
        return null;
    }

    public static function adminUrl($path = ''): string
    {
        return self::baseUrl('admin/tour_guide/' . $path);
    }

    public static function userUrl($path = ''): string
    {
        return self::baseUrl('tour_guide/' . $path);
    }

    public static function baseUrl($path = ''): string
    {
        // Determine the protocol (http or https)
        $protocol = self::isHttps() ? 'https://' : 'http://';

        // Ensure host and request URI exist
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $port = $_SERVER['SERVER_PORT'] ?? null;

        // Add the port to the host if it's a non-default port for the protocol
        if (!empty($port)) {
            if (($protocol === 'https://' && $port !== '443') || ($protocol === 'http://' && $port !== '80')) {
                $host .= ':' . $port;
            }
        }

        // Ensure request URI is properly set
        $requestUri = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/'), '/');

        // Construct and return the full base URL
        return rtrim($protocol . $host . $requestUri, '/') . '/' . ltrim($path, '/');
    }

    private static function isHttps(): bool
    {
        // Check if HTTPS is directly set
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            return true;
        }

        // Check for forwarded protocol via proxy
        if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
            return true;
        }

        // Check Cloudflare visitor protocol
        if (isset($_SERVER['HTTP_CF_VISITOR'])) {
            $cfVisitor = json_decode($_SERVER['HTTP_CF_VISITOR']);
            if (isset($cfVisitor->scheme) && $cfVisitor->scheme === 'https') {
                return true;
            }
        }

        // Default to HTTP if none of the above conditions are met
        return false;
    }


    /**
     * Translate a given key using the language files with caching.
     *
     * This method retrieves the translation for the provided key from the
     * language files. If the key is not found, it returns a formatted version
     * of the key by replacing underscores with spaces and capitalizing the first letter.
     *
     * @param string $key The key to be translated.
     * @param  mixed $label   sprint_f label
     *
     * @return string The translated string or the formatted key if not found.
     */
    public static function translate($key, $label = ''): string
    {
        static $lang = null; // Static variable to cache the language array

        $locale = self::userLocale();

        // Load the language file if not already loaded
        if ($lang === null) {
            $langFilePath = TOUR_GUIDE_RESOURCES_DIR . "/language/$locale/default.php";

            if (file_exists($langFilePath)) {
                // Include the file and assign the $lang variable defined in it
                include($langFilePath); // This will populate the $lang variable in the current scope
            }

            // If the language file doesn't define $lang, fallback to an empty array
            if (!isset($lang) || !is_array($lang)) {
                $lang = [];
            }
        }

        $line = $lang[trim($key)] ?? $key;

        if (is_array($label) && count($label) > 0) {
            $line = vsprintf($line, $label);
        } else {

            try {
                $line = sprintf($line, $label);
            } catch (\ValueError | \ArgumentCountError $e) {
            }
        }

        return $line;
    }


    public static function session($key, $value = null)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if ($value === null) {
            return isset($_SESSION[$key]) ? $_SESSION[$key] : null;
        }

        if ($value === false) {
            unset($_SESSION[$key]);
            return null;
        }

        $_SESSION[$key] = $value;
        return $value;
    }

    public static function setupSessionActive(): bool
    {
        return !empty(self::getSetupSessionId());
    }

    public static function getSetupSessionId(): int
    {
        return (int)self::session('tour_guide_setup');
    }

    public static function setSetupSessionId($tourId)
    {
        return self::session('tour_guide_setup', $tourId);
    }

    public static function supportedLanguages(): array
    {
        return TourGuideTranslator::getLanguages();
    }

    public static function getLocaleFromLanguage($language): string
    {
        $supported_languages = TourGuideTranslator::getLanguages();
        foreach ($supported_languages as $key => $row) {
            if (strtolower($row['name']) == $language) {
                return $row['code'];
            }
        }
        return 'en';
    }

    public static function systemLocale(): string
    {
        return 'en';
    }

    public static function activeUserLocale(): string
    {
        return 'en';
    }

    public static function userRoles(): array
    {
        return ["Admin", "Staff", "Client", "Guest"];
    }

    public static function activeUserRole(): string
    {
        return 'Guest';
    }

    public static function activeUser(): array
    {
        return [
            'locale' => self::systemLocale(), // i.e 'en'
            'first_name' => '',
            'last_name' => '',
            'email' => '',
            'id' => '',
            'role' => self::activeUserRole(),
            'finished_tours' => [],
            'last_view' => [
                'tour' => 0,
                'stpe' => 0
            ]
        ];
    }

    public static function asset($path): string
    {
        $docRoot = $_SERVER['DOCUMENT_ROOT'] ?? false;
        if ($docRoot) {
            $path = realpath(TOUR_GUIDE_RESOURCES_DIR) . "/assets/$path";
            $path = str_replace($docRoot, '', $path);
        }
        return self::baseUrl($path);
    }


    public static function encode($value, $doubleEncode = true): string
    {
        if ($value instanceof \BackedEnum) {
            $value = $value->value;
        }

        return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', $doubleEncode);
    }
}