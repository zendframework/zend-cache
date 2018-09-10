<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Cache;

use const E_USER_DEPRECATED;
use ErrorException;
use PHPUnit\Framework\TestCase;
use Zend\Cache;
use Zend\Stdlib\ErrorHandler;

/**
 * @group      Zend_Cache
 * @covers Zend\Cache\PatternFactory
 */
class PatternFactoryTest extends TestCase
{
    public function setUp()
    {
        ErrorHandler::start(E_USER_DEPRECATED);
        Cache\PatternFactory::resetPluginManager();
    }

    public function tearDown()
    {
        Cache\PatternFactory::resetPluginManager();
        ErrorHandler::clean();
    }

    public function testDefaultPluginManager()
    {
        $plugins = Cache\PatternFactory::getPluginManager();
        $this->assertInstanceOf('Zend\Cache\PatternPluginManager', $plugins);
    }

    public function testChangePluginManager()
    {
        $plugins = new Cache\PatternPluginManager(
            $this->getMockBuilder('Interop\Container\ContainerInterface')->getMock()
        );
        Cache\PatternFactory::setPluginManager($plugins);
        $this->assertSame($plugins, Cache\PatternFactory::getPluginManager());
    }

    public function testFactory()
    {
        $pattern1 = Cache\PatternFactory::factory('capture');
        $this->assertInstanceOf('Zend\Cache\Pattern\CaptureCache', $pattern1);

        $pattern2 = Cache\PatternFactory::factory('capture');
        $this->assertInstanceOf('Zend\Cache\Pattern\CaptureCache', $pattern2);

        $this->assertNotSame($pattern1, $pattern2);
    }

    public function testWillTriggerDeprecationError()
    {
        Cache\PatternFactory::factory('capture');
        $error = ErrorHandler::stop();

        $this->assertInstanceOf(ErrorException::class, $error);
        $this->assertSame(E_USER_DEPRECATED, $error->getSeverity());
    }
}
