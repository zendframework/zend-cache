<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2018 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
namespace Zend\Cache\Storage;

use Zend\Cache\Storage\AdapterPluginManager\AdapterPluginManagerV2Polyfill;
use Zend\Cache\Storage\AdapterPluginManager\AdapterPluginManagerV3Polyfill;
use Zend\ServiceManager\AbstractPluginManager;

/**
 * @see AdapterPluginManagerV2Polyfill
 * @see AdapterPluginManagerV3Polyfill
 */
class AdapterPluginManager extends AbstractPluginManager
{

}
