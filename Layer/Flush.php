<?php

namespace Obullo\Layer;

use Obullo\Layer\Layer;

/**
 * Flush Class
 * 
 * @category  Layer
 * @package   Flush
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT
 * @link      http://obullo.com/package/layer
 */
Class Flush
{
    /**
     * Container class
     * 
     * @var object
     */
    public $c;

    /**
     * Cache service
     * 
     * @var object
     */
    public $cache;

    /**
     * Constructor
     *
     * @param object $c container
     */
    public function __construct($c)
    {
        $this->c = $c;
        $this->cache = $c->load('service/cache');
        $this->c->load('service/logger')->debug('Layer Flush Class Initialized');
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
        if ( sizeof($data) > 0 ) {  // We can't use count() in sub layers sizeof gives better results.
            $hashString .= str_replace('"', '', json_encode($data)); // remove quotes to fix equality problem
        }
        $KEY = $this->generateId($hashString);
        if ($this->cache->keyExists($KEY)) {
            return $this->cache->delete($KEY);
        }
        return false;
    }

    /**
     * Removes layer from cache using layer id
     * 
     * @param integer $layerId id
     * 
     * @return boolean
     */
    public function id($layerId)
    {
        if (is_numeric($layerId) AND $this->cache->keyExists($layerId)) {
            return $this->cache->delete($layerId);
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

// END Flush class

/* End of file Flush.php */
/* Location: .Obullo/Layer/Flush.php */