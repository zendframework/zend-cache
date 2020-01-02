<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2018 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Cache\Storage;

use SplObjectStorage;
use Zend\EventManager\EventsCapableInterface;

interface PluginCapableInterface extends EventsCapableInterface
{
    /**
     * Check if a plugin is registered
     *
     * @param  Plugin\PluginInterface $plugin
     * @return bool
     */
    public function hasPlugin(Plugin\PluginInterface $plugin);

    /**
     * Return registry of plugins
     *
     * @return SplObjectStorage
     */
    public function getPluginRegistry();
}
