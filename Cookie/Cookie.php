<?php

namespace Obullo\Cookie;

use Psr\Http\Message\ServerRequestInterface as Request;

use RuntimeException;
use Obullo\Log\LoggerInterface as Logger;
use Obullo\Config\ConfigInterface as Config;

/**
 * Control cookie set, get, delete and queue operations
 * 
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2015 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 */
class Cookie implements CookieInterface
{
    /**
     * Cookie unique id
     * 
     * @var string
     */
    protected $id;

    /**
     * Logger
     * 
     * @var object
     */
    protected $logger;

    /**
     * Cookie response headers
     * 
     * @var array
     */
    protected $headers = array();

    /**
     * Request cookies
     * 
     * @var array
     */
    protected $requestCookies = array();

    /**
     * Response cookies
     * 
     * @var array
     */
    protected $responseCookies = array();

    /**
     * Constructor
     * 
     * @param Request $request request
     * @param Config  $config  config
     * @param Logger  $logger  logger
     */
    public function __construct(Request $request, Config $config, Logger $logger)
    {
        $this->requestCookies = $request->getCookieParams();
        $this->config = $config;
        $this->logger = $logger;
        $this->logger->debug('Cookie Class Initialized');
    }

    /**
     * Create unique cookie id
     * 
     * @return void
     */
    protected function createId()
    {
        if ($this->id == null) {
            $this->id = uniqid();  // Create random id for new cookie
        }
    }

    /**
     * Set cookie name
     * 
     * @param string $name cookie name
     * 
     * @return object
     */
    public function name($name)
    {
        $this->createId();
        $this->responseCookies[$this->id]['name'] = trim($name);
        return $this;
    }
    
    /**
     * Set cookie value
     * 
     * @param string $value value
     * 
     * @return object
     */
    public function value($value = '')
    {
        $this->createId();
        $this->responseCookies[$this->id]['value'] = $value;
        return $this;
    }

    /**
     * Set cookie expire in seconds
     * 
     * @param integer $expire seconds
     * 
     * @return object
     */
    public function expire($expire = 0)
    {
        $this->createId();
        $this->responseCookies[$this->id]['expire'] = (int)$expire;
        return $this;
    }

    /**
     * Set cookie domain name
     * 
     * @param string $domain name
     * 
     * @return void
     */
    public function domain($domain = '')
    {
        $this->createId();
        $this->responseCookies[$this->id]['domain'] = $domain;
        return $this;
    }

    /**
     * Set cookie path
     * 
     * @param string $path name
     * 
     * @return object
     */
    public function path($path = '/')
    {
        $this->createId();
        $this->responseCookies[$this->id]['path'] = $path;
        return $this;
    }

    /**
     * Set secure cookie
     * 
     * @param boolean $bool true or false
     * 
     * @return object
     */
    public function secure($bool = false)
    {
        $this->createId();
        $this->responseCookies[$this->id]['secure'] = $bool;
        return $this;
    }

    /**
     * Make cookie available just for http. ( No javascript )
     * 
     * @param boolean $bool true or false
     * 
     * @return object
     */
    public function httpOnly($bool = false)
    {
        $this->createId();
        $this->responseCookies[$this->id]['httpOnly'] = $bool;
        return $this;
    }

    /**
     * Set a cookie prefix
     * 
     * @param string $prefix prefix
     * 
     * @return object
     */
    public function prefix($prefix = '')
    {
        $this->createId();
        $this->responseCookies[$this->id]['prefix'] = $prefix;
        return $this;
    }

    /**
     * Set cookie
     *
     * Accepts six parameter, or you can submit an associative
     * array in the first parameter containing all the values.
     * 
     * @param string $name  cookie name
     * @param string $value cookie value
     *
     * @return array
     */
    public function set($name = null, $value = null)
    {
        if (is_string($name) && $name != null) {    // Build method chain parameters

            if (! isset($this->responseCookies[$this->id]['name'])) {
                $this->name($name);   // Set cookie name
            }
            if (! isset($this->responseCookies[$this->id]['value'])) {
                $this->value($value); // Set cookie value
            }
            $properties = $this->buildParameters($this->responseCookies[$this->id]);
        }
        if ($name == null && $value == null) {  // If user want to use this way $this->cookie->name()->value()->set();

            $properties = $this->buildParameters($this->responseCookies[$this->id]);
        }
        $this->toHeader($this->id, $properties);
    }

