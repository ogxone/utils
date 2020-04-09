<?php
/**
 * Created by PhpStorm.
 * User: ogxone
 * Date: 20.09.15
 * Time: 13:10
 */

namespace Ogxone\Utils\Options;

/**
 * Trait OptionsTrait
 * @package Ogxone\Utils\Options
 */
trait OptionsTrait
{
    use MutatorTrait;
    /**
     * @var bool if set this object can be initialized only once
     */
    protected $isImmutable = false;

    /**
     * todo this should probably be private
     * @var array
     */
    protected $__options = [];

    /**
     * @var bool detects first call to setOptions
     */
    private $firstCall = true;

    /**
     * Sets bunch of options using setters if found
     *
     * @param array $userOptions
     * @param bool $strictMode
     * @param bool $useSetter
     * @return $this if this is second call of this function and immutable mode was set
     */
    public function setOptions(Array $userOptions, bool $strictMode = false, bool $useSetter = true)
    {
        // ensure that only one call to this methos is allowed if immutable mode was set
        if ($this->isImmutable && !$this->firstCall) {
            throw new \BadMethodCallException(sprintf(
                'Object `%s` marked as immutable and have already been initialized',
                get_called_class()
            ));
        }

        $this->firstCall = false;

        $priorities = $this->priorities();
        // if priorities have been provided
        if ($priorities) {
            // sorting options by priorities
            uksort($userOptions, function ($a, $b) use ($priorities) {
                // for each option key
                // taking it priority from priorities array
                $ka = $priorities[$a] ?? 0;
                $kb = $priorities[$b] ?? 0;

                // and comparing them
                return $ka <=> $kb;
            });
        }

        foreach ($userOptions as $name => $option) {
            $this->setOption($name, $option, $strictMode, $useSetter);
        }
        return $this;
    }

    /**
     * Provides options priorities if options have to be installed in a particular way
     *
     * @return array
     */
    protected function priorities()
    {
        return [];
    }

