<?php
/**
 * Created by PhpStorm.
 * User: valentinyelchenko
 * Date: 10/9/18
 * Time: 2:06 PM
 */

namespace Ogxone\UtilsTest\Consul;

use Ogxone\Utils\Consul\ConsulUtils;
use PHPUnit\Framework\TestCase;

/**
 * Class ConsulUtilsTest
 * @package Ogxone\UtilsTest\Consul
 */
class ConsulUtilsTest extends TestCase
{
    private $fixtures;

    protected function setUp()
    {
        $this->fixtures = require __DIR__ . '/fixtures/fixtures.php';
    }

    /**
     * @test
     */
    public function ensureThatExtractingEmptyDataDoesntTriggerException()
    {
        try {
            ConsulUtils::extractFolderData('');
        } catch (\Throwable $e) {
            $this->fail($e->getMessage());
        }

        $this->assertNull($e ?? null);

        try {
            ConsulUtils::extractFolderData('', ConsulUtils::FORMAT_SH);
        } catch (\Throwable $e) {
            $this->fail($e->getMessage());
        }

        $this->assertNull($e ?? null);
    }

    /**
     * @test
     */
    public function ensureSimpleVariablesParsingIsWorking()
    {
        $parsed = ConsulUtils::extractFolderData($this->fixtures['simple_key_value']);

        $this->assertParsedValue('CACHE_CS_HOST', $parsed, '127.0.0.1');
        $this->assertParsedValue('ELASTIC_INDEXER_HOST', $parsed, 'http://vpc-motorsport-qa-p7xqhcsykdwl5sm4y7cxwgutpy.us-east-1.es.amazonaws.com:80');
    }

    /**
     * @test
     */
    public function ensureListVariablesParsingIsWorking()
    {
        $parsed = ConsulUtils::extractFolderData($this->fixtures['list_key_value']);

        $this->assertParsedValue('DB_DSN', $parsed, 'mysql:dbname=ms_v6_q;host=msv6-aurora.cluster-c85uo2p40u0z.us-east-1.rds.amazonaws.com');
        $this->assertParsedValue('DOMAINS', $parsed, 'ua=ua&es=es');
        $this->assertParsedValue('DOMAINS_LIST', $parsed, ['ua' => 'ua', 'es' => 'es']);
    }

    /**
     * @test
     */
    public function ensureObjVariablesParsingIsWorking()
    {
        $parsed = ConsulUtils::extractFolderData($this->fixtures['obj_key_value']);

        $this->assertParsedValue('CACHE_RCS_HOST', $parsed, '127.0.0.1');
        $this->assertParsedValue('MEMCACHE_HOSTS', $parsed, '[["msv6-content-v3.4wvmqu.0001.use1.cache.amazonaws.com", 11211],["msv6-content-v3-2.4wvmqu.0001.use1.cache.amazonaws.com", 11211]]');
        $this->assertParsedValue('MEMCACHE_HOSTS_OBJ', $parsed, [['msv6-content-v3.4wvmqu.0001.use1.cache.amazonaws.com', 11211], ['msv6-content-v3-2.4wvmqu.0001.use1.cache.amazonaws.com', 11211]]);
    }

    /**
     * @test
     * @expectedException \RuntimeException
     */
    public function ensureThatBadJsonWillTriggerException()
    {
        ConsulUtils::extractFolderData($this->fixtures['invalid_obj']);
    }

    private function assertParsedValue(string $key, $array, $expected)
    {
        $this->assertArrayHasKey($key, $array);
        $this->assertEquals($expected, $array[$key]);
    }
}