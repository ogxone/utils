<?php
/**
 * Created by PhpStorm.
 * User: valentinyelchenko
 * Date: 6/20/18
 * Time: 10:56 PM
 */

namespace Ogxone\Utils\Consul;

use Ogxone\Utils\Php\PhpVariable;
use Ogxone\Utils\Sh\ShVariable;

/**
 * Class ConsulUtils
 * @package Ogxone\Utils\Consul
 */
class ConsulUtils
{
    public const FORMAT_PHP = 1;
    public const FORMAT_SH = 2;

    /**
     * Creates key => value map from the raw consul data
     *
     * @param string $rawData
     * @param int $format
     * @return array
     */
    public static function extractFolderData(string $rawData = '', int $format = self::FORMAT_PHP) : array
    {
        $rawData = $rawData ?: '[]';

        $data = json_decode($rawData, true);
        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new \Error(sprintf('Failed to decode envs data. Error was: %s. Data was: %s', json_last_error_msg(), $rawData));
        }

        $data = (array)$data;
        $envs = [];

        foreach ($data as $datum) {
            $value = $datum['Value'];
            $key = $datum['Key'];

            if (empty($value)) {
                // this is a folder
                continue;
            }

            $envName = basename($key);

            if (empty($key)) {
                // env name cannot be computed
                continue;
            }

            $envs[$envName] = self::parseInputAccordingToFormat($format, $envName, $value);
        }

        return $envs;
    }

    private static function parseInputAccordingToFormat(int $format, string $var, string $value)
    {
        switch ($format) {
            case self::FORMAT_SH:
                return ShVariable::parseFromRawInput($var, base64_decode($value));
            case self::FORMAT_PHP:
            default:
                return PhpVariable::parseFromRawInput($var, base64_decode($value));
        }
    }

    /**
     * Creates key => value map from the raw consul data
     *
     * @param string $rawData
     * @return array
     */
    public static function extractFolderDataAsSh(string $rawData = '[]') : array
    {
        return self::extractFolderData($rawData, self::FORMAT_SH);
    }
}
