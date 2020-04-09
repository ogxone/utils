<?php
/**
 * Created by PhpStorm.
 * User: ogxone
 * Date: 10.10.16
 * Time: 15:13
 */

namespace Ogxone\Utils\Options;

/**
 * Class DefaultOptions
 * @package Ogxone\Utils\Options
 */
class DefaultOptions implements OptionsInterface
{
    use OptionsTrait;

    /**
     * @param array $options
     * @return static
     */
    public static function fromArray(Array $options)
    {
        $thisObj = new static;
        $thisObj->setOptions($options);

        return $thisObj;
    }
}
