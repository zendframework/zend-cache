<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Cache;

use Traversable;
use Zend\Stdlib\ArrayUtils;
use Zend\ServiceManager\ServiceManager;

/**
 * @deprecated static factories are deprecated as of zendframework 2.9 and may be removed in future major versions.
 */
abstract class PatternFactory
{

    /**
     * The pattern manager
     *
     * @var null|PatternPluginManager
     */
    protected static $plugins = null;

    /**
     * Instantiate a cache pattern
     *
     * @param  string|Pattern\PatternInterface          $patternName
     * @param  array|Traversable|Pattern\PatternOptions $options
     *
     * @return Pattern\PatternInterface
     * @throws Exception\InvalidArgumentException
     * @deprecated static factories are deprecated as of zendframework 2.9 and may be removed in future major versions.
     */
    public static function factory($patternName, $options = [])
    {
        trigger_error(sprintf(
            '%s is deprecated; please use %s::get instead',
            __METHOD__,
            PatternPluginManager::class
        ), E_USER_DEPRECATED);

        if ($options instanceof Pattern\PatternOptions) {
            $options = $options->toArray();
        }

        if ($options instanceof Traversable) {
            $options = ArrayUtils::iteratorToArray($options);
        }

        if (!is_array($options)) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s expects an array, Traversable object, or %s\Pattern\PatternOptions object; received "%s"',
                __METHOD__,
                __NAMESPACE__,
                (is_object($options) ? get_class($options) : gettype($options))
            ));
        }

        if ($patternName instanceof Pattern\PatternInterface) {
            $patternName->setOptions(new Pattern\PatternOptions($options));

            return $patternName;
        }

        return static::getPluginManager()->get($patternName, $options);
    }

    /**
     * Get the pattern plugin manager
     *
     * @return PatternPluginManager
     * @deprecated static factories are deprecated as of zendframework 2.9 and may be removed in future major versions.
     */
    public static function getPluginManager()
    {
        trigger_error(sprintf(
            '%s is deprecated',
            __METHOD__
        ), E_USER_DEPRECATED);

        if (static::$plugins === null) {
            static::$plugins = new PatternPluginManager(new ServiceManager);
        }

        return static::$plugins;
    }

    /**
     * Set the pattern plugin manager
     *
     * @param  PatternPluginManager $plugins
     *
     * @return void
     * @deprecated static factories are deprecated as of zendframework 2.9 and may be removed in future major versions.
     */
    public static function setPluginManager(PatternPluginManager $plugins)
    {
        trigger_error(sprintf(
            '%s is deprecated',
            __METHOD__
        ), E_USER_DEPRECATED);

        static::$plugins = $plugins;
    }

    /**
     * Reset pattern plugin manager to default
     *
     * @return void
     * @deprecated static factories are deprecated as of zendframework 2.9 and may be removed in future major versions.
     */
    public static function resetPluginManager()
    {
        trigger_error(sprintf(
            '%s is deprecated',
            __METHOD__
        ), E_USER_DEPRECATED);

        static::$plugins = null;
    }
}
