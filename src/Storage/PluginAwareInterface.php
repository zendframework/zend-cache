<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2018 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Cache\Storage;

use Zend\Cache\Exception;

interface PluginAwareInterface extends PluginCapableInterface
{
    /**
     * Register a plugin
     *
     * @param  Plugin\PluginInterface $plugin
     * @param  int $priority
     * @return StorageInterface
     * @throws Exception\LogicException
     */
    public function addPlugin(Plugin\PluginInterface $plugin, $priority = 1);

    /**
     * Unregister an already registered plugin
     *
     * @param  Plugin\PluginInterface $plugin
     * @return StorageInterface
     * @throws Exception\LogicException
     */
    public function removePlugin(Plugin\PluginInterface $plugin);
}
