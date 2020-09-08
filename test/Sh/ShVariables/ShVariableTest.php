<?php
/**
 * Created by PhpStorm.
 * User: valentinyelchenko
 * Date: 10/9/18
 * Time: 11:22 AM
 */

namespace Ogxone\UtilsTest\Sh\ShVariable;

use Ogxone\Utils\Sh\ShVariable;
use PHPUnit\Framework\TestCase;

/**
 * Class ShVariableTest
 * @package Ogxone\UtilsTest\Sh
 */
class ShVariableTest extends TestCase
{
    private $fixtures;

    protected function setUp()
    {
        $this->fixtures = require __DIR__ . '/fixtures.php';
    }

    /**
     * @test
     */
    public function ensureSimpleVariablesParsingIsWorking()
    {
        $parsed = ShVariable::parseVariablesFromArray($this->fixtures['simple_variables']);

        $this->assertCount(3, $parsed);

        $this->assertParsedValue('CACHE_CS_HOST', $parsed, '127.0.0.1');
        $this->assertParsedValue('ELASTIC_INDEXER_HOST', $parsed, '"http://vpc-motorsport-qa-p7xqhcsykdwl5sm4y7cxwgutpy.us-east-1.es.amazonaws.com:80"');
        $this->assertParsedValue('CONTENT_SERVICE_VERSION', $parsed, '134');
    }

    /**
     * @test
     */
    public function ensureListVariablesParsingIsWorking()
    {
        $parsed = ShVariable::parseVariablesFromArray($this->fixtures['list_variables']);

        $this->assertCount(3, $parsed);

        $this->assertParsedValue('DB_DSN', $parsed, 'mysql:dbname=ms_v6_q;host=msv6-aurora.cluster-c85uo2p40u0z.us-east-1.rds.amazonaws.com');
        $this->assertParsedValue('DOMAINS_LIST', $parsed, ['ru' => 'ru', 'es' => 'es', 'ua' => 'ua']);
        $this->assertParsedValue('COUNTRIES_LIST', $parsed, ['?Italy' => 'Italy', 'US' => 'US']);
    }

    /**
     * @test
     */
    public function ensureObjVariablesParsingIsWorking()
    {
        $parsed = ShVariable::parseVariablesFromArray($this->fixtures['obj_variables']);

        $this->assertCount(4, $parsed);

        $this->assertParsedValue('CACHE_RCS_HOST', $parsed, '127.0.0.1');
        $this->assertParsedValue('MEMCACHE_HOSTS', $parsed, '[["msv6-content-v3.4wvmqu.0001.use1.cache.amazonaws.com", 11211],["msv6-content-v3-2.4wvmqu.0001.use1.cache.amazonaws.com", 11211]]');
        $this->assertParsedValue('MEMCACHE_HOSTS_OBJ', $parsed, [['msv6-content-v3.4wvmqu.0001.use1.cache.amazonaws.com', 11211], ['msv6-content-v3-2.4wvmqu.0001.use1.cache.amazonaws.com', 11211]]);
        $this->assertParsedValue('CUSTOM_JSON_OBJ', $parsed, ['opt1' => 'opt1val', 'opt2' => 123]);
    }

    /**
     * @test
     * @expectedException \RuntimeException
     */
    public function ensureThatBadJsonWillTriggerException()
    {
        ShVariable::parseVariablesFromArray($this->fixtures['obj_variables_with_error']);
    }

    /**
     * @test
     */
    public function ensureShVariablesWorksWithJson()
    {
        $parsed = ShVariable::parseFromRawInput('json_OBJ', '[["msv6-content-v3.4wvmqu.0001.use1.cache.amazonaws.com", 11211]]');
        $this->assertEquals('$(echo [["msv6-content-v3.4wvmqu.0001.use1.cache.amazonaws.com", 11211]])', $parsed);

        $parsed = ShVariable::parseFromRawInput('json', '[["msv6-content-v3.4wvmqu.0001.use1.cache.amazonaws.com", 11211]]');
        $this->assertEquals('[["msv6-content-v3.4wvmqu.0001.use1.cache.amazonaws.com", 11211]]', $parsed);
    }
    
    private function assertParsedValue(string $key, $array, $expected)
    {
        $this->assertArrayHasKey($key, $array);
        $this->assertEquals($expected, $array[$key]);
    }
}