<?php
/**
 * Created by PhpStorm.
 * User: valentinyelchenko
 * Date: 4/25/17
 * Time: 6:49 PM
 */

namespace Ogxone\Utils\ApplicationMeta;

/**
 * Class PhpSettings
 * @package Ogxone\Utils\ApplicationMeta
 */
class PhpSettings
{
    /**
     * @param bool $isDebugMode
     * @param array $settings
     * @return array
     */
    public static function createForEnvironment(bool $isDebugMode, Array $settings): array
    {
        $configSection = $isDebugMode ? 'debug_mode' : 'production_mode';

        $defaultSettings = (array)($settings['default'] ?? []);
        $sectionSettings = (array)($settings[$configSection] ?? []);

        $settings = array_merge($defaultSettings, $sectionSettings);

        return $settings;
    }
}