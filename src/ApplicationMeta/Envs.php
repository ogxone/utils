<?php
/**
 * Created by PhpStorm.
 * User: valentinyelchenko
 * Date: 4/25/17
 * Time: 1:06 PM
 */

namespace Ogxone\Utils\ApplicationMeta;

use Ogxone\Utils\Php\ErrorHandler;

/**
 * Class Envs manipulates with enviromental variables
 * Optionally allows user to provide additional variables
 *
 * @package Ogxone\Utils\ApplicationMeta
 */
final class Envs
{
    private const DEFAULT_CACHE_FILE_PATH = 'data/envs.cache.php';

    /**
     * @var array
     */
    private $appEnvs;

    /**
     * Loads env variables from the cache file or compile it using provided config otherwise
     * Stores new envs if caching is turned on
     *
     * Envs constructor.
     * @param array $config
     * @param bool $useCaching
     * @throws \ErrorException
     */
    public function __construct(Array $config, bool $useCaching)
    {
        $cacheFilePath = $config['cache_file'] ?? self::DEFAULT_CACHE_FILE_PATH;
        if ($useCaching) {
            if (file_exists($cacheFilePath)) {
                $envs = $this->includeCachedEnvs($cacheFilePath);
            } else {
                $envs = $this->generageEnvs($config);
                $this->saveEnvs($cacheFilePath, $envs);
            }
        } else {
            $envs = $this->generageEnvs($config);
        }

        $this->appEnvs = $envs;
    }

    /**
     * @param string $cacheFilePath
     * @return mixed
     * @throws \ErrorException
     */
    private function includeCachedEnvs(string $cacheFilePath) : array
    {
        ErrorHandler::start(E_ALL);
        $envs = include $cacheFilePath;
        $e = ErrorHandler::stop();

        if ($e) {
            throw new \ErrorException('Exception occured while retrieving cached env vars. See previous', 1, 1, __FILE__, __LINE__, $e);
        }

        return $envs;
    }

    /**
     * @param array $config
     * @return array
     * @throws \ErrorException
     */
    private function generageEnvs(array $config) : array
    {
        if (!$this->isNewStypeEnvsConfig($config)) {
            return $config;
        }

        $envs = $config['values'] ?? [];
        $sources = $config['sources'] ?? [];

        ErrorHandler::start(E_ALL);
        $sourcesData = [];
        foreach ($sources as $source) {
            if (file_exists($source)) {
                $sourcesData[] = include $source;
            }
        }

        $envs = array_merge($envs, ...$sourcesData);

        $e = ErrorHandler::stop();

        if ($e) {
            throw new \ErrorException('Exception occured while generating env vars. See previous', 1, 1, __FILE__, __LINE__, $e);
        }

        return $envs;
    }

    /**
     * Keeps older initialization code valid
     *
     * @param array $config
     * @return bool
     */
    private function isNewStypeEnvsConfig(array $config) : bool
    {
        return isset($config['values']) || isset($config['sources']);
    }

    /**
     * @param string $cacheFilePath
     * @param array $envs
     * @throws \ErrorException
     */
    private function saveEnvs(string $cacheFilePath, array $envs) : void
    {
        ErrorHandler::start(E_ALL);
        file_put_contents($cacheFilePath, sprintf('<?php return %s;' , var_export($envs, true)));
        $e = ErrorHandler::stop();

        if ($e) {
            throw new \ErrorException('Exception occured while saving env vars. See previous', 1, 1, __FILE__, __LINE__, $e);
        }
    }

    /**
     * @param $name
     * @return string
     */
    public function get($name, $append = '') : string
    {
        return $this->getEnvInternal($name)  . $append;
    }

    /**
     * @param $name
     * @return array
     */
    public function getArr($name) : array
    {
        return $this->getEnvInternal($name);
    }

    /**
     * @param $name
     * @return bool
     */
    public function has($name) : bool
    {
        return $this->getEnvInternal($name) !== false;
    }

    /**
     * @param $name
     * @return bool
     */
    public function is($name) : bool
    {
        return $this->getEnvInternal($name) == true;
    }

    /**
     * @param $name
     * @param string $subpath
     * @return string
     */
    public function getDirPath($name, string $subpath = '')
    {
        $path = $this->getEnvInternal($name);

        if (!$path || !is_dir($path = $path . $subpath)) {
            return false;
        }

        return $path;
    }

    /**
     * @param string $name
     * @param string|null $file
     * @return string
     */
    public function getFilePath(string $name, string $file = '')
    {
        $path = $this->getEnvInternal($name);

        if (!$path || !file_exists($path = $path . $file)) {
            return false;
        }

        return $path;
    }

    /**
     * @param $name
     * @return array|false|mixed|string
     */
    private function getEnvInternal($name)
    {
        $uname = strtoupper($name);
        return $this->appEnvs[$name] ?? $this->appEnvs[$uname] ?? getenv($uname);
    }
}