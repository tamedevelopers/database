<?php

declare(strict_types=1);

namespace builder\Database\Capsule;

class Str{
    
    /**
     * Replace the first occurrence of a substring in a string.
     *
     * @param  string  $search   The substring to search for.
     * @param  string  $replace  The replacement substring.
     * @param  string  $subject  The original string.
     * @return string  The modified string.
     */
    public static function replaceFirst($search, $replace, $subject)
    {
        // Find the position of the first occurrence of the search string
        $pos = strpos($subject, $search);

        // If a match is found, replace that portion of the subject string
        if ($pos !== false) {
            $subject = substr_replace($subject, $replace, $pos, strlen($search));
        }

        // Return the modified subject string
        return $subject;
    }
    
    /**
     * Replace the last occurrence of a substring in a string.
     *
     * @param  string  $search   The substring to search for.
     * @param  string  $replace  The replacement substring.
     * @param  string  $subject  The original string.
     * @return string  The modified string.
     */
    public static function replaceLast($search, $replace, $subject)
    {
        // Find the position of the first occurrence of the search string
        $pos = strrpos($subject, $search);

        // If a match is found, replace that portion of the subject string
        if ($pos !== false) {
            $subject = substr_replace($subject, $replace, $pos, strlen($search));
        }

        // Return the modified subject string
        return $subject;
    }

    /**
     * Convert a string to lowercase.
     * @param string|null $value
     * 
     * @return string
     */
    public static function lower(?string $value = null)
    {
        return trim(strtolower((string) $value));
    }

    /**
     * Convert a string to uppercase.
     * @param string|null $value
     * 
     * @return string
     */
    public static function upper(?string $value = null)
    {
        return trim(strtolower((string) $value));
    }

    /**
     * Convert a string to camel case.
     * @param string|null $value
     * 
     * @return string
     */
    public static function camelCase(?string $value = null)
    {
        return preg_replace('/([a-z])([A-Z])/', '$1_$2', trim((string) $value));
    }

    /**
     * Get the plural form of an English word.
     *
     * @param  string  $value
     * @return string
     */
    public static function pluralize(?string $value = null)
    {
        $value = (string) $value;
        if (strlen($value) === 1) {
            return $value;
        }

        // Pluralization rules for common cases
        $rules = [
            '/(s)tatus$/i'                   => '$1tatuses',
            '/(quiz)$/i'                     => '$1zes',
            '/^(ox)$/i'                      => '$1en',
            '/([m|l])ouse$/i'                => '$1ice',
            '/(matr|vert|ind)ix|ex$/i'       => '$1ices',
            '/(x|ch|ss|sh)$/i'               => '$1es',
            '/([^aeiouy]|qu)y$/i'            => '$1ies',
            '/(hive)$/i'                     => '$1s',
            '/(?:([^f])fe|([lr])f)$/i'       => '$1$2ves',
            '/(shea|lea|loa|thie)f$/i'       => '$1ves',
            '/sis$/i'                        => 'ses',
            '/([ti])um$/i'                   => '$1a',
            '/(tomat|potat|ech|her|vet)o$/i' => '$1oes',
            '/(bu)s$/i'                      => '$1ses',
            '/(alias)$/i'                    => '$1es',
            '/(octop)us$/i'                  => '$1i',
            '/(ax|test)is$/i'                => '$1es',
            '/(us)$/i'                       => '$1es',
            '/([^s]+)$/i'                    => '$1s',
        ];

        foreach ($rules as $pattern => $replacement) {
            if (preg_match($pattern, $value)) {
                return preg_replace($pattern, $replacement, $value);
            }
        }

        // Default case: append 's' to the word
        return $value . 's';
    }

}