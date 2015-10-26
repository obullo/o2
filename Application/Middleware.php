<?php

namespace Obullo\Application;

use RuntimeException;
use Obullo\Container\ContainerInterface;

/**
 * Middleware
 * 
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2015 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 */
class Middleware
{
    /**
     * Container
     * 
     * @var object
     */
    protected $c;

    /**
     * Count
     * 
     * @var integer
     */
    protected $count;

    /**
     * Middleware stack
     * 
     * @var array
     */
    protected $middlewares = array();

    /**
     * Registered middlewares
     * 
     * @var array
     */
    protected $registered = array();

    /**
     * Names
     * 
     * @var array
     */
    protected $names;

    /**
     * Constructor
     * 
     * @param Obullo\Container\Dependency $dependency object 
     */
    public function __construct($dependency)
    {
        $this->dependency = $dependency;
    }

    /**
     * Register application middlewares
     * 
     * @param array $array middlewares
     * 
     * @return object Middleware
     */
    public function configure(array $array)
    {
        $this->registered = $array;

        // $this->add('Router');  // Add first middlewares
        // $this->add('Begin');

        return $this;
    }

    /**
     * Check given middleware is registered
     * 
     * @param string $name middleware
     * 
     * @return boolean
     */
    public function isConfigured($name)
    {
        if (isset($this->registered[$name])) {
            return true;
        }
        return false;
    }

    /**
     * Add middleware
     * 
     * @param string|array $name middleware key
     * 
     * @return object Middleware
     */
    public function add($name)
    {
        if (is_string($name)) {
            return $this->resolveMiddleware($name);
        } elseif (is_array($name)) { 
            foreach ($name as $key) {
                $this->resolveMiddleware($key);
            }
        }
        return $this;
    }

    /**
     * Check middleware is loaded returns true if registered otherwise false
     * 
     * @param string $name middleware name
     * 
     * @return boolean
     */
    public function has($name)
    {
        $this->validateMiddleware($name);
        if (isset($this->names[$name]) 
            && isset($this->middlewares[$this->names[$name]]) 
            && $this->getClassNameByIndex($this->names[$name]) == $name
        ) {
            return true;
        }
        return false;
    }

    /**
     * Get class name without namespace using explode method
     * 
     * @param integer $index number
     * 
     * @return string class name without namespace
     */
    protected function getClassNameByIndex($index)
    {
        $class = get_class($this->middlewares[$index]);
        $exp = explode("\\", $class);
        return end($exp);
    }

    /**
     * Returns to middleware object to inject parameters
     * 
     * @param string $name middleware
     * 
     * @return object
     */
    public function get($name)
    {
        $this->validateMiddleware($name);
        $index = $this->names[$name];
        return $this->middlewares[$index];
    }

    /**
     * Resolve middleware
     * 
     * @param string $name middleware key
     * 
     * @return object mixed
     */
    protected function resolveMiddleware($name)
    {
        $this->validateMiddleware($name);
        $Class = $this->registered[$name];
        ++$this->count;
        $this->names[$name] = $this->count;
        return $this->middlewares[$this->count] = $this->dependency->resolveDependencies($Class);  // Store middlewares
    }

    /**
     * Removes middleware
     * 
     * @param string|array $name middleware key
     * 
     * @return void
     */
    public function remove($name)
    {
        if (is_string($name)) {
            $this->validateMiddleware($name);
            $index = $this->middlewaresNames[$name];
            unset($this->middlewares[$index], $this->names[$name]);
            --$this->count;
        }
        if (is_array($name)) {
            foreach ($name as $key) {
                $this->remove($key);
            }
        }
    }

    /**
     * Validate middleware
     * 
     * @param string $name middleware
     * 
     * @return void
     */
    protected function validateMiddleware($name)
    {
        if (! isset($this->registered[$name])) {
            throw new RuntimeException(
                sprintf(
                    'Middleware "%s" is not registered in middlewares.php',
                    $name
                )
            );
        }
    }

    /**
     * Returns to all middleware objects
     * 
     * @return array
     */
    public function getValues()
    {
        return array_values($this->middlewares);
    }

    /**
     * Returns to all middleware names
     * 
     * @return array
     */
    public function getNames()
    {
        return array_keys($this->names);
    }

}