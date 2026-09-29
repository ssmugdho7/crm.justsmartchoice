<?php

defined('TOUR_GUIDE_DIR') or define('TOUR_GUIDE_DIR', __DIR__);
defined('TOUR_GUIDE_RESOURCES_DIR') or define('TOUR_GUIDE_RESOURCES_DIR', TOUR_GUIDE_DIR . '/resources');

require_once __DIR__ . '/Libraries/HTMLPurifier/HTMLPurifier.standalone.php';

use TourGuide\Helper\TourGuideHelper;

if (!function_exists('tourGuideHelper')) {
    /**
     * Global helper function to access the Tour Guide functionality.
     *
     * This function provides easy access to the Tour Guide helper instance.
     *
     * @return TourGuideInterface The Tour Guide helper instance.
     */
    function tourGuideHelper()
    {
        return TourGuideHelper::getInstance();
    }
}