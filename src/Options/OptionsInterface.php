<?php
/**
 * Created by PhpStorm.
 * User: ogxone
 * Date: 12/15/15
 * Time: 10:17 AM
 */

namespace Ogxone\Utils\Options;


/**
 * Interface OptionsInterface
 * @package Ogxone\Utils\Options
 */
interface OptionsInterface
{
    /**
     * @param array $userOptions
     * @param bool $strictMode
     * @param bool $useSetter
     * @return mixed
     */
    public function setOptions(Array $userOptions, bool $strictMode = false, bool $useSetter = true);

    /**
     * @param string $name
     * @param $option
     * @param bool $strictMode
     * @param bool $useSetter
     * @return mixed
     */
    public function setOption(string $name, $option, bool $strictMode = false, bool $useSetter = true);

    /**
     * @return array
     */
    public function getRawOptions(): array ;

    /**
     * @param bool $useGetters
     * @return array
     */
    public function getOptions(bool $useGetters = true);

    /**
     * @param string $name
     * @param bool $strict
     * @param null $default
     * @param bool $useGetter
     * @return mixed
     */
    public function getOption(string $name, bool $strict = false, $default = null, bool $useGetter = true);

    /**
     * @param string $name
     * @param string $type
     * @param bool $strict
     * @param bool|null $default
     * @param bool $useGetter
     * @return mixed
     */
    public function getOptionOfType(string $name, string $type, bool $strict = true, $default = null, bool $useGetter = true);

    /**
     * @param string $name
     * @param bool $useGetter
     * @return bool
     */
    public function hasOption(string $name, bool $useGetter = true) : bool;

    /**
     * @param string $name
     * @return bool
     */
    public function isOptionExists(string $name) : bool;
}