    /**
     * Build cookie parameters
     * 
     * @param array $params cookie params
     * 
     * @return array
     */
    protected function buildParameters($params)
    {
        if (! is_array($params) || ! isset($params['name'])) {
            throw new RuntimeException("Cookie name can't be empty.");
        }
        $cookie = array();
        foreach (array('name','value','expire','domain','path','secure','httpOnly','prefix') as $k) {
            if (array_key_exists($k, $params)) {
                $cookie[$k] = $params[$k];
            } else {
                $cookie[$k] = $this->config['cookie'][$k];
            }
        }
        $cookie['name'] = trim($cookie['prefix'].$cookie['name']);
        $cookie['expire'] = $this->getExpiration($cookie['expire']);
        return $cookie;
    }

    /**
     * Convert to `Set-Cookie` header
     *
     * @param string $id         Cookie-id
     * @param array  $properties Cookie properties
     *
     * @return string
     */
    protected function toHeader($id, array $properties)
    {
        $result = urlencode($properties['name']) . '=' . urlencode($properties['value']);

        if (isset($properties['domain'])) {
            $result .= '; domain=' . $properties['domain'];
        }

        if (isset($properties['path'])) {
            $result .= '; path=' . $properties['path'];
        }

        $timestamp = $this->getTimestamp($properties);

        if ($timestamp !== 0) {
            $result .= '; expires=' . gmdate('D, d-M-Y H:i:s e', $timestamp);
        }

        if (isset($properties['secure']) && $properties['secure']) {
            $result .= '; secure';
        }

        if (isset($properties['httponly']) && $properties['httponly']) {
            $result .= '; HttpOnly';
        }
        $this->headers[$id] = $result;
    }

    /**
     * Returns to cookie response header array
     * 
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Create timestamp
     * 
     * @param array $properties cookie properties
     * 
     * @return mixed
     */
    protected function getTimestamp(array $properties)
    {
        $timestamp = 0;
        if (isset($properties['expire'])) {
            if (is_string($properties['expire'])) {
                $timestamp = strtotime($properties['expire']);
            } else {
                $timestamp = (int)$properties['expire'];
            }
        }
        return $timestamp;
    }

    /**
     * Get cookie
     * 
     * @param string $key    cookie key
     * @param string $prefix cookie prefix
     * 
     * @return string sanizited cookie
     */
    public function get($key, $prefix = null)
    {
        if (! isset($this->requestCookies[$key]) && empty($prefix) && ! empty($this->config['cookie']['prefix'])) {
            $prefix = $this->config['cookie']['prefix'];
        }
        $realKey = trim($prefix.$key);
        if (! isset($this->requestCookies[$realKey])) {
            return false;
        }
        return $this->requestCookies[$realKey];
    }

    /**
     * Returns to id of response cookie
     * 
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get expiration of cookie
     * 
     * @param int $expire in second
     * 
     * @return int
     */
    protected function getExpiration($expire)
    {
        if (! is_numeric($expire)) {
            $expire = time() - 86500;
        } else {
            if ($expire > 0) {
                $expire = time() + $expire;
            }
        }
        return $expire;
    }

    /**
    * Delete a cookie
    *
    * @param string $name   cookie
    * @param string $prefix custom prefix
    * 
    * @return void
    */
    public function delete($name = null, $prefix = null)
    {
        $prefix = ($prefix == null) ? $this->config['cookie']['prefix'] : $prefix;

        if ($name != null) {
            $this->name($name);
        }
        if ($prefix != null) {
            $this->prefix($prefix);
        }
        $this->value(null)->expire(-1)->prefix($prefix)->set();
    }

    /**
     * Removes cookie from response headers
     * 
     * @param string $name   cookie name
     * @param string $prefix cookie name
     * 
     * @return void
     */
    public function remove($name, $prefix = null)
    {
        $prefix = ($prefix == null) ? $this->config['cookie']['prefix'] : $prefix;

        if (! empty($prefix)) {
            $name = trim($prefix.$name);
        }
        foreach ($this->responseCookies as $id => $value) {
            if ($name == $value['name'] && isset($this->headers[$id])) {
                unset($this->headers[$id]);
                unset($this->responseCookies[$id]);
            }
        }
    }


}