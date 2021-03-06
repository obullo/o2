<?php

namespace Obullo\Layer;

use Obullo\Log\LoggerInterface;
use Obullo\Cache\CacheInterface;

/**
 * Flush cache remove handler
 * 
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2015 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 */
class Flush
{
    /**
     * Cache service
     * 
     * @var object
     */
    protected $cache;

    /**
     * Logger
     * 
     * @var object
     */
    protected $logger;

    /**
     * Constructor
     *
     * @param object $logger \Obullo\Log\LoggerInterface
     * @param object $cache  \Obullo\Cache\CacheInterface
     */
    public function __construct(LoggerInterface $logger, CacheInterface $cache)
    {
        $this->cache = $cache;
        $logger->debug('Layer Flush Class Initialized');
    }

    /**
     * Removes layer from cache using layer "uri" and "parameters".
     * 
     * @param string $uri  string
     * @param array  $data array
     * 
     * @return boolean
     */
    public function uri($uri = '', $data = array())
    {
        $hashString = trim($uri, '/');
        if (sizeof($data) > 0 ) {      // We can't use count() in sub layers sizeof gives better results.
            $hashString .= str_replace('"', '', json_encode($data)); // Remove quotes to fix equality problem
        }
        $KEY = $this->generateId($hashString);
        if ($this->cache->exists($KEY)) {
            return $this->cache->delete($KEY);
        }
        return false;
    }

    /**
     * Create unsigned integer id using 
     * hash string.
     * 
     * @param string $hashString resource
     * 
     * @return string id
     */
    public function generateId($hashString)
    {
        $id = trim($hashString);
        return Layer::CACHE_KEY. (int)sprintf("%u", crc32((string)$id));
    }
}