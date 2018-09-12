<?php
declare(strict_types=1);

namespace Zend\Cache\Storage;

use SplObjectStorage;
use Zend\EventManager\EventsCapableInterface;

interface PluginCapableInterface extends EventsCapableInterface
{

    public function hasPlugin(Plugin\PluginInterface $plugin): bool;

    public function getPluginRegistry(): SplObjectStorage;
}
