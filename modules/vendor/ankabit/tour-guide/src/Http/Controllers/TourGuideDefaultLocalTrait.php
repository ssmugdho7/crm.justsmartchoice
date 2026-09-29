<?php

namespace TourGuide\Http\Controllers;

use TourGuide\Http\TourGuideServiceTrait;

/**
 * Trait TourGuideDefaultLocalTrait
 *
 * Provides utility methods for controllers related to handling AJAX requests,
 * staff permissions, redirection, view loading, and alert messaging within the TourGuide module.
 */
trait TourGuideDefaultLocalTrait
{
    use TourGuideServiceTrait;

    /**
     * Determines if the current request is an AJAX request.
     * 
     * @return bool Returns true if the request is made via AJAX, otherwise false.
     */
    public function isAJAX()
    {
        // Check if the request was made via XMLHttpRequest
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * Placeholder method to check if the current staff can perform a specific action.
     *
     * @param string $action The action to check permissions for.
     * @return bool Always returns false as it is not yet implemented.
     */
    public function staffCan($action)
    {
        // This method is currently a placeholder and should be implemented in the controller
        throw new \Exception("Staff Can is not implemented", 1);
    }

    /**
     * Sets a session-based alert message.
     *
     * @param string $type The type of alert (e.g., success, error, info).
     * @param string $message The alert message content.
     */
    public function setAlert($type, $message)
    {
        // Use helper to store alert message in the session
        tourGuideHelper()->session($type, $message);
    }

    /**
     * Redirects the user to a specified URL.
     *
     * @param string $url The destination URL.
     */
    public function redirect($url)
    {
        // Send HTTP header to perform redirection
        header("Location: $url");
        exit; // Ensure script termination after redirect
    }

    /**
     * Retrieves the current URI string of the request.
     *
     * @return string The URI of the current request or an empty string if unavailable.
     */
    public function uriString()
    {
        // Return the current request URI
        return $_SERVER['REQUEST_URI'] ?? '';
    }

    /**
     * Loads a specific view file and passes data to it.
     *
     * @param string $view The view file to load (relative to the views directory).
     * @param array $data Optional array of data to be extracted and made available to the view.
     */
    public function loadView($view, $data = [])
    {
        // Define the full path to the view file
        $fullViewPath = TOUR_GUIDE_RESOURCES_DIR . 'views/' . $view;

        // Extract the data array to individual variables
        extract($data);

        // Check if the view file exists, then include it
        if (file_exists($fullViewPath)) {
            include($fullViewPath);
        }
    }

    /**
     * Ensures that the current staff has permission to perform a specific action.
     *
     * @param string $action The action to verify permissions for.
     * @throws \Exception If the method is not implemented or permission is denied.
     */
    public function ensureStaffCan($action)
    {
        // This method is currently a placeholder and should be implemented based on the consuming controller permission system
        throw new \Exception("Ensure Staff Can is not implemented", 1);
    }
}