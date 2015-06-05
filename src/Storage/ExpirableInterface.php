<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Cache\Storage;

interface ExpirableInterface
{
    /**
     * Sets an expiration date (a timeout) on an item
     *
     * @param $key
     * @param $ttl
     *
     * @return bool
     */
    public function setTimeout($key, $ttl);

    /**
     * Remaining timeout of an item
     *
     * @param $key
     *
     * @return mixed
     */
    public function getRemainingTimeout($key);

}
