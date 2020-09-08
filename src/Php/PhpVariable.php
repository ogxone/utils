<?php
/**
 * Created by PhpStorm.
 * User: valentinyelchenko
 * Date: 9/13/18
 * Time: 9:36 PM
 */

namespace Ogxone\Utils\Php;

/**
 * Class PhpVariable
 * @package Ogxone\Utils\Php
 */
class PhpVariable
{
    /**
     * @param $object
     * @return bool
     */
    public static function isEmpty($object) : bool
    {
        if (method_exists($object, 'isEmpty')) {
            return $object->isEmpty();
        }

        return empty($object);
    }

    /**
     * @param string $name
     * @param $rawValue
     * @return mixed
     */
    public static function parseFromRawInput(string $name, $rawValue)
    {
        if (\substr($name, -5) === '_LIST') {
            parse_str($rawValue, $out);
            return $out;
        } elseif (substr($name, -4) === '_OBJ') {
            $out = \json_decode($rawValue, true);
            if (JSON_ERROR_NONE !== \json_last_error()) {
                throw new \RuntimeException(sprintf(
                    'Failed to decode key %s. Json error: %s',
                    $name,
                    \json_last_error_msg()
                ));
            }

            return $out;
        }

        return $rawValue;
    }
}