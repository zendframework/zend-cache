<?php
/**
 * @see       https://github.com/zendframework/zend-cache for the canonical source repository
 * @copyright Copyright (c) 2018 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-cache/blob/master/LICENSE.md New BSD License
 */

use Zend\Cache\Storage\AdapterPluginManager;
use Zend\ServiceManager\ServiceManager;

call_user_func(function () {
    $target = method_exists(ServiceManager::class, 'configure')
        ? AdapterPluginManager\AdapterPluginManagerV3Polyfill::class
        : AdapterPluginManager\AdapterPluginManagerV2Polyfill::class;

    class_alias($target, AdapterPluginManager::class);
});
