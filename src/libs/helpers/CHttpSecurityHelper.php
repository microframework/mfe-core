<?php namespace mfe\core\libs\helpers;

use InvalidArgumentException;

/**
 * Class CHttpSecurityHelper
 *
 * @package mfe\core\libs\helpers
 */
final class CHttpSecurityHelper
{
    /**
     * Private constructor; non-instantiable.
     */
    private function __construct()
    {
    }

    /**
     * @param string $value
     *
     * @return string
     */
    public static function filter($value)
    {
        $value = (string)$value;
        $length = strlen($value);
        $string = '';
        for ($i = 0; $i < $length; $i++) {
            $ascii = ord($value[$i]);
            if ($ascii === 13) {
                $lf = ord($value[$i + 1]);
                $ws = ord($value[$i + 2]);
                if ($lf === 10 && in_array($ws, [9, 32], true)) {
                    $string .= $value[$i] . $value[$i + 1];
                    $i++;
                }
                continue;
            }
            if (($ascii < 32 && $ascii !== 9) // TAB
                || $ascii === 127 // DEL
                || $ascii > 254 // NULL
            ) {
                continue;
            }
            $string .= $value[$i];
        }
        return $string;
    }

    /**
     * @param string $value
     *
     * @return bool
     */
    public static function isValid($value)
    {
        $value = (string)$value;
        if (preg_match("#(?:(?:(?<!\r)\n)|(?:\r(?!\n))|(?:\r\n(?![ \t])))#", $value)) {
            return false;
        }
        $length = strlen($value);
        for ($i = 0; $i < $length; $i++) {
            $ascii = ord($value[$i]);
            if (127 === $ascii // DELL
                || $ascii > 254 //NULL
                || ($ascii < 32
                    && !in_array($ascii, [
                        9, // TAB
                        10, // LINE FEED
                        13 // RETURN
                    ], true)
                )
            ) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param string $value
     *
     * @throws InvalidArgumentException
     */
    public static function assertValid($value)
    {
        if (!self::isValid($value)) {
            throw new InvalidArgumentException('Invalid header value');
        }
    }

    /**
     * @param mixed $name
     *
     * @throws InvalidArgumentException
     */
    public static function assertValidName($name)
    {
        if (!preg_match('/^[a-zA-Z0-9\'`#$%&*+.^_|~!-]+$/', $name)) {
            throw new InvalidArgumentException('Invalid header name');
        }
    }
}
