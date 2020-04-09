<?php
/**
 * Created by PhpStorm.
 * User: valentinyelchenko
 * Date: 4/25/17
 * Time: 1:06 PM
 */

namespace Ogxone\Utils\ApplicationMeta;

/**
 * Class ApplicationEnv
 * @package Ogxone\Utils\ApplicationMeta
 */
final class ApplicationEnv
{
    /**
     * @var string
     */
    private $currentEnvironment;

    /**
     * @var array
     */
    private $environments;

    /**
     * Initialized environments list with attributes
     *
     * @param array $config
     * @return ApplicationEnv
     * @throws \UnexpectedValueException
     */
    public static function fromArray(Array $config)
    {
        // creation objects of this type from outside is restricted

        $environments       = [];
        $environmentNames   = [];

        $availableEnvironments  = $config['environments'] ?? false;
        $currentEnvironment     = $config['current_environment'] ?? false;

        if (!$availableEnvironments || !$currentEnvironment) {
            throw new \UnexpectedValueException(sprintf(
                'Invlalid environment configuration `%s`. current_environment and environments key are required',
                json_encode($config)
            ));
        }

        // preparing environments configuration
        foreach ((array)$availableEnvironments as $item) {
            // extracting fields
            $name          = $item['name'] ?? null;
            $isCacheable   = $item['is_cacheable'] ?? null;
            $isDebuggable  = $item['is_debuggable'] ?? null;
            $isDevelopment = $item['is_development'] ?? false;
            $isProduction  = $item['is_production'] ?? false;
            $shortName     = $item['short_name'] ?? $name;

            // validating fields
            if (is_null($name) || is_null($isCacheable) || is_null($isDebuggable)) {
                throw new \UnexpectedValueException(sprintf(
                    'Invlalid environment configuration `%s`',
                    json_encode($item)
                ));
            }

            // adding environment

            $name = (string)$name;
            $environments[$name] = [
                'name'           => $name,
                'is_cacheable'   => (bool)$isCacheable,
                'is_debuggable'  => (bool)$isDebuggable,
                'is_development' => (bool)$isDevelopment,
                'is_production' => (bool)$isProduction,
                'short_name'     => $shortName
            ];
            $environmentNames[] = $name;
        }

        if (!isset($environments[$currentEnvironment])) {
            throw new \UnexpectedValueException(sprintf(
                'Unknown environment `%s`',
                $currentEnvironment
            ));
        }

        return new self($currentEnvironment, $environments);
    }

    /**
     * Restricts cloning of this object
     * @throws \BadMethodCallException
     */
    public function __clone()
    {
        // cloning is restricted
        throw new \BadMethodCallException(sprintf(
            'Method call `%s` is restricted',
            __METHOD__
        ));
    }

    /**
     * Creates validated env list from the array
     *
     * Env constructor.
     * @param string $currentEnvironment
     * @param array $environments
     */
    private function __construct(string $currentEnvironment, Array $environments)
    {
        $this->currentEnvironment = $currentEnvironment;
        $this->environments     = $environments;
    }

    /**
     * Will compose the list of environments starting from production to the current
     * after that optionally will append local config
     *
     * @param bool $applyLocal
     * @return array
     */
    public function getParentsWithSelf($applyLocal = false) : array
    {
        $configs = [];
        foreach ($this->environments as $env) {
            $configs[] = $env['name'];
            if ($this->currentEnvironment == $env['name']) {
                break;
            }
        }

        if ($applyLocal) {
            $configs[] = 'local';
        }

        return $configs;
    }

    /**
     * @return string
     */
    public function getCurrentEnv() : string
    {
        return $this->currentEnvironment;
    }

    /**
     * @return string
     */
    public function getCurrentShortEnv() : string    
    {
        return $this->environments[$this->currentEnvironment]['short_name'] ?? $this->currentEnvironment;
    }

    /**
     * @return bool
     */
    public function isCacheableEnv() : bool
    {
        return $this->environments[$this->currentEnvironment]['is_cacheable'];
    }

    /**
     * @return mixed
     */
    public function isDebuggableEnv() : bool
    {
        return $this->environments[$this->currentEnvironment]['is_debuggable'];
    }

    /**
     * @return mixed
     */
    public function isDevelopmentEnv() : bool
    {
        return $this->environments[$this->currentEnvironment]['is_development'];
    }

    /**
     * @return mixed
     */
    public function isProductionEnv() : bool
    {
        return $this->environments[$this->currentEnvironment]['is_production'];
    }
}