<?php
/**
 * Created by PhpStorm.
 * User: valentinyelchenko
 * Date: 9/28/18
 * Time: 5:38 PM
 */

namespace Ogxone\Utils\Options;

/**
 * Trait MutatorTrait
 * @package Ogxone\Utils\Options
 */
trait MutatorTrait
{
    /**
     * @var array the list of generated mutators
     */
    private static $mutators = [];

    /**
     * @param string $fieldName
     * @return string
     */
    protected function getGetter(string $fieldName): string
    {
        return $this->createMutators($fieldName)['getter'];
    }

    /**
     * @param string $fieldName
     * @return string
     */
    protected function getSetter(string $fieldName): string
    {
        return $this->createMutators($fieldName)['setter'];
    }

    /**
     * @param string $fieldName
     * @return array
     */
    private function createMutators(string $fieldName): array
    {
        if (!isset(self::$mutators[$fieldName])) {
            $methodBody = str_replace(' ', '', ucwords(str_replace('_', ' ', $fieldName)));

            self::$mutators[$fieldName] = [
                'getter' => 'get' . $methodBody,
                'setter' => 'set' . $methodBody,
            ];
        }

        return self::$mutators[$fieldName];
    }
}
