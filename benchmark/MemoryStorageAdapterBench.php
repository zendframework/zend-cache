<?php

namespace ZendBench\Cache;

use Zend\Cache\StorageFactory;
use Zend\Stdlib\ErrorHandler;

/**
 * @Revs(100)
 * @Iterations(10)
 * @Warmup(1)
 */
class MemoryStorageAdapterBench extends CommonStorageAdapterBench
{
    public function __construct()
    {
        ErrorHandler::start(E_USER_DEPRECATED);
        // instantiate the storage adapter
        $this->storage = StorageFactory::adapterFactory('memory');
        ErrorHandler::clean();

        parent::__construct();
    }
}
