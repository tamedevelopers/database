<?php

declare(strict_types=1);

namespace builder\Database\Capsule;

use builder\Database\Schema\Traits\ExpressionTrait;


class Forge {

    use ExpressionTrait;

    /**
     * If the given value is not an array and not null, wrap it in one.
     *
     * @param  mixed  $value
     * @return array
     */
    public static function wrap($value)
    {
        if (is_null($value)) {
            return [];
        }

        return is_array($value) ? $value : [$value];
    }

    /**
     * Get the first element of an array.
     *
     * @param  array  $array
     * @return mixed|null
     */
    public static function head($array = null)
    {
        return isset($array[0]) ? $array[0] : null;
    }

    /**
     * Get the last element of an array.
     *
     * @param array $array
     * @return mixed|null
     */
    public static function last($array = null)
    {
        if (!is_array($array)) {
            return null;
        }

        return end($array);
    }

    /**
     * Merge the binding arrays into a single array.
     *
     * @param array $bindings
     * @return array
     */
    public static function mergeBinding(array $bindings)
    {
        // Extract the values from the associative array
        $values = array_values($bindings);
        
        // Merge all the arrays into a single array
        $mergedBindings = array_merge(...$values);
        
        // Return the merged bindings
        return $mergedBindings;
    }

    /**
     * Flatten a multidimensional array into a single-dimensional array.
     *
     * @param array $array The multidimensional array to flatten.
     * @return array The flattened array.
     */
    public static function flattenValue(array $array)
    {
        $result = [];

        foreach ($array as $value) {
            if (is_array($value)) {
                $result = array_merge($result, self::flattenValue($value));
            } else {
                $result[] = $value;
            }
        }

        return $result;
    }

    /**
     * Exclude specified keys from an array.
     *
     * @param array $array The input array
     * @param mixed $keys The key(s) to exclude
     * @return array The filtered array
     */
    public static function exceptArray($array, $keys)
    {
        // Convert single key to an array
        if (!is_array($keys)) {
            $keys = [$keys];
        }
        
        // Use array_filter to keep only the elements with keys not present in $keys
        return array_filter($array, function ($key) use ($keys) {
            return !in_array($key, $keys);
        }, ARRAY_FILTER_USE_KEY);
    }


}