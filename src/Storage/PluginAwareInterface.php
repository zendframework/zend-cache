<?php
declare(strict_types=1);

namespace Zend\Cache\Storage;

interface PluginAwareInterface extends PluginCapableInterface
{

    public function addPlugin(Plugin\PluginInterface $plugin, $priority = 1): void;

    public function removePlugin(Plugin\PluginInterface $plugin): void;
}
