<?php

namespace App\Entities\Cast;

use CodeIgniter\Entity\Cast\BaseCast;

class EtatCaster extends BaseCast
{
    public static function get($value, array $params = [])
    {
        $value = self::convertValue($value);
        if (gettype($value) === 'integer') {
            return $params[$value] ?? null;
        }
        return $value;
    }

    public static function set($value, array $params = [])
    {
        $value = self::convertValue($value);
        if (gettype($value) === 'string') {
            return array_search($value, $params);
        }
        return $value;
    }

    private static function convertValue($value)
    {
        if ($value == 0) {
            return (int)$value;
        } else {
            $valueNum = (int)$value;
            if (strlen($value) >= 1 && $valueNum) {
                return $valueNum;
            }
        }
        return (string)$value;
    }
}
