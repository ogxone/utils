<?php
/**
 * Created by PhpStorm.
 * User: valentinyelchenko
 * Date: 10/3/18
 * Time: 4:30 PM
 */

namespace Ogxone\Utils\Sh;

use Ogxone\Utils\Php\PhpVariable;

/**
 * Class Variable
 * @package Ogxone\Utils\Sh
 */
class ShVariable
{
    /**
     * @param array $bashVars
     * @return array|null
     */
    public static function parseVariablesFromArray(array $bashVars) : ?array
    {
        // retrieving environment data
        $config = @array_reduce($bashVars, function($c, $i){
            $i = explode('=', $i, 2);
            $c[$i[0]] = PhpVariable::parseFromRawInput($i[0], $i[1]);
            return $c;
        }, []);

        return $config;
    }


    /**
     * @param string $name
     * @param $rawValue
     * @return mixed
     */
    public static function parseFromRawInput(string $name, $rawValue)
    {
        if (substr($name, -4) === '_OBJ') {
            return sprintf("$(echo %s)", trim($rawValue, '\''));
        }

        return $rawValue;
    }
}