<?php

namespace ZendTest\Cache\Service;

use Interop\Container\ContainerInterface;
use function method_exists;
use PHPUnit\Framework\TestCase;
use Zend\Cache\Service\StorageFactoryFactory;
use Zend\Cache\Storage\AdapterPluginManager;
use Zend\Cache\Storage\PluginManager;
use Zend\Cache\Storage\StorageFactory;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceManager;

class StorageFactoryFactoryTest extends TestCase
{

    /**
     * @var StorageFactoryFactory
     */
    private $factory;

    protected function setUp()
    {
        parent::setUp();
        $this->factory = new StorageFactoryFactory();
    }


    public function testFactoryCreatesServiceV2()
    {
        if (method_exists(new ServiceManager(), 'configure')) {
            $this->markTestSkipped('Test is only needed in ZF2');
        }

        $adapterPluginManager = $this->prophesize(AdapterPluginManager::class);
        $pluginManager = $this->prophesize(PluginManager::class);

        $container = $this->prophesize(ServiceLocatorInterface::class);
        $container->get(AdapterPluginManager::class)->willReturn($adapterPluginManager);

        $container->get(PluginManager::class)->willReturn($pluginManager);

        $instance = $this->factory->createService($container->reveal());
        $this->assertInstanceOf(StorageFactory::class, $instance);
    }

    public function testFactoryCreatesServiceV3()
    {
        if (! method_exists(new ServiceManager(), 'configure')) {
            $this->markTestSkipped('Test is only needed in ZF3');
        }

        $adapterPluginManager = $this->prophesize(AdapterPluginManager::class);
        $pluginManager = $this->prophesize(PluginManager::class);

        $container = $this->prophesize(ContainerInterface::class);
        $container->get(AdapterPluginManager::class)->willReturn($adapterPluginManager);

        $container->get(PluginManager::class)->willReturn($pluginManager);

        $instance = $this->factory->__invoke($container->reveal(), StorageFactory::class);

        $this->assertInstanceOf(StorageFactory::class, $instance);
    }
}
