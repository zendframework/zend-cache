<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Cache\Storage\Plugin;

use ArrayObject;
use stdClass;
use Zend\Cache;
use Zend\Cache\Storage\Capabilities;
use Zend\Cache\Storage\Event;
use Zend\Cache\Storage\PostEvent;
use Zend\EventManager\Test\EventListenerIntrospectionTrait;

/**
 * @group      Zend_Cache
 * @covers Zend\Cache\Storage\Plugin\Serializer<extended>
 */
class SerializerTest extends CommonPluginTest
{
    use EventListenerIntrospectionTrait;

    /**
     * The storage adapter
     *
     * @var \Zend\Cache\Storage\Adapter\AbstractAdapter
     */
    protected $_adapter;

    public function setUp()
    {
        $this->_adapter = $this->getMockForAbstractClass('Zend\Cache\Storage\Adapter\AbstractAdapter');
        $this->_options = new Cache\Storage\Plugin\PluginOptions();
        $this->_plugin  = new Cache\Storage\Plugin\Serializer();
        $this->_plugin->setOptions($this->_options);
    }

    public function testAddPlugin()
    {
        $this->_adapter->addPlugin($this->_plugin, 100);

        // check attached callbacks
        $expectedListeners = [
            'getItem.post'        => 'onReadItemPost',
            'getItems.post'       => 'onReadItemsPost',

            'setItem.pre'         => 'onWriteItemPre',
            'setItems.pre'        => 'onWriteItemsPre',
            'addItem.pre'         => 'onWriteItemPre',
            'addItems.pre'        => 'onWriteItemsPre',
            'replaceItem.pre'     => 'onWriteItemPre',
            'replaceItems.pre'    => 'onWriteItemsPre',
            'checkAndSetItem.pre' => 'onWriteItemPre',

            'incrementItem.pre'   => 'onIncrementItemPre',
            'incrementItems.pre'  => 'onIncrementItemsPre',
            'decrementItem.pre'   => 'onDecrementItemPre',
            'decrementItems.pre'  => 'onDecrementItemsPre',

            'getCapabilities.post' => 'onGetCapabilitiesPost',
        ];

        $events = $this->_adapter->getEventManager();
        foreach ($expectedListeners as $eventName => $expectedCallbackMethod) {
            $listeners = $this->getArrayOfListenersForEvent($eventName, $events);

            // event should attached only once
            $this->assertSame(1, count($listeners));

            // check expected callback method
            $cb = array_shift($listeners);
            $this->assertArrayHasKey(0, $cb);
            $this->assertSame($this->_plugin, $cb[0]);
            $this->assertArrayHasKey(1, $cb);
            $this->assertSame($expectedCallbackMethod, $cb[1]);

            // check expected priority
            if (substr($eventName, -4) == '.pre') {
                $this->assertListenerAtPriority($cb, 100, $eventName, $events);
            } else {
                $this->assertListenerAtPriority($cb, -100, $eventName, $events);
            }
        }
    }

    public function testRemovePlugin()
    {
        $this->_adapter->addPlugin($this->_plugin);
        $this->_adapter->removePlugin($this->_plugin);

        // no events should be attached
        $this->assertEquals(0, count($this->getEventsFromEventManager($this->_adapter->getEventManager())));
    }

    public function testSerializeOnWriteItemPre()
    {
        $key      = 'testKey';
        $value    = 123;
        $valueSer = serialize($value);

        $args  = new ArrayObject(['key' => $key, 'value' => $value]);
        $event = new Event('setItem.pre', $this->_adapter, $args);
        $this->_plugin->onWriteItemPre($event);

        $this->assertFalse($event->propagationIsStopped(), 'Event propagation has been stopped');
        $this->assertSame($valueSer, $event->getParam('value'), 'Value was not serialized');
        $this->assertSame($key, $event->getParam('key'), 'Missing or changed key');
    }

    public function testUnserializeOnReadItemPost()
    {
        $key      = 'testKey';
        $value    = 123;
        $valueSer = serialize($value);
        
        $args  = new ArrayObject(['key' => $key, 'success'  => true, 'casToken' => null]);
        $event = new PostEvent('getItem.post', $this->_adapter, $args, $valueSer);
        $this->_plugin->onReadItemPost($event);

        $this->assertFalse($event->propagationIsStopped(), 'Event propagation has been stopped');
        $this->assertSame($value, $event->getResult(), 'Result was not unserialized');
    }

    public function testDontUnserializeOnReadMissingItemPost()
    {
        $key      = 'testKey';
        $value    = null;

        $args  = new ArrayObject(['key' => $key]);
        $event = new PostEvent('getItem.post', $this->_adapter, $args, $value);
        $this->_plugin->onReadItemPost($event);

        $this->assertFalse($event->propagationIsStopped(), 'Event propagation has been stopped');
        $this->assertSame($value, $event->getResult(), 'Missing item was unserialized');
    }

    public function testUnserializeOnReadItemsPost()
    {
        $values = ['key1' => serialize(123), 'key2' => serialize(456)];
        $args   = new ArrayObject(['keys' => array_keys($values) + ['missing']]);
        $event  = new PostEvent('getItems.post', $this->_adapter, $args, $values);
        $this->_plugin->onReadItemsPost($event);

        $this->assertFalse($event->propagationIsStopped(), 'Event propagation has been stopped');

        $values = $event->getResult();
        $this->assertSame(123, $values['key1'], "Item 'key1' was not unserialized");
        $this->assertSame(456, $values['key2'], "Item 'key2' was not unserialized");
        $this->assertArrayNotHasKey('missing', $values, 'Missing item should not be present in the result');
    }

    public function testOnGetCapabilitiesPostOverwritesSupportedDatatypes()
    {
        $baseCapabilities = new Capabilities($this->_adapter, new stdClass, [
            'supportedDatatypes' => [
                'NULL'     => false,
                'boolean'  => false,
                'integer'  => false,
                'double'   => false,
                'string'   => true,
                'array'    => false,
                'object'   => false,
                'resource' => true,
            ]
        ]);

        $event = new PostEvent('getCapabilities.post', $this->_adapter, new ArrayObject(), $baseCapabilities);
        $this->_plugin->onGetCapabilitiesPost($event);

        $this->assertFalse($event->propagationIsStopped(), 'Event propagation has been stopped');
        $this->assertSame([
            'NULL'     => true,
            'boolean'  => true,
            'integer'  => true,
            'double'   => true,
            'string'   => true,
            'array'    => true,
            'object'   => 'object',
            'resource' => false,
        ], $event->getResult()->getSupportedDatatypes(), 'Unexpected supported datatypes');
    }
}
