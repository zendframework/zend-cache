<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2018 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Cache\Storage;

use Traversable;
use Zend\Cache\Exception;
use Zend\EventManager\EventsCapableInterface;
use Zend\Stdlib\ArrayUtils;

final class StorageFactory
{

    /**
     * @var AdapterPluginManager
     */
    private $adapters;

    /**
     * @var PluginManager
     */
    private $plugins;

    public function __construct(AdapterPluginManager $adapters, PluginManager $plugins)
    {
        $this->adapters = $adapters;
        $this->plugins = $plugins;
    }

    /**
     * Creates a storage adapter by parsing the `caches` configuration.
     *
     * @param array|Traversable $config
     *
     * @return StorageInterface
     * @throws Exception\InvalidArgumentException
     */
    public function createFromCachesConfig($config)
    {
        if ($config instanceof Traversable) {
            $config = ArrayUtils::iteratorToArray($config);
        }

        if (!is_array($config)) {
            throw new Exception\InvalidArgumentException(
                'The factory needs an associative array '
                . 'or a Traversable object as an argument'
            );
        }

        // instantiate the adapter
        if (!isset($config['adapter'])) {
            throw new Exception\InvalidArgumentException('Missing "adapter"');
        }
        $adapterName = $config['adapter'];
        $adapterOptions = [];
        if (is_array($config['adapter'])) {
            if (!isset($config['adapter']['name'])) {
                throw new Exception\InvalidArgumentException('Missing "adapter.name"');
            }

            $adapterName = $config['adapter']['name'];
            $adapterOptions = isset($config['adapter']['options']) ? $config['adapter']['options'] : [];
        }
        if (isset($config['options'])) {
            $adapterOptions = array_merge($adapterOptions, $config['options']);
        }

        $pluginConfiguration = [];

        // add plugins
        if (isset($config['plugins'])) {
            if (!is_array($config['plugins'])) {
                throw new Exception\InvalidArgumentException(
                    'Plugins needs to be an array'
                );
            }

            $pluginConfiguration = $config['plugins'];
        }

        return $this->create($adapterName, $adapterOptions, $pluginConfiguration);
    }

    /**
     * Creates a storage adapter and attaches plugins if needed.
     *
     * @param string $adapterName
     * @param array  $options
     * @param array  $pluginConfiguration
     *
     * @return StorageInterface
     */
    public function create($adapterName, array $options = [], array $pluginConfiguration = [])
    {
        $adapter = $this->adapters->get($adapterName, $options);

        if (empty($pluginConfiguration)) {
            return $adapter;
        }

        if (!$adapter instanceof EventsCapableInterface) {
            throw new Exception\RuntimeException(sprintf(
                "The adapter '%s' doesn't implement '%s' and therefore can't handle plugins",
                get_class($adapter),
                EventsCapableInterface::class
            ));
        }

        foreach ($pluginConfiguration as $k => $v) {
            $pluginPrio = 1; // default priority

            if (is_string($k)) {
                if (!is_array($v)) {
                    throw new Exception\InvalidArgumentException(
                        "'plugins.{$k}' needs to be an array"
                    );
                }
                $pluginName = $k;
                $pluginOptions = $v;
            } elseif (is_array($v)) {
                if (!isset($v['name'])) {
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

            $plugin = $this->plugins->get($pluginName, $pluginOptions);
            if (!$adapter->hasPlugin($plugin)) {
                $adapter->addPlugin($plugin, $pluginPrio);
            }

            return $adapter;
        }
    }
}
