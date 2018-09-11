<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2018 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
namespace Zend\Cache\Storage\AdapterPluginManager;

use Zend\Cache\Exception;
use Zend\Cache\Storage\PluginManager;
use Zend\Cache\Storage\StorageInterface;
use Zend\EventManager\EventsCapableInterface;
use Zend\ServiceManager\Exception\InvalidServiceException;
use Zend\Stdlib\ArrayUtils;

/**
 * Trait providing common logic between AdapterPluginManager implementations.
 *
 * Trait does not define properties, as the properties common between the
 * two versions are originally defined in their parent class, causing a
 * resolution conflict.
 */
trait AdapterPluginManagerTrait
{
    /**
     * Validate the plugin is of the expected type (v3).
     *
     * Validates against `$instanceOf`.
     *
     * @param mixed $instance
     * @throws InvalidServiceException
     */
    public function validate($instance)
    {
        if (! $instance instanceof $this->instanceOf) {
            throw new InvalidServiceException(sprintf(
                '%s can only create instances of %s; %s is invalid',
                get_class($this),
                $this->instanceOf,
                (is_object($instance) ? get_class($instance) : gettype($instance))
            ));
        }
    }

    /**
     * Validate the plugin is of the expected type (v2).
     *
     * Proxies to `validate()`.
     *
     * @param mixed $plugin
     * @throws Exception\RuntimeException if invalid
     */
    public function validatePlugin($plugin)
    {
        try {
            $this->validate($plugin);
        } catch (InvalidServiceException $e) {
            throw new Exception\RuntimeException($e->getMessage(), $e->getCode(), $e);
        }
    }

    private function parseOptions(array $options)
    {
        $plugins = [];
        if (array_key_exists('plugins', $options)) {
            $plugins = $options['plugins'] ?: [];
            unset($options['plugins']);
        }

        $adapter = $options;

        if (isset($options['options'])) {
            $adapter = $options['options'];
        }

        if (isset($options['adapter']['options']) && is_array($options['adapter']['options'])) {
            $adapter = ArrayUtils::merge($adapter, $options['adapter']['options']);
        }

        return [$adapter, $plugins];
    }

    /**
     * Attaches plugins by using the provided plugin manager.
     *
     * @param StorageInterface $adapter
     * @param PluginManager    $pluginManager
     * @param array            $plugins
     *
     * @return void
     * @throws Exception\RuntimeException if adapter does not implement `EventsCapableInterface`
     * @throws Exception\InvalidArgumentException if the plugin configuration does not fit specification.
     */
    private function attachPlugins(StorageInterface $adapter, PluginManager $pluginManager, array $plugins)
    {
        if (! $adapter instanceof EventsCapableInterface) {
            throw new Exception\RuntimeException(sprintf(
                "The adapter '%s' doesn't implement '%s' and therefore can't handle plugins",
                get_class($adapter),
                EventsCapableInterface::class
            ));
        }

        foreach ($plugins as $k => $v) {
            $pluginPrio = 1; // default priority

            if (is_string($k)) {
                if (! is_array($v)) {
                    throw new Exception\InvalidArgumentException(
                        "'plugins.{$k}' needs to be an array"
                    );
                }
                $pluginName = $k;
                $pluginOptions = $v;
            } elseif (is_array($v)) {
                if (! isset($v['name'])) {
                    throw new Exception\InvalidArgumentException(
                        "Invalid plugins[{$k}] or missing plugins[{$k}].name"
                    );
                }
                $pluginName = (string) $v['name'];

                if (isset($v['options'])) {
                    $pluginOptions = $v['options'];
                } else {
                    $pluginOptions = [];
                }

                if (isset($v['priority'])) {
                    $pluginPrio = $v['priority'];
                }
            } else {
                $pluginName = $v;
                $pluginOptions = [];
            }

            $plugin = $pluginManager->get($pluginName, $pluginOptions);
            if (! $adapter->hasPlugin($plugin)) {
                $adapter->addPlugin($plugin, $pluginPrio);
            }
        }
    }
}
