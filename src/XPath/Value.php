<?php

namespace Pace\XPath;

use DateTime;

class Value
{
    /**
     * Pace attributes that are string-typed even when the value looks numeric.
     *
     * @var array
     */
    protected static $stringAttributes = [
        'job',
        'customer',
        'vendor',
        'inventoryItem',
        'company',
        'branch',
        'shipTo',
    ];

    /**
     * Format a value for use in an XPath expression.
     *
     * @param mixed $value
     * @param string|null $xpath
     * @param bool $forFunction When true, always quote string values (e.g. contains() arguments).
     * @return string
     */
    public static function format($value, ?string $xpath = null, bool $forFunction = false)
    {
        switch (true) {
            case ($value === null):
                return 'null';

            case ($value instanceof DateTime):
                return sprintf('date( %d, %d, %d )', $value->format('Y'), $value->format('n'), $value->format('j'));

            case (is_int($value)):
            case (is_float($value)):
                return (string) $value;

            case (is_bool($value)):
                return $value ? '\'true\'' : '\'false\'';

            case (is_string($value) && !$forFunction && static::shouldFormatAsNumber($value, $xpath)):
                return strpos($value, '.') !== false ? (string) (float) $value : (string) (int) $value;

            default:
                return static::quoteString((string) $value);
        }
    }

    /**
     * Determine if a string value should be formatted as an unquoted number.
     *
     * @param string $value
     * @param string|null $xpath
     * @return bool
     */
    protected static function shouldFormatAsNumber($value, ?string $xpath)
    {
        if (preg_match('/[A-Za-z]/', $value)) {
            return false;
        }

        if (preg_match('/^0\d+$/', $value)) {
            return false;
        }

        if (!is_numeric($value)) {
            return false;
        }

        $attribute = static::attributeName($xpath);

        if ($attribute !== null && in_array($attribute, static::$stringAttributes, true)) {
            return false;
        }

        return true;
    }

    /**
     * Extract the attribute name from an XPath expression.
     *
     * @param string|null $xpath
     * @return string|null
     */
    protected static function attributeName(?string $xpath)
    {
        if ($xpath === null) {
            return null;
        }

        if (preg_match('/@([^\/\s]+)$/', $xpath, $matches)) {
            return $matches[1];
        }

        return ltrim($xpath, '@');
    }

    /**
     * Quote a string value for XPath.
     *
     * @param string $value
     * @return string
     */
    protected static function quoteString($value)
    {
        if (strpos($value, '"') !== false && strpos($value, "'") === false) {
            return "'$value'";
        }

        if (strpos($value, "'") !== false && strpos($value, '"') === false) {
            return "\"$value\"";
        }

        if (strpos($value, '"') !== false && strpos($value, "'") !== false) {
            $escaped = str_replace("'", "''", $value);

            return "'$escaped'";
        }

        return "\"$value\"";
    }
}
