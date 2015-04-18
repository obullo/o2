<?php

namespace Obullo\Http\Response;

use Obullo\Container\Container;

/**
 * Manage Http Response Headers
 * 
 * @category  Http
 * @package   Error
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/http
 */
class Headers
{
    /**
     * Response Headers
     * 
     * @var array
     */
    protected $headers = array();

    /**
     * Header replace option and any
     * possible option handler
     * 
     * @var array
     */
    protected $options = array();

    /**
     * Set response header
     * 
     * @param string  $name    header key
     * @param string  $value   header value
     * @param boolean $replace header replace option
     *
     * @return void
     */
    public function set($name, $value = null, $replace = true)
    {
        $this->options[$name] = ['replace' => $replace];
        $this->headers[$name] = $value;
    }

    /**
     * Get header
     *
     * @param string $name header key
     * 
     * @return void
     */
    public function get($name)
    {
        return $this->headers[$name];
    }

    /**
     * Remove header
     * 
     * @param string $name header key
     * 
     * @return void
     */
    public function remove($name)
    {
        unset($this->headers[$name]);
    }

    /**
     * Returns to all headers
     * 
     * @return array
     */
    public function all()
    {
        return $this->headers;
    }

    /**
     * Get header options
     * 
     * @return array
     */
    public function options()
    {
        return $this->options;
    }

}

// END Headers.php File
/* End of file Headers.php

/* Location: .Obullo/Http/Response/Headers.php */