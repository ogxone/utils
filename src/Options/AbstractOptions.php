<?php
/**
 * Created by PhpStorm.
 * User: ogxone
 * Date: 01.10.15
 * Time: 11:41
 */

namespace Ogxone\Utils\Options;

/**
 * Class AbstractOptions
 * @package Ogxone\Utils\Options
 */
abstract class AbstractOptions implements OptionsInterface
{
    use OptionsTrait;

    /**
     * AbstractOptions constructor.
     * @param array $options
     */
    public function __construct(Array $options)
    {
        $this->setOptions($this->getDefaultOptions());
        $this->setOptions($options, true);
        $this->validate();
    }

    /**
     * @return array
     */
    protected function getDefaultOptions()
    {
        return [];
    }

    /**
     *
     */
    protected function validate()
    {
    }
}