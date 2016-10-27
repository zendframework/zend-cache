<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Cache\Storage\Adapter;

use Redis as RedisResource;
use ReflectionClass;
use Traversable;
use Zend\Cache\Exception;
use Zend\Stdlib\ArrayUtils;

/**
 * This is a resource manager for redis
 */
class RedisResourceManager
{
    /**
     * Registered resources
     *
     * @var array
     */
    protected $resources = [];

    /**
     * Check if a resource exists
     *
     * @param string $id
     * @return bool
     */
    public function hasResource($id)
    {
        return isset($this->resources[$id]);
    }

    /**
     * Get redis server version
     *
     * @param string $resourceId
     * @return string
     * @throws Exception\RuntimeException
     */
    public function getVersion($resourceId)
    {
        // check resource id and initialize the resource
        $this->getResource($resourceId);

        return $this->resources[$resourceId]['version'];
    }

    /**
     * Get redis major server version
     *
     * @param string $resourceId
     * @return int
     * @throws Exception\RuntimeException
     */
    public function getMajorVersion($resourceId)
    {
        // check resource id and initialize the resource
        $this->getResource($resourceId);

        return (int) $this->resources[$resourceId]['version'];
    }

    /**
     * Get redis server version
     *
     * @deprecated 2.2.2 Use getMajorVersion instead
     *
     * @param string $id
     * @return int
     * @throws Exception\RuntimeException
     */
    public function getMayorVersion($id)
    {
        return $this->getMajorVersion($id);
    }

    /**
     * Get redis resource database
     *
     * @param string $id
     * @return string
     */
    public function getDatabase($id)
    {
        if (!$this->hasResource($id)) {
            throw new Exception\RuntimeException("No resource with id '{$id}'");
        }

        $resource = $this->resources[$id];
        return $resource['database'];
    }

    /**
     * Get redis resource password
     *
     * @param string $id
     * @return string
     */
    public function getPassword($id)
    {
        if (!$this->hasResource($id)) {
            throw new Exception\RuntimeException("No resource with id '{$id}'");
        }

        $resource = $this->resources[$id];
        return $resource['password'];
    }

    /**
     * Gets a redis resource
     *
     * @param string $id
     * @return RedisResource
     * @throws Exception\RuntimeException
     */
    public function getResource($id)
    {
        if (!$this->hasResource($id)) {
            throw new Exception\RuntimeException("No resource with id '{$id}'");
        }

        // initialize the redis instance and connection if not already done
        if (!$this->resources[$id]['initialized']) {

            // create a new instance if we don't have it
            if (!$this->resources[$id]['resource']) {
                $this->resources[$id]['resource'] = new RedisResource();
            }

            $resource = $this->resources[$id];
            $server   = $resource['server'];
            $redis    = $resource['resource'];

            if ($resource['persistent_id'] !== '') {
                //connect or reuse persistent connection
                $success = $redis->pconnect($server['host'], $server['port'], $server['timeout'], $resource['persistent_id']);
            } elseif ($server['port']) {
                $success = $redis->connect($server['host'], $server['port'], $server['timeout']);
            } elseif ($server['timeout']) {
                //connect through unix domain socket
                $success = $redis->connect($server['host'], $server['timeout']);
            } else {
                $success = $redis->connect($server['host']);
            }

            if (!$success) {
                throw new Exception\RuntimeException('Could not estabilish connection with Redis instance');
            }

            foreach ($resource['lib_options'] as $k => $v) {
                $redis->setOption($k, $v);
            }

            if ($resource['password']) {
                $redis->auth($resource['password']);
            }

            $redis->select($resource['database']);

            $info = $redis->info();
            $this->resources[$id]['version']     = $info['redis_version'];
            $this->resources[$id]['initialized'] = true;
        }

        return $this->resources[$id]['resource'];
    }

