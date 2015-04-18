<?php

namespace Obullo\Application;

use Controller;
use Obullo\Config\Env;
use Obullo\Config\Config;
use BadMethodCallException;
use Obullo\Debugger\WebSocket;
use Obullo\Container\Container;

/*
|--------------------------------------------------------------------------
| Php startup error handler
|--------------------------------------------------------------------------
*/
if (error_get_last() != null) {
    include TEMPLATES .'errors'. DS .'startup.php';
}
require OBULLO .'Container'. DS .'Container.php';
require OBULLO .'Config'. DS .'Config.php';

require 'Obullo.php';

/**
 * Container
 * 
 * @var object
 */
$c = new Container;

$c['env'] = function () use ($c) {
    return new Env($c);
};
$c['config'] = function () use ($c) {
    return new Config($c);
};
$c['app'] = function () {
    return new Http;
};
/**
 * Obullo bootstrap
 * 
 * @category  Container
 * @package   Container
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/container
 */
class Http extends Obullo
{
    /**
     * Middleware objects
     * 
     * @var array
     */
    protected $middleware = array();

    /**
     * Middleware names
     * 
     * @var array
     */
    protected $middlewareNames = array();

    /**
     * Constructor
     *
     * @return void
     */
    public function init()
    {
        global $c;
        $this->c = $c;

        $this->detectEnvironment();
        $this->setErrorReporting();
        $this->setDefaultTimezone();
        $this->setPhpDebugger();

        $this->middleware = array($this); // Define default middleware stack

        include OBULLO_CONTROLLER;

        include APP_COMPONENTS;
        include APP_PROVIDERS;
        include APP_EVENTS;
        include APP_ROUTES;

        if ($this->c['config']['debugger']['enabled']) {
            $this->websocket = new WebSocket($this->c);
            $this->websocket->connect();
        }
        register_shutdown_function(array($this, 'close'));
    }

    /**
     * Run
     *
     * This method invokes the middleware stack, including the core application;
     * the result is an array of HTTP status, header, and output.
     * 
     * @return void
     */
    public function run()
    {
        $this->init();
        $this->c['router']->init();                 // Initialize Routes

        $class = $this->c['router']->fetchClass();
        $method = $this->c['router']->fetchMethod();
        $namespace = $this->c['router']->fetchNamespace();

        include MODULES .$this->c['router']->fetchModule(DS).$this->c['router']->fetchDirectory(). DS .$this->c['router']->fetchClass().'.php';

        $this->className = '\\'.$namespace.'\\'.$class;
        $this->dispatchClass();

        $this->class = new $this->className;  // Call the controller
        $this->method = $method;

        $this->dispatchMethod();
        $this->dispatchMiddlewares();
        $this->dispatchAnnotations();  // Read annotations after the attaching middlewares otherwise @middleware->remove()
                                       // does not work

        $middleware = current($this->middleware);  // Invoke middleware chains using current then each middleware will call next 
        $middleware->load();
        
        if (method_exists($this->class, 'extend')) {      // View traits must be run at the top level otherwise layout view file
            $this->class->extend();                       // could not load view variables.
        }
        $middleware->call();

        $this->c['response']->flush();
    }

    /**
     * Register assigned middlewares
     * 
     * @return void
     */
    protected function dispatchMiddlewares()
    {
        global $c;
        $currentRoute = $this->getCurrentRoute();

        foreach ($this->c['router']->getAttachedRoutes() as $value) {
            $attachedRoute = str_replace('#', '\#', $value['attachedRoute']);  // Ignore delimiter

            if ($value['route'] == $currentRoute) {     // if we have natural route match
                $this->middleware($value['name'], $value['options']);
            } elseif (preg_match('#'. $attachedRoute .'#', $currentRoute)) {
                $this->middleware($value['name'], $value['options']);
            }
        }
        include APP_MIDDLEWARES;  // Include app/middlewares.php
    }

    /**
     * Add middleware
     *
     * This method prepends new middleware to the application middleware stack.
     * The argument must be an instance that subclasses Slim_Middleware.
     *
     * @param mixed $middleware class name or \Http\Middlewares\Middleware object
     * @param array $params     parameters
     *
     * @return void
     */
    public function middleware($middleware, $params = array())
    {
        if (is_string($middleware)) {
            $Class = '\\Http\\Middlewares\\'.ucfirst($middleware);
            $middleware = new $Class;
        }
        $middleware->params = $params;      // Inject Parameters
        $middleware->setContainer($this->c);
        $middleware->setApplication($this);
        $middleware->setNextMiddleware(current($this->middleware));
        array_unshift($this->middleware, $middleware);

        $name = get_class($middleware);
        $this->middlewareNames[$name] = $name;  // Track names
    }

    /**
     * Removes middleware ( Only works with annotations )
     * 
     * @param string $middleware name
     * 
     * @return void
     */
    public function remove($middleware)
    {
        $removal = 'Http\\Middlewares\\'.ucfirst($middleware);
        if ( ! isset($this->middlewareNames[$removal])) {  // Check middleware exist
            return;
        }
        foreach ($this->middleware as $key => $value) {
            $current = get_class($value);
            if ($current == $removal) {
                unset($this->middleware[$key]);
            }
        }
    }

    /**
     * Returns to all middleware class names
     * 
     * @return array
     */
    public function getMiddlewares()
    {
        return $this->middlewareNames;
    }

    /**
     * Execute the controller
     * 
     * @return void
     */
    public function call()
    {
        if ($this->c['config']['output']['compress'] AND extension_loaded('zlib')  // Do we need to output compression ?
            AND isset($_SERVER['HTTP_ACCEPT_ENCODING'])
            AND strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false
        ) {
            ob_start('ob_gzhandler');
        } else {
            ob_start();
        }
        call_user_func_array(array($this->class, $this->method), array_slice($this->class->uri->rsegments, 3));
    }

    /**
     * Register shutdown
     *
     * 1 . Write cookies if package loaded and we have queued cookies.
     * 2 . Check debugger module
     * 
     * @return void
     */
    public function close()
    {
        if ($this->c->loaded('cookie') AND count($cookies = $this->c['cookie']->getQueuedCookies()) > 0) {
            foreach ($cookies as $cookie) {
                $this->c['cookie']->write($cookie);
            }
        }
        $this->checkDebugger();
    }

    /**
     * Check debugger module is enabled ?
     * 
     * @return void
     */
    public function checkDebugger()
    {
        if ($this->c['config']['debugger']['enabled'] AND ! isset($_REQUEST['o_debugger'])) {
            $this->websocket->emit();
        }
    }
}

// END Http.php File
/* End of file Http.php

/* Location: .Obullo/Application/Http.php */