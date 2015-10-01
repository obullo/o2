<?php

namespace Obullo\Cache;

use Obullo\Container\ContainerInterface;

/**
 * Cache Manager
 * 
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2015 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 */
class CacheManager
{
    /**
     * Container class
     * 
     * @var object
     */
    protected $c;

    /**
     * Service Parameters
     * 
     * @var array
     */
    protected $params;

    /**
     * Create classes
     * 
     * @param object $c container
     * 
     * @return object
     */
    public function __construct(ContainerInterface $c)
    {
        $this->c = $c;
    }

    /**
     * Set parameters
     * 
     * @param array $params parameters
     *
     * @return void
     */
    public function setParameters(array $params)
    {
        $this->params = $params;
    }

    /**
     * Returns to selected cache handler object
     * 
     * @return object
     */
    public function getClass()
    {
        return $this->c['app']
            ->provider($this->params['provider']['name'])
            ->get($this->params['provider']['params']);
    }
}