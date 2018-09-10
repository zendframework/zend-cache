<?php
/**
 * @see       https://github.com/zendframework/zend-cache for the canonical source repository
 * @copyright Copyright (c) 2018 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-cache/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\Cache\Psr\CacheItemPool;

use PHPUnit\Framework\TestCase;
use Zend\Cache\Psr\CacheItemPool\CacheItemPoolDecorator;
use Zend\Cache\StorageFactory;
use Zend\Stdlib\ErrorHandler;

class MemoryIntegrationTest extends TestCase
{
    protected function setUp()
    {
        ErrorHandler::start(E_USER_DEPRECATED);

        parent::setUp();
    }

    protected function tearDown()
    {
        ErrorHandler::clean();
        parent::tearDown();
    }

    /**
     * The memory adapter calculates the TTL on reading which violates PSR-6
     *
     * @expectedException \Zend\Cache\Psr\CacheItemPool\CacheException
     */
    public function testAdapterNotSupported()
    {
        $storage = StorageFactory::adapterFactory('memory');
        new CacheItemPoolDecorator($storage);
    }
}
