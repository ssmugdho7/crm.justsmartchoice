<?php

namespace TourGuide\Helper;

use TourGuide\TourGuideDB;

/**
 * Interface for Tour Guide Helper.
 *
 * This interface defines the methods that any Tour Guide implementation must provide.
 */
interface TourGuideHelperInterface
{
    /**
     * Get the database instance.
     *
     * @return TourGuideDB|null The database instance, or null if not applicable.
     */
    public static function dbInstance(): ?TourGuideDB;

    /**
     * Generate the admin URL for the Tour Guide.
     *
     * @param string $path The specific path to append to the admin URL.
     * @return string The full admin URL.
     */
    public static function adminUrl($path = ''): string;

    /**
     * Generate the user URL for the Tour Guide.
     *
     * @param string $path The specific path to append to the user URL.
     * @return string The full user URL.
     */
    public static function userUrl($path = ''): string;

    /**
     * Generate the base URL for the application.
     *
     * @param string $path Optional path to append to the base URL.
     * @return string The constructed base URL.
     */
    public static function baseUrl($path = ''): string;

    /**
     * Translate a given key using the translation function.
     *
     * @param string $key The translation key.
     * @param  mixed $label   sprint_f label
     * @return string The translated string.
     */
    public static function translate($key, $label = ''): string;

    /**
     * Manage session data for the specified key.
     *
     * @param string $key The session key to access.
     * @param mixed|null $value The value to set; if null, returns the current value.
     * @return mixed The current value or null if not set.
     */
    public static function session($key, $value = null);

    /**
     * Determine is there is an active setup session i.e the build is in setup mode.
     *
     * @return boolean
     */
    public static function setupSessionActive(): bool;

    /**
     * Get the active tour setup id
     */
    public static function getSetupSessionId(): int;

    /**
     * Set setup session id
     *
     * @param int|false $tourId
     * @return mixed
     */
    public static function setSetupSessionId($tourId);

    /**
     * Get the available languages for the Tour Guide.
     *
     * @return array An array of available languages. i.e [['code'=>'en', 'name'=>'english']...]
     */
    public static function supportedLanguages(): array;

    /**
     * Get the default app local
     *
     * @return string The default language code i.e fr.
     */
    public static function systemLocale(): string;


    /**
     * Get the current user local
     *
     * @return string The user language code i.e fr.
     */
    public static function activeUserLocale(): string;


    /**
     * Get the locale i.e 'en' from a given language i.e 'english'
     *
     * @param string $language
     * @return string
     */
    public static function getLocaleFromLanguage($language): string;

    /**
     * Get the user roles available in the Tour Guide system.
     *
     * @return array An array of user roles.
     */
    public static function userRoles(): array;

    /**
     * Get the active user role.
     *
     * @return string The active user role.
     */
    public static function activeUserRole(): string;

    /**
     * Get the active user information.
     *
     * @return array An array representing the active user.
     */
    public static function activeUser(): array;

    /**
     * Get the asset URL for the specified path.
     *
     * @param string $path The asset path.
     * @return string The asset URL.
     */
    public static function asset($path): string;

    /**
     * Encode HTML special characters in a string.
     *
     * @param  bool  $doubleEncode
     * @return string
     */
    public static function encode($value, $doubleEncode = true): string;
}