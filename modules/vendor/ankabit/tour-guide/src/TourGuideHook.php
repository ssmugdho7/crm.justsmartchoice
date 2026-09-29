<?php

namespace TourGuide;

class TourGuideHook
{
    private static $filters = [];

    /**
     * Adds a filter to the filter manager.
     *
     * @param string $tag The name of the filter.
     * @param callable $callback The callback function to execute.
     */
    public static function addFilter(string $tag, callable $callback): void
    {
        self::$filters[$tag][] = $callback;
    }

    /**
     * Applies a filter to a given value.
     *
     * @param string $tag The name of the filter.
     * @param mixed  $value The value to filter.
     * @param mixed  ...$args Additional arguments to pass to the callback.
     * @return mixed The filtered value.
     */
    public static function applyFilter(string $tag, $value, ...$args)
    {
        if (isset(self::$filters[$tag])) {
            foreach (self::$filters[$tag] as $callback) {
                // Pass the value and additional args to the callback
                $value = call_user_func($callback, $value, ...$args);
            }
        }

        return $value;
    }
}