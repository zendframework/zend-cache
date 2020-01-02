<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Cache;

use Traversable;
use Zend\EventManager\EventsCapableInterface;
use Zend\ServiceManager\ServiceManager;
use Zend\Stdlib\ArrayUtils;

/**
 * @deprecated static factories are deprecated as of zendframework 2.9 and may be removed in future major versions.
 */
abstract class StorageFactory
{
    /**
     * Plugin manager for loading adapters
     *
     * @var null|Storage\AdapterPluginManager
     */
    protected static $adapters = null;

    /**
     * Plugin manager for loading plugins
     *
     * @var null|Storage\PluginManager
     */
    protected static $plugins = null;

    /**
     * The storage factory
     * This can instantiate storage adapters and plugins.
     *
     * @param array|Traversable $config
     * @return Storage\StorageInterface
     * @throws Exception\InvalidArgumentException
     * @deprecated static factories are deprecated as of zendframework 2.9 and may be removed in future major versions.
     */
    public static function factory($config)
    {
        trigger_error(sprintf(
            '%s is deprecated; please use %s::get instead',
            __METHOD__,
            Storage\AdapterPluginManager::class
        ), E_USER_DEPRECATED);

        if ($config instanceof Traversable) {
            $config = ArrayUtils::iteratorToArray($config);
        }

        if (! is_array($config)) {
            throw new Exception\InvalidArgumentException(
                'The factory needs an associative array '
                . 'or a Traversable object as an argument'
            );
        }

        // instantiate the adapter
        if (! isset($config['adapter'])) {
            throw new Exception\InvalidArgumentException('Missing "adapter"');
        }

        $adapterName = $config['adapter'];
        $adapterOptions = [];
        if (is_array($config['adapter'])) {
            if (! isset($config['adapter']['name'])) {
                throw new Exception\InvalidArgumentException('Missing "adapter.name"');
            }

            $adapterName    = $config['adapter']['name'];
            $adapterOptions = isset($config['adapter']['options']) ? $config['adapter']['options'] : [];
        }
        if (isset($config['options'])) {
            $adapterOptions = ArrayUtils::merge($adapterOptions, $config['options']);
        }

        $adapter = self::adapterFactory((string) $adapterName, $adapterOptions);

        // add plugins
        if (isset($config['plugins'])) {
            if (! $adapter instanceof EventsCapableInterface) {
                throw new Exception\RuntimeException(sprintf(
                    "The adapter '%s' doesn't implement '%s' and therefore can't handle plugins",
                    get_class($adapter),
                    EventsCapableInterface::class
                ));
            }

            if (! is_array($config['plugins'])) {
                throw new Exception\InvalidArgumentException(
                    'Plugins needs to be an array'
                );
            }

            foreach ($config['plugins'] as $k => $v) {
                $pluginPrio = 1; // default priority

                if (is_string($k)) {
                    if (! is_array($v)) {
                        throw new Exception\InvalidArgumentException(
                            "'plugins.{$k}' needs to be an array"
                        );
                    }
                    $pluginName    = $k;
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
                    $pluginName    = $v;
                    $pluginOptions = [];
                }

                $plugin = static::pluginFactory($pluginName, $pluginOptions);
                if (! $adapter->hasPlugin($plugin)) {
                    $adapter->addPlugin($plugin, $pluginPrio);
                }
            }
        }

        return $adapter;
    }

    /**
     * Instantiate a storage adapter
     *
     * @param  string|Storage\StorageInterface                  $adapterName
     * @param  array|Traversable|Storage\Adapter\AdapterOptions $options
     * @return Storage\StorageInterface
     * @throws Exception\RuntimeException
     * @deprecated static factories are deprecated as of zendframework 2.9 and may be removed in future major versions.
     */
    public static function adapterFactory($adapterName, $options = [])
    {
        trigger_error(sprintf(
            '%s is deprecated; please use %s::get instead',
            __METHOD__,
            Storage\AdapterPluginManager::class
        ), E_USER_DEPRECATED);

        $adapter = $adapterName;

        if (! $adapterName instanceof Storage\StorageInterface) {
            $adapter = static::getAdapterPluginManager()->get($adapterName);
        }

        if ($options) {
            $adapter->setOptions($options);
        }

        return $adapter;
    }

