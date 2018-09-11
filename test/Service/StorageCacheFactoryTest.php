<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Cache\Service;

use Interop\Container\ContainerInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Zend\Cache\StorageFactory;
use Zend\Cache\Service\StorageCacheFactory;
use Zend\Cache\Storage\AdapterPluginManager;
use Zend\Cache\Storage\Adapter\AbstractAdapter;
use Zend\Cache\Storage\Adapter\Memory;
use Zend\Cache\Storage\PluginManager;
use Zend\Cache\Storage\Plugin\PluginInterface;
use Zend\Cache\Storage\StorageInterface;
use Zend\ServiceManager\Config;
use Zend\ServiceManager\ServiceManager;
use Zend\Stdlib\ErrorHandler;

/**
 * @covers Zend\Cache\Service\StorageCacheFactory
 */
class StorageCacheFactoryTest extends TestCase
{
    protected $sm;

    public function setUp()
    {
        ErrorHandler::start(E_USER_DEPRECATED);
        StorageFactory::resetAdapterPluginManager();
        StorageFactory::resetPluginManager();
        $config = [
            'services' => [
                'config' => [
                    'cache' => [
                        'adapter' => 'Memory',
                        'plugins' => ['Serializer', 'ClearExpiredByFactor'],
                    ]
                ]
            ],
            'factories' => [
                'CacheFactory' => StorageCacheFactory::class
            ]
        ];
        $this->sm = new ServiceManager();
        (new Config($config))->configureServiceManager($this->sm);
    }

    public function tearDown()
    {
        StorageFactory::resetAdapterPluginManager();
        StorageFactory::resetPluginManager();
        ErrorHandler::clean();
    }

    public function testCreateServiceCache()
    {
        $cache = $this->sm->get('CacheFactory');
        $this->assertEquals(Memory::class, get_class($cache));
    }

    public function testSetsFactoryAdapterPluginManagerInstanceOnInvocation()
    {
        $adapter = $this->prophesize(AbstractAdapter::class);
        $adapter->willImplement(StorageInterface::class);
        $adapter->setOptions(Argument::any())->shouldNotBeCalled();
        $adapter->hasPlugin(Argument::any(), Argument::any())->shouldNotBeCalled();
        $adapter->addPlugin(Argument::any(), Argument::any())->shouldNotBeCalled();

        $adapterPluginManager = $this->prophesize(AdapterPluginManager::class);
        $adapterPluginManager->get('Memory')->willReturn($adapter->reveal());

        $container = $this->prophesize(ContainerInterface::class);
        $container->has(AdapterPluginManager::class)->willReturn(true);
        $container->get(AdapterPluginManager::class)->willReturn($adapterPluginManager->reveal());
        $container->has(PluginManager::class)->willReturn(false);

        $container->has('config')->willReturn(true);
        $container->get('config')->willReturn([
            'cache' => [ 'adapter' => 'Memory' ],
        ]);

        $factory = new StorageCacheFactory();
        $this->assertSame($adapter->reveal(), $factory($container->reveal(), 'Cache'));
        $this->assertSame($adapterPluginManager->reveal(), StorageFactory::getAdapterPluginManager());
    }

    public function testSetsFactoryPluginManagerInstanceOnInvocation()
    {
        $plugin = $this->prophesize(PluginInterface::class);
        $plugin->setOptions(Argument::any())->shouldNotBeCalled();

        $pluginManager = $this->prophesize(PluginManager::class);
        $pluginManager->get('Serializer')->willReturn($plugin->reveal());

        $adapter = $this->prophesize(AbstractAdapter::class);
        $adapter->willImplement(StorageInterface::class);
        $adapter->setOptions(Argument::any())->shouldNotBeCalled();
        $adapter->hasPlugin($plugin->reveal(), Argument::any())->willReturn(false);
        $adapter->addPlugin($plugin->reveal(), Argument::any())->shouldBeCalled();

        $adapterPluginManager = $this->prophesize(AdapterPluginManager::class);
        $adapterPluginManager->get('Memory')->willReturn($adapter->reveal());

        $container = $this->prophesize(ContainerInterface::class);
        $container->has(AdapterPluginManager::class)->willReturn(true);
        $container->get(AdapterPluginManager::class)->willReturn($adapterPluginManager->reveal());
        $container->has(PluginManager::class)->willReturn(true);
        $container->get(PluginManager::class)->willReturn($pluginManager->reveal());

        $container->has('config')->willReturn(true);
        $container->get('config')->willReturn([
            'cache' => [
                'adapter' => 'Memory',
                'plugins' => ['Serializer'],
            ],
        ]);

        $factory = new StorageCacheFactory();
        $factory($container->reveal(), 'Cache');
        $this->assertSame($pluginManager->reveal(), StorageFactory::getPluginManager());
    }
}