    /**
     * Sets individual field value optionally using setter
     *
     * @param string $name
     * @param $option
     * @param bool $strict
     * @param bool $useSetter
     * @return $this if immutable mode was switched on
     */
    public function setOption(string $name, $option, bool $strict = false, bool $useSetter = true)
    {
        // ensure that this method cant't be called in immutable mode
        if ($this->isImmutable && !$this->firstCall) {
            throw new \BadMethodCallException(sprintf(
                'Setting individual fields is forbidden in immutable mode in object `%s`',
                get_called_class()
            ));
        }

        // creating setter name
        $setter = $this->getSetter($name);
        if ($useSetter && method_exists($this, $setter)) {
            // calling setter if provided
            $this->callSetter($setter, $option);
        } else if (array_key_exists($name, $this->__options) || !$strict) {
            // or setting as raw field
            $this->__options[$name] = $option;
        } else {
            // setter was not found and strict mode flag was provided
            throw new \UnexpectedValueException(sprintf(
                'Option %s is not allowed in component %s',
                $name,
                get_called_class()
            ));
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getRawOptions(): array
    {
        return $this->__options;
    }

    /**
     * @param bool $useGetters to be deprecated in future
     * @return array
     */
    public function getOptions(bool $useGetters = true)
    {
        $options = $this->getRawOptions();;

        if (false === $useGetters) {
            return $options;
        }

        // get data from methods
        if ($useGetters) {
            $ref = new \ReflectionClass($this);
            $methods = $ref->getMethods(\ReflectionMethod::IS_PUBLIC);

            if ($methods) {
                foreach ($methods as $method) {
                    if (
                        0 === strpos($name = $method->getName(), 'get') &&  // get all getters
                        false === strpos($name, 'getOption') &&             // strip service functions
                        false === strpos($name, 'getRawOptions')
                    ) {
                        try {
                            // getter may perform validation and throw exception
                            // because of that

                            $value = $method->invoke($this);
                        } catch (\Throwable $e) {
                            $value = null;
                        }
                        $options[$this->createOptionNameFromGetter(substr($name, 3))] = $value;
                    }
                }
            }
        }

        return $options;
    }

    /**
     * Get one option from this object optionally using getter
     *
     * @param string $name
     * @param bool $strict
     * @param null $default
     * @param bool $useGetter
     * @return null
     */
    public function getOption(string $name, bool $strict = false, $default = null, bool $useGetter = true)
    {
        $getter = $this->getGetter($name);

        if (
            $useGetter &&
            method_exists($this, $getter) &&
            null !== ($optionValue = $this->callGetter($getter))
        ) {
            return $optionValue;
        } else if (
            isset($this->__options[$name]) &&
            null !== ($optionValue = $this->__options[$name])
        ) {
            return $optionValue;
        } else if ($strict) {
            throw new \UnexpectedValueException(sprintf(
                'Option %s have to be provided in component %s',
                $name,
                get_called_class()
            ));
        } else {
            return $default;
        }
    }

    /**
     * Get one option from this object optionally using getter
     *
     * @param string $name
     * @param string $type
     * @param bool $strict
     * @param bool|null $default
     * @param bool $useGetter
     * @return null
     */
    public function getOptionOfType(string $name, string $type, bool $strict = true, $default = null, bool $useGetter = true)
    {
        $option = $this->getOption($name, $strict, $default, $useGetter);

        switch ($type) {
            case 'bool':
                $type = 'boolean';
            case 'integer':
            case 'string':
            case 'boolean':
            case 'float':
            case 'array':
                $isValidType = gettype($option) == $type;
                break;
            default:
                $isValidType = $option instanceof $type;
        }

        if ($this->hasOption($name, $useGetter) && !$isValidType) {
            throw new \InvalidArgumentException(sprintf(
                'Options %s expected to be of type %s',
                $name,
                $type
            ));
        }

        return $option;
    }

    /**
     * @param $name
     * @param bool $useGetter
     * @return bool
     */
    public function hasOption(string $name, bool $useGetter = true) : bool
    {
        $getter = $this->getGetter($name);

        if (
            $useGetter &&
            method_exists($this, $getter)
        ) {
            return null !== $this->$getter();
        } else {
            return isset($this->__options[$name]);
        }
    }

    public function isOptionExists(string $name) : bool
    {
        $getter = $this->getGetter($name);

        return method_exists($this, $getter) || array_key_exists($name, $this->__options);
    }

    /**
     * Make current object immutable
     *
     * @return void
     */
    public function setImmutable() : void
    {
        $this->isImmutable = true;
    }

    /**
     * @return bool
     */
    public function isImmutable() : bool
    {
        return true === $this->isImmutable;
    }

    /**
     * Resets this object
     */
    public function reset()
    {
        $this->firstCall = true;
        $this->__options = [];
    }

    /**
     * @param string $setter
     * @param $option
     * @throws \UnexpectedValueException if error has occurred during setter execution
     */
    private function callSetter(string $setter, $option)
    {
        try {
            $this->$setter($option);
        } catch (\Throwable $e) {
            throw new \UnexpectedValueException(
                sprintf('
                        Exception has occured while setting option `%s` of `%s`. 
                        Message was: %s. For more info see previous exception',
                    $setter,
                    get_called_class(),
                    $e->getMessage()
                ),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * @param $getter
     * @return mixed
     * @throws \UnexpectedValueException if error has occurred during getter execution
     */
    private function callGetter($getter)
    {
        try {
            return $this->$getter();
        } catch (\Throwable $e) {
            throw new \UnexpectedValueException(
                sprintf('
                        Exception has occured while getting option `%s` of `%s`. 
                        Message was: %s. For more info see previous exception',
                    $getter,
                    get_called_class(),
                    $e->getMessage()
                ),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Creates underscored options name from camel-cased method name
     *
     * @param $name
     * @param string $separator
     * @return string
     */
    private function createOptionNameFromGetter($name, $separator = '_')
    {
        $name = lcfirst($name);
        return strtolower(preg_replace('/([A-Z])/', $separator . '$1', $name));
    }
}