    /**
     * Get the adapter plugin manager
     *
     * @return Storage\AdapterPluginManager
     * @deprecated static factories are deprecated as of zendframework 2.9 and may be removed in future major versions.
     */
    public static function getAdapterPluginManager()
    {
        trigger_error(sprintf(
            '%s is deprecated',
            __METHOD__
        ), E_USER_DEPRECATED);

        if (static::$adapters === null) {
            static::$adapters = new Storage\AdapterPluginManager(new ServiceManager);
        }
        return static::$adapters;
    }

    /**
     * Change the adapter plugin manager
     *
     * @param  Storage\AdapterPluginManager $adapters
     * @return void
     * @deprecated static factories are deprecated as of zendframework 2.9 and may be removed in future major versions.
     */
    public static function setAdapterPluginManager(Storage\AdapterPluginManager $adapters)
    {
        trigger_error(sprintf(
            '%s is deprecated',
            __METHOD__
        ), E_USER_DEPRECATED);

        static::$adapters = $adapters;
    }

    /**
     * Resets the internal adapter plugin manager
     *
     * @return void
     * @deprecated static factories are deprecated as of zendframework 2.9 and may be removed in future major versions.
     */
    public static function resetAdapterPluginManager()
    {
        trigger_error(sprintf(
            '%s is deprecated',
            __METHOD__
        ), E_USER_DEPRECATED);

        static::$adapters = null;
    }

    /**
     * Instantiate a storage plugin
     *
     * @param string|Storage\Plugin\PluginInterface     $pluginName
     * @param array|Traversable|Storage\Plugin\PluginOptions $options
     * @return Storage\Plugin\PluginInterface
     * @throws Exception\RuntimeException
     * @deprecated static factories are deprecated as of zendframework 2.9 and may be removed in future major versions.
     */
    public static function pluginFactory($pluginName, $options = [])
    {
        trigger_error(sprintf(
            '%s is deprecated; please use %s::get instead',
            __METHOD__,
            Storage\PluginManager::class
        ), E_USER_DEPRECATED);

        $plugin = $pluginName;
        if (! $pluginName instanceof Storage\Plugin\PluginInterface) {
            $plugin = self::getPluginManager()->get($pluginName);
        }

        if ($options) {
            if (! $options instanceof Storage\Plugin\PluginOptions) {
                $options = new Storage\Plugin\PluginOptions($options);
            }
            $plugin->setOptions($options);
        }

        return $plugin;
    }

    /**
     * Get the plugin manager
     *
     * @return Storage\PluginManager
     * @deprecated static factories are deprecated as of zendframework 2.9 and may be removed in future major versions.
     */
    public static function getPluginManager()
    {
        trigger_error(sprintf(
            '%s is deprecated',
            __METHOD__
        ), E_USER_DEPRECATED);

        if (static::$plugins === null) {
            static::$plugins = new Storage\PluginManager(new ServiceManager);
        }
        return static::$plugins;
    }

    /**
     * Change the plugin manager
     *
     * @param  Storage\PluginManager $plugins
     * @return void
     * @deprecated static factories are deprecated as of zendframework 2.9 and may be removed in future major versions.
     */
    public static function setPluginManager(Storage\PluginManager $plugins)
    {
        trigger_error(sprintf(
            '%s is deprecated',
            __METHOD__
        ), E_USER_DEPRECATED);

        static::$plugins = $plugins;
    }

    /**
     * Resets the internal plugin manager
     *
     * @return void
     * @deprecated static factories are deprecated as of zendframework 2.9 and may be removed in future major versions.
     */
    public static function resetPluginManager()
    {
        trigger_error(sprintf(
            '%s is deprecated',
            __METHOD__
        ), E_USER_DEPRECATED);

        static::$plugins = null;
    }
}
