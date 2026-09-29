<?php

namespace TourGuide\Helper;

/**
 * Helper class for Tour Guide functionality.
 *
 * This class acts as a service locator to provide a centralized way to access Tour Guide Helper methods.
 */
class TourGuideHelper
{
    /**
     * @var TourGuideInterface The instance of the Tour Guide implementation.
     */
    private static $instance;

    /**
     * Get the instance of the Tour Guide implementation.
     *
     * If an instance does not exist, it creates a new DefaultTourGuide instance.
     *
     * @return TourGuideInterface The Tour Guide implementation instance.
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new TourGuideDefaultHelper();
        }
        return self::$instance;
    }

    /**
     * Set the instance of the class.
     *
     * This method is used to set a static instance of the class.
     * It allows external code to provide an instance that can be used
     * by the class methods.
     *
     * @param mixed $instance An instance of the class or any compatible type.
     * 
     * @return void
     *
     * @throws InvalidArgumentException If the instance does not implement the required interface.
     */
    public static function setInstance($instance): void
    {
        // Validate that the instance implements the required interface
        if (!$instance instanceof TourGuideHelperInterface) {
            throw new \InvalidArgumentException('The provided instance must implement TourGuideHelperInterface.');
        }

        self::$instance = $instance; // Assign the provided instance to the static property
    }
}
