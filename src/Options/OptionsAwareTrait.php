<?php
/**
 * Created by PhpStorm.
 * User: ogxone
 * Date: 12/15/15
 * Time: 10:18 AM
 */

namespace Ogxone\Utils\Options;

/**
 * Trait OptionsAwareTrait
 * @package Ogxone\Utils\Options
 */
trait OptionsAwareTrait
{
    /**
     * @var OptionsInterface
     */
    protected $_options;

    /**
     * @return OptionsInterface
     */
    public function getOptions(): OptionsInterface
    {
        return $this->_options;
    }

    /**
     * @param OptionsInterface $options
     */
    public function setOptions(OptionsInterface $options)
    {
        $this->_options = $options;
    }
}
