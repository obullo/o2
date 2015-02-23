<?php

namespace Obullo\Annotations;

use Controller;
use Obullo\Container\Container;

/**
 * Filter Class
 * 
 * @category  Annotations
 * @package   Filter
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/annotations
 */
Class Filter
{
    /**
     * Container
     * 
     * @var object
     */
    protected $c;

    /**
     * Before filters data
     * 
     * @var array
     */
    protected $before = array();

    /**
     * After filters data
     * 
     * @var array
     */
    protected $after = array();

    /**
     * On Controller load() filters data
     * 
     * @var array
     */
    protected $load = array();

    /**
     * Finish filters data
     * 
     * @var array
     */
    protected $finish = array();

    /**
     * Track of filter names
     * 
     * @var array
     */
    protected $track = array();

    /**
     * Key counter
     * 
     * @var integer
     */
    protected $count;

    /**
     * Http method name
     * 
     * @var string
     */
    protected $httpMethod = 'get';

    /**
     * Constructor
     * 
     * @param object $c container
     */
    public function __construct(Container $c)
    {
        $this->c = $c;
        $this->count = 0;
        $method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'get';
        $this->httpMethod = strtolower($method);
    }

    /**
     * Initialize to before filters
     * 
     * @param string $filter name
     * 
     * @return object
     */
    public function before($filter = '')
    {
        $this->before[$this->count] = array('name' => $filter);
        $this->track[] = 'before';
        ++$this->count;
        return $this;
    }

    /**
     * Initialize to after filters
     * 
     * @param string $filter name
     * 
     * @return object
     */
    public function after($filter = '')
    {
        $this->after[$this->count] = array('name' => $filter);
        $this->track[] = 'after';
        ++$this->count;
        return $this;
    }

    /**
     * Initialize to finish filters
     * 
     * @param string $filter name
     * 
     * @return object
     */
    public function finish($filter = '')
    {
        $this->finish[$this->count] = array('name' => $filter);
        $this->track[] = 'finish';
        ++$this->count;
        return $this;
    }

    /**
     * Initialize to on load filters
     * 
     * @param string $filter name
     * 
     * @return object
     */
    public function load($filter = '')
    {
        $this->load[$this->count] = array('name' => $filter);
        $this->track[] = 'load';
        ++$this->count;
        return $this;
    }

    /**
     * Initialize to after filters
     * 
     * @param string|array $params http method(s): ( post, get, put, delete )
     * 
     * @return object
     */
    public function when($params = '')
    {
        if (is_string($params)) {
            $params = array($params);
        }
        $count = $this->count - 1;
        $last = end($this->track);
        $this->{$last}[$count]['when'] = $params;  // push when parameters
        return $this;
    }

    /**
     * Initialize to allowed methods filters
     * 
     * @param string|array $params parameters
     * 
     * @return void
     */
    public function method($params = null)
    {
        if (is_string($params)) {
            $params = array($params);
        }
        // WARNING:
        // We controller instance other wise layer functionalities not works well.
        // After that the last layer request router instance every time become old.
        
        Controller::$instance->router->runFilter('methodNotAllowed', 'before', array('allowedMethods' => $params));
        return;
    }

    /**
     * Subscribe to events
     *
     * @param string $namespace event subscribe listener
     * 
     * @return void
     */
    public function subscribe($namespace)
    {
        $Class = '\\'.ltrim($namespace, '\\');
        $this->c['event']->subscribe(new $Class($this->c));
    }

    /**
     * Render filter data
     *
     * @param string $method before or after
     * 
     * @return void
     */
    public function initFilters($method = 'before')
    {   
        if (count($this->{$method}) == 0) {
            return;
        }
        foreach ($this->{$method} as $val) {
            if (isset($val['when']) AND in_array($this->httpMethod, $val['when'])) {  // stop filter

                // WARNING:
                // We use controller instance other wise layer functionalities does not work well.
                // After that the last layer request router instance every time become old.
            
                Controller::$instance->router->runFilter($val['name'], $method);
            }
            if ( ! isset($val['when'])) {
                Controller::$instance->router->runFilter($val['name'], $method);
            }
        }
    }

    
}

// END Filter.php File
/* End of file Filter.php

/* Location: .Obullo/Application/Filter.php */