    /**
     * Get server
     * @param string $id
     * @throws Exception\RuntimeException
     * @return array array('host' => <host>[, 'port' => <port>[, 'timeout' => <timeout>]])
     */
    public function getServer($id)
    {
        if (!$this->hasResource($id)) {
            throw new Exception\RuntimeException("No resource with id '{$id}'");
        }

        $resource = $this->resources[$id];
        return $resource['server'];
    }

    /**
     * Normalize one server into the following format:
     * array('host' => <host>[, 'port' => <port>[, 'timeout' => <timeout>]])
     *
     * @param string|array $server
     *
     * @throws Exception\InvalidArgumentException
     */
    protected function normalizeServer(&$server)
    {
        $host    = null;
        $port    = null;
        $timeout = 0;

        // convert a single server into an array
        if ($server instanceof Traversable) {
            $server = ArrayUtils::iteratorToArray($server);
        }

        if (is_array($server)) {
            // array(<host>[, <port>[, <timeout>]])
            if (isset($server[0])) {
                $host    = (string) $server[0];
                $port    = isset($server[1]) ? (int) $server[1] : $port;
                $timeout = isset($server[2]) ? (int) $server[2] : $timeout;
            }

            // array('host' => <host>[, 'port' => <port>, ['timeout' => <timeout>]])
            if (!isset($server[0]) && isset($server['host'])) {
                $host    = (string) $server['host'];
                $port    = isset($server['port'])    ? (int) $server['port']    : $port;
                $timeout = isset($server['timeout']) ? (int) $server['timeout'] : $timeout;
            }
        } else {
            // parse server from URI host{:?port}
            $server = trim($server);
            if (strpos($server, '/') !== 0) {
                // non unix domain socket connection
                $server = parse_url($server);
            } else {
                $server = ['host' => $server];
            }
            if (!$server) {
                throw new Exception\InvalidArgumentException("Invalid server given");
            }

            $host    = $server['host'];
            $port    = isset($server['port'])    ? (int) $server['port']    : $port;
            $timeout = isset($server['timeout']) ? (int) $server['timeout'] : $timeout;
        }

        if (!$host) {
            throw new Exception\InvalidArgumentException('Missing required server host');
        }

        $server = [
            'host'    => $host,
            'port'    => $port,
            'timeout' => $timeout,
        ];
    }

    /**
     * Extract password to be used on connection
     *
     * @param mixed $resource
     * @param mixed $serverUri
     *
     * @return string|null
     */
    protected function extractPassword($resource, $serverUri)
    {
        if (! empty($resource['password'])) {
            return $resource['password'];
        }

        if (! is_string($serverUri)) {
            return;
        }

        // parse server from URI host{:?port}
        $server = trim($serverUri);

        if (strpos($server, '/') === 0) {
            return;
        }

        //non unix domain socket connection
        $server = parse_url($server);

        return isset($server['pass']) ? $server['pass'] : null;
    }

    /**
     * Set a resource
     *
     * @param string $id
     * @param array|Traversable|RedisResource $resource
     * @return RedisResourceManager Fluent interface
     */
    public function setResource($id, $resource)
    {
        $id = (string) $id;

        $defaults = [
            'resource'      => null,
            'persistent_id' => '',
            'lib_options'   => [],
            'server'        => [],
            'password'      => '',
            'database'      => 0,
            'version'       => 0,
            'initialized'   => false,
        ];

        if (!$resource instanceof RedisResource) {
            if ($resource instanceof Traversable) {
                $resource = ArrayUtils::iteratorToArray($resource);
            } elseif (!is_array($resource)) {
                throw new Exception\InvalidArgumentException(
                    'Resource must be an instance of an array or Traversable'
                );
            }

            // set missing options to default
            $resource = array_merge($defaults, $resource);

            // normalize and validate params
            $resource['persistent_id'] = $this->normalizePersistentId($resource['persistent_id']);
            $resource['lib_options']   = $this->normalizeLibOptions($resource['lib_options']);

            // #6495 note: order is important here, as `normalizeServer` applies destructive
            // transformations on $resource['server']
            $resource['password'] = $this->extractPassword($resource, $resource['server']);

            $this->normalizeServer($resource['server']);
        } else {
            // there are two ways of determining if redis is already initialized with connect function:
            // 1) ping server
            // 2) check undocumented property 'socket' which is available only after successful connect
            // TODO: how to get back redis connection info from resource?
            $resource = array_merge($defaults, [
                'resource'    => $resource,
                'initialized' => isset($resource->socket),
            ]);
        }

        $this->resources[$id] = $resource;
        return $this;
    }

    /**
     * Remove a resource
     *
     * @param string $id
     * @return RedisResourceManager Fluent interface
     */
    public function removeResource($id)
    {
        unset($this->resources[$id]);
        return $this;
    }

    /**
     * Set the persistent id
     *
     * @param string $id
     * @param string $persistentId
     * @return RedisResourceManager Fluent interface
     * @throws Exception\RuntimeException
     */
    public function setPersistentId($id, $persistentId)
    {
        if (!$this->hasResource($id)) {
            return $this->setResource($id, [
                'persistent_id' => $persistentId
            ]);
        }

        if ($this->resources[$id] instanceof RedisResource) {
            throw new Exception\RuntimeException(
                "Can't change persistent id of resource {$id} after instanziation"
            );
        }

        $this->resources[$id]['persistent_id'] = $this->normalizePersistentId($persistentId);

        return $this;
    }

    /**
     * Get the persistent id
     *
     * @param string $id
     * @return string
     * @throws Exception\RuntimeException
     */
    public function getPersistentId($id)
    {
        if (!$this->hasResource($id)) {
            throw new Exception\RuntimeException("No resource with id '{$id}'");
        }

        if ($this->resources[$id] instanceof RedisResource) {
            throw new Exception\RuntimeException(
                "Can't get persistent id of an instantiated redis resource"
            );
        }

        return $this->resources[$id]['persistent_id'];
    }

    /**
     * Normalize the persistent id
     *
     * @param string $persistentId
     * @return string
     */
    protected function normalizePersistentId(& $persistentId)
    {
        return (string) $persistentId;
    }

    /**
     * Set Redis options
     *
     * @param string $id
     * @param array  $libOptions
     * @return RedisResourceManager Fluent interface
     */
    public function setLibOptions($id, array $libOptions)
    {
        if (!$this->hasResource($id)) {
            return $this->setResource($id, [
                'lib_options' => $libOptions
            ]);
        }

        $libOptions = $this->normalizeLibOptions($libOptions);
        $this->resources[$id]['lib_options'] = $libOptions;

        if ($this->resources[$id]['resource'] instanceof RedisResource) {
            $redis = $this->resources[$id]['resource'];
            if (method_exists($redis, 'setOptions')) {
                $redis->setOptions($libOptions);
            } else {
                foreach ($libOptions as $key => $value) {
                    $redis->setOption($key, $value);
                }
            }
        }

        return $this;
    }

    /**
     * Get Redis options
     *
     * @param string $id
     * @return array
     * @throws Exception\RuntimeException
     */
    public function getLibOptions($id)
    {
        if (!$this->hasResource($id)) {
            throw new Exception\RuntimeException("No resource with id '{$id}'");
        }

        if ($this->resources[$id] instanceof RedisResource) {
            $libOptions = [];
            $reflection = new ReflectionClass('Redis');
            $constants  = $reflection->getConstants();
            foreach ($constants as $constName => $constValue) {
                if (substr($constName, 0, 4) == 'OPT_') {
                    $libOptions[$constValue] = $resource->getOption($constValue);
                }
            }
            return $libOptions;
        }

        return $this->resources[$id]['lib_options'];
    }

    /**
     * Set one Redis option
     *
     * @param string     $id
     * @param string|int $key
     * @param mixed      $value
     * @return RedisResourceManager Fluent interface
     */
    public function setLibOption($id, $key, $value)
    {
        return $this->setLibOptions($id, [$key => $value]);
    }

    /**
     * Get one Redis option
     *
     * @param string     $id
     * @param string|int $key
     * @return mixed
     * @throws Exception\RuntimeException
     */
    public function getLibOption($id, $key)
    {
        if (!$this->hasResource($id)) {
            throw new Exception\RuntimeException("No resource with id '{$id}'");
        }

        $key = $this->normalizeLibOptionKey($key);

        if ($this->resources[$id] instanceof RedisResource) {
            return $resource->getOption($key);
        }

        return isset($this->resources[$id]['lib_options'][$key]) ? $this->resources[$id]['lib_options'][$key] : null;
    }

    /**
     * Normalize Redis options
     *
     * @param array|Traversable $libOptions
     * @return array
     * @throws Exception\InvalidArgumentException
     */
    protected function normalizeLibOptions($libOptions)
    {
        if (!is_array($libOptions) && !($libOptions instanceof Traversable)) {
            throw new Exception\InvalidArgumentException(
                "Lib-Options must be an array or an instance of Traversable"
            );
        }

        $result = [];
        foreach ($libOptions as $key => $value) {
            $key          = $this->normalizeLibOptionKey($key);
            $result[$key] = $value;
        }

        return $result;
    }

    /**
     * Convert option name into it's constant value
     *
     * @param string|int $key
     * @return string
     * @throws Exception\InvalidArgumentException
     */
    protected function normalizeLibOptionKey($key)
    {
        // convert option name into it's constant value
        if (is_string($key)) {
            $const = 'Redis::OPT_' . str_replace([' ', '-'], '_', strtoupper($key));
            if (!defined($const)) {
                throw new Exception\InvalidArgumentException("Unknown redis option '{$key}' ({$const})");
            }
            return constant($const);
        }

        return (int) $key;
    }

    /**
     * Set server
     *
     * Server can be described as follows:
     * - URI:   /path/to/sock.sock
     * - Assoc: array('host' => <host>[, 'port' => <port>[, 'timeout' => <timeout>]])
     * - List:  array(<host>[, <port>, [, <timeout>]])
     *
     * @param string       $id
     * @param string|array $server
     * @return RedisResourceManager
     */
    public function setServer($id, $server)
    {
        if (!$this->hasResource($id)) {
            return $this->setResource($id, [
                'server' => $server
            ]);
        }

        $this->normalizeServer($server);

        $resource             = & $this->resources[$id];
        $resource['password'] = $this->extractPassword($resource, $server);

        if ($resource['resource'] instanceof RedisResource) {
            $resourceParams = ['server' => $server];

            if (! empty($resource['password'])) {
                $resourceParams['password'] = $resource['password'];
            }

            $this->setResource($id, $resourceParams);
        } else {
            $resource['server'] = $server;
        }

        return $this;
    }

    /**
     * Set redis password
     *
     * @param string $id
     * @param string $password
     * @return RedisResource
     */
    public function setPassword($id, $password)
    {
        if (!$this->hasResource($id)) {
            return $this->setResource($id, [
                'password' => $password,
            ]);
        }

        $this->resources[$id]['password']    = $password;
        $this->resources[$id]['initialized'] = false;

        return $this;
    }

    /**
     * Set redis database number
     *
     * @param string $id
     * @param int $database
     * @return RedisResourceManager
     */
    public function setDatabase($id, $database)
    {
        $database = (int) $database;

        if (!$this->hasResource($id)) {
            return $this->setResource($id, [
                'database' => $database,
            ]);
        }

        $resource = & $this->resources[$id];
        if ($resource['resource'] instanceof RedisResource && $resource['initialized']) {
            $resource['resource']->select($database);
        }

        $resource['database']    = $database;
        $resource['initialized'] = false;

        return $this;
    }
}
