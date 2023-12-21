<?php

namespace App\Entities\Cast;

use CodeIgniter\Entity\Cast\BaseCast;

use function PHPUnit\Framework\isNull;

class LinkCaster extends BaseCast
{
    public static function get($value, array $params = [])
    {
        if ($value === null) {
            $value = null;
        } elseif (!self::isLink($value)) {
            $value = base_url($value);
        }

        return $value;
    }

    /**
     * Determines if the given text is a valid link.
     *
     * @param string $text The text to check.
     * @return bool Returns true if the text is a valid link, false otherwise.
     */
    private static function isLink($text)
    {
        // Regular expression to match URLs
        $pattern = '/^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/';

        // Check if the text matches the pattern
        if (preg_match($pattern, $text)) {
            return true;
        } else {
            return false;
        }
    }
}
