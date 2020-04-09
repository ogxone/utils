<?php
/**
 * Created by PhpStorm.
 * User: valentinyelchenko
 * Date: 4/25/17
 * Time: 5:45 PM
 */

namespace Ogxone\Utils\ApplicationMeta;

/**
 * Class ApplicationMeta
 * @package Ogxone\Utils\ApplicationMeta
 */
final class ApplicationMeta
{
    /**
     * @var ApplicationEnv
     */
    private $applicationEnv;
    /**
     * @var Envs
     */
    private $envs;

    /**
     * @var static
     */
    private static $instance;

    /**
     * Factory method
     * Creates ApplicationEnv, Env and initializes self
     *
     * @param array $config
     */
    public static function configure(Array $config)
    {
        // object of this type permitted to be initialized exactly once
        if (null !== self::$instance) {
            throw new \UnexpectedValueException(sprintf(
                '`%s` can be configured only once',
                __CLASS__
            ));
        }

        // create application env
        $applicationEnvConfig = $config['application_env'] ?? null;

        if (!$applicationEnvConfig) {
            throw new \UnexpectedValueException;
        }

        $applicationEnv = ApplicationEnv::fromArray((array)$applicationEnvConfig);

        // create environments wrapper
        $envs = (array)($config['envs'] ?? []);
        $envsIsCacheable = $config['envs_is_cacheable'] ?? $applicationEnv->isCacheableEnv();
        $envs = new Envs($envs, $envsIsCacheable);

        self::$instance =  new self($applicationEnv, $envs);

        // apply php settings
        $phpSettings = $config['php']['settings'] ?? null;

        if ($phpSettings) {
            self::applyPhpSettings(
                PhpSettings::createForEnvironment(self::isDebugMode(), (array)$phpSettings)
            );
        }
    }

    /**
     * @return self
     */
    private static function instance() : self
    {
        if (null === self::$instance) {
            throw new \UnexpectedValueException(sprintf(
                '`%s` have not been initialized',
                __CLASS__
            ));
        }

        return self::$instance;
    }

    /**
     * @return Envs
     */
    public static function envs() : Envs
    {
        return self::instance()->envs;
    }

    /**
     * @return ApplicationEnv
     */
    public static function applicationEnv() : ApplicationEnv
    {
        return self::instance()->applicationEnv;
    }

    /**
     * @return bool
     */
    public static function isDevelopmentMode() : bool
    {
        return self::applicationEnv()->isDevelopmentEnv();
    }

    /**
     * @return bool
     */
    public static function isProductionMode() : bool
    {
        return self::applicationEnv()->isProductionEnv();
    }

    /**
     * @return bool
     */
    public static function isDebugMode() : bool
    {
        return self::applicationEnv()->isDebuggableEnv() || self::envs()->is('debug_mode');
    }

    /**
     * @return mixed
     */
    public static function isUseCaching() : bool
    {
        return self::envs()->has('USE_CACHING')
            ? self::envs()->is('USE_CACHING')
            : self::applicationEnv()->isCacheableEnv();
    }

    /**
     * @return bool
     */
    public static function isVerboseErrors() : bool
    {
        return self::isDebugMode() || self::envs()->is('verbose_errors');
    }

    /**
     * @return bool
     * @deprecated
     */
    public static function isPreproduction() : bool
    {
        throw new \Exception(sprintf('%s is Deprecated', __METHOD__));
        return self::envs()->is('IS_PREPRODUCTION');
    }

    /**
     * @return bool
     */
    public static function isSecure(): bool
    {
        $serverPort = getenv('HTTP_X_FORWARDED_PORT') ?: getenv('SERVER_PORT');
        return $serverPort == 443;
    }

    public static function isCli() : bool
    {
        return false !== strpos(php_sapi_name(), 'cli');
    }

    /**
     * @param string|null $subAppName
     * @return string
     */
    public static function getBuildId(string $subAppName = null) : string
    {
        $buildFileName = $subAppName ? sprintf('%s_build_id_path', $subAppName) : 'build_id_path';

        $buildIdFile = self::envs()->getFilePath($buildFileName);

        $buildId = $buildIdFile ? (string)include $buildIdFile : -1;

        return $buildId;
    }

    /**
     * ApplicationMeta constructor.
     * @param ApplicationEnv $applicationEnv
     * @param Envs $envs
     */
    private function __construct(ApplicationEnv $applicationEnv, Envs $envs)
    {
        $this->applicationEnv   = $applicationEnv;
        $this->envs             = $envs;
    }

    /**
     * @param array $phpSettings
     */
    private static function applyPhpSettings(Array $phpSettings)
    {
        foreach ($phpSettings as $name => $value) {
            ini_set($name, $value);
        }
    }
}