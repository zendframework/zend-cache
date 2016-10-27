<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Cache\Storage;

use Zend\EventManager\Event as BaseEvent;

class Event extends BaseEvent
{
    /**
     * Constructor
     *
     * Accept a storage adapter and its parameters.
     *
     * @param  string           $name Event name
     * @param  StorageInterface $storage
     * @param  array            $params
     */
    public function __construct($name, StorageInterface $storage, array $params = null)
    {
        parent::__construct($name, $storage, $params);
    }

    /**
     * Set the event target/context
     *
     * @param  StorageInterface $target
     * @return Event
     * @see    Zend\EventManager\Event::setTarget()
     */
    public function setTarget($target)
    {
        return $this->setStorage($target);
    }

    /**
     * Alias of setTarget
     *
     * @param  StorageInterface $storage
     * @return Event
     * @see    Zend\EventManager\Event::setTarget()
     */
    public function setStorage(StorageInterface $storage)
    {
        $this->target = $storage;
        return $this;
    }

    /**
     * Alias of getTarget
     *
     * @return StorageInterface
     */
    public function getStorage()
    {
        return $this->getTarget();
    }

    /**
     * {@inheritdoc}
     *
     * Overwritten for performance reasons as the storage adapter events will handle
     * params as plain arrays only.
     *
     * @param  string $name
     * @param  mixed  $default
     * @return mixed
     */
    public function getParam($name, $default = null)
    {
        if (array_key_exists($name, $this->params)) {
            return $this->params[$name];
        }

        return $default;
    }

    /**
     * {@inheritdoc}
     *
     * Overwritten for performance reasons as the storage adapter events will handle
     * params as plain arrays only.
     *
     * @param  string $name
     * @param  mixed  $value
     */
    public function setParam($name, $value)
    {
        $this->params[$name] = $value;
    }
}
