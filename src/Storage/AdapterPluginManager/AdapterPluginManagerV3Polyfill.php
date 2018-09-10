<?php

namespace Zend\Cache\Storage\AdapterPluginManager;

use Zend\Cache\Storage\Adapter;
use Zend\Cache\Storage\StorageInterface;
use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\Factory\InvokableFactory;

/**
 * zend-servicemanager v3-compatible plugin manager implementation for cache pattern adapters.
 *
 * Enforces that retrieved adapters are instances of
 * Pattern\PatternInterface. Additionally, it registers a number of default
 * patterns available.
 */
class AdapterPluginManagerV3Polyfill extends AbstractPluginManager
{

    use AdapterPluginManagerTrait;

    protected $aliases = [
        'apc' => Adapter\Apc::class,
        'Apc' => Adapter\Apc::class,
        'APC' => Adapter\Apc::class,
        'apcu' => Adapter\Apcu::class,
        'ApcU' => Adapter\Apcu::class,
        'Apcu' => Adapter\Apcu::class,
        'APCu' => Adapter\Apcu::class,
        'black_hole' => Adapter\BlackHole::class,
        'blackhole' => Adapter\BlackHole::class,
        'blackHole' => Adapter\BlackHole::class,
        'BlackHole' => Adapter\BlackHole::class,
        'dba' => Adapter\Dba::class,
        'Dba' => Adapter\Dba::class,
        'DBA' => Adapter\Dba::class,
        'ext_mongo_db' => Adapter\ExtMongoDb::class,
        'extmongodb' => Adapter\ExtMongoDb::class,
        'ExtMongoDb' => Adapter\ExtMongoDb::class,
        'ExtMongoDB' => Adapter\ExtMongoDb::class,
        'extMongoDb' => Adapter\ExtMongoDb::class,
        'extMongoDB' => Adapter\ExtMongoDb::class,
        'filesystem' => Adapter\Filesystem::class,
        'Filesystem' => Adapter\Filesystem::class,
        'memcache' => Adapter\Memcache::class,
        'Memcache' => Adapter\Memcache::class,
        'memcached' => Adapter\Memcached::class,
        'Memcached' => Adapter\Memcached::class,
        'memory' => Adapter\Memory::class,
        'Memory' => Adapter\Memory::class,
        'mongo_db' => Adapter\MongoDb::class,
        'mongodb' => Adapter\MongoDb::class,
        'MongoDb' => Adapter\MongoDb::class,
        'MongoDB' => Adapter\MongoDb::class,
        'mongoDb' => Adapter\MongoDb::class,
        'mongoDB' => Adapter\MongoDb::class,
        'redis' => Adapter\Redis::class,
        'Redis' => Adapter\Redis::class,
        'session' => Adapter\Session::class,
        'Session' => Adapter\Session::class,
        'xcache' => Adapter\XCache::class,
        'xCache' => Adapter\XCache::class,
        'Xcache' => Adapter\XCache::class,
        'XCache' => Adapter\XCache::class,
        'win_cache' => Adapter\WinCache::class,
        'wincache' => Adapter\WinCache::class,
        'winCache' => Adapter\WinCache::class,
        'WinCache' => Adapter\WinCache::class,
        'zend_server_disk' => Adapter\ZendServerDisk::class,
        'zendserverdisk' => Adapter\ZendServerDisk::class,
        'zendServerDisk' => Adapter\ZendServerDisk::class,
        'ZendServerDisk' => Adapter\ZendServerDisk::class,
        'zend_server_shm' => Adapter\ZendServerShm::class,
        'zendservershm' => Adapter\ZendServerShm::class,
        'zendServerShm' => Adapter\ZendServerShm::class,
        'zendServerSHM' => Adapter\ZendServerShm::class,
        'ZendServerShm' => Adapter\ZendServerShm::class,
        'ZendServerSHM' => Adapter\ZendServerShm::class,
    ];

    protected $factories = [
        Adapter\Apc::class => InvokableFactory::class,
        Adapter\Apcu::class => InvokableFactory::class,
        Adapter\BlackHole::class => InvokableFactory::class,
        Adapter\Dba::class => InvokableFactory::class,
        Adapter\ExtMongoDb::class => InvokableFactory::class,
        Adapter\Filesystem::class => InvokableFactory::class,
        Adapter\Memcache::class => InvokableFactory::class,
        Adapter\Memcached::class => InvokableFactory::class,
        Adapter\Memory::class => InvokableFactory::class,
        Adapter\MongoDb::class => InvokableFactory::class,
        Adapter\Redis::class => InvokableFactory::class,
        Adapter\Session::class => InvokableFactory::class,
        Adapter\WinCache::class => InvokableFactory::class,
        Adapter\XCache::class => InvokableFactory::class,
        Adapter\ZendServerDisk::class => InvokableFactory::class,
        Adapter\ZendServerShm::class => InvokableFactory::class,
    ];

    /**
     * Don't share by default (v2)
     *
     * @var boolean
     */
    protected $shareByDefault = false;

    /**
     * Don't share by default (v3)
     *
     * @var boolean
     */
    protected $sharedByDefault = false;

    /**
     * @var string
     */
    protected $instanceOf = StorageInterface::class;
}
