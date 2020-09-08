<?php
/**
 * Created by PhpStorm.
 * User: valentinyelchenko
 * Date: 10/9/18
 * Time: 11:22 AM
 */

namespace Ogxone\UtilsTest\Sh;

use Ogxone\Utils\Php\PhpVariable;
use PHPUnit\Framework\TestCase;

/**
 * Class ShVariableTest
 * @package Ogxone\UtilsTest\Sh
 */
class PhpVariableTest extends TestCase
{
    /**
     * @test
     */
    public function ensureSimpleVariablesParsingIsWorking()
    {
        $parsed = PhpVariable::parseFromRawInput('CONTENT_SERVICE_VERSION', 134);

        $this->assertEquals(134, $parsed);
    }

    /**
     * @test
     */
    public function ensureListVariablesParsingIsWorking()
    {
        $parsed = PhpVariable::parseFromRawInput('EDITION_LIST', 'ru=ru&es=es&ua=ua');

        $this->assertEquals(['ru' => 'ru', 'es' => 'es', 'ua' => 'ua'], $parsed);

        $parsed = PhpVariable::parseFromRawInput('COUNTRIES_LIST', '?Italy=Italy&US=US');
        $this->assertEquals(['?Italy' => 'Italy', 'US' => 'US'], $parsed);
    }

    /**
     * @test
     */
    public function ensureObjVariablesParsingIsWorking()
    {
        $parsed = PhpVariable::parseFromRawInput('MEMCACHE_HOSTS_OBJ', '[["msv6-content-v3.4wvmqu.0001.use1.cache.amazonaws.com", 11211],["msv6-content-v3-2.4wvmqu.0001.use1.cache.amazonaws.com", 11211]]');
        $this->assertEquals($parsed, [['msv6-content-v3.4wvmqu.0001.use1.cache.amazonaws.com', 11211], ['msv6-content-v3-2.4wvmqu.0001.use1.cache.amazonaws.com', 11211]]);

        $parsed = PhpVariable::parseFromRawInput('MEMCACHE_HOSTS', '[["msv6-content-v3.4wvmqu.0001.use1.cache.amazonaws.com", 11211],["msv6-content-v3-2.4wvmqu.0001.use1.cache.amazonaws.com", 11211]]');
        $this->assertEquals($parsed, '[["msv6-content-v3.4wvmqu.0001.use1.cache.amazonaws.com", 11211],["msv6-content-v3-2.4wvmqu.0001.use1.cache.amazonaws.com", 11211]]');

        $parsed = PhpVariable::parseFromRawInput('MEMCACHE_HOSTS_OBJ', '{"opt1": "opt1val", "opt2": 123}');
        $this->assertEquals($parsed, ['opt1' => 'opt1val', 'opt2' => 123]);
    }

    /**
     * @test
     * @expectedException \RuntimeException
     */
    public function ensureThatBadJsonWillTriggerException()
    {
        PhpVariable::parseFromRawInput('MEMCACHE_HOSTS_OBJ', '{"opt1","opt1val", "opt2": 123}');
    }
}