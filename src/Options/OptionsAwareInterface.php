<?php
/**
 * Created by PhpStorm.
 * User: ogxone
 * Date: 12/15/15
 * Time: 10:16 AM
 */

namespace Ogxone\Utils\Options;


/**
 * Interface OptionsAwareInterface
 * @package Ogxone\Utils\Options
 */
interface OptionsAwareInterface
{
    /**
     * @return mixed
     */
    public function getOptions();

    /**
     * @param OptionsInterface $options
     * @return mixed
     */
    public function setOptions(OptionsInterface $options);
}
