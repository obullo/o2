<?php

/**
 * Obullo
 * 
 * @category  Core
 * @package   Obullo
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/package/obullo
 */

$start = microtime(true);  // Run Timer
/*
 * ------------------------------------------------------
 *  Before request event
 * ------------------------------------------------------
 */
$c['event']->fire('before.request');

/*
 * ------------------------------------------------------
 *  Load core components
 * ------------------------------------------------------
 */
$router 	= $c->load('router');
$response 	= $c->load('response');
$pageUri    = "{$router->fetchDirectory()} / {$router->fetchClass()} / index";
$controller = PUBLIC_DIR . $router->fetchTopDirectory(DS). $router->fetchDirectory() . DS .'controller'. DS . $router->fetchClass() . '.php';

if ( ! file_exists($controller)) {
    $response->show404($pageUri);
}

require $controller;  // Include the controller file.

$className = $router->fetchClass();

if ( ! class_exists($className, false)) {  // Check method exist or not
    $response->show404($pageUri);
}

$class = new $className;  // Call the controller


$docs = null;
if ($c['config']['annotations']) {
    
    $docs = new App\Annotations\Reader($class);
    
    // $reflection->getDocComment();

    // Read doc blocks 
    // if we have before.filter("") run
    // 
    //
}

/*
 * ------------------------------------------------------
 *  Before controller event
 * ------------------------------------------------------
 */
$c['event']->fire('before.controller', array($class, $docs));


if (method_exists($class, 'load')) {
    $class->load();
}
foreach (get_class_methods($class) as $method) {
    if ($method != 'index' AND $method != 'load' AND strpos($method, '_') !== 0) {
        throw new RunTimeException(
            'Just one public method allowed because of Obullo has a principle "One Index Method Per Controller".
            If you want to use private methods try to add underscore prefix ( _methodname ). e.g. <pre>private function _methodname() {}</pre>'
        );
    }
}
if ( ! method_exists($class, 'index')) {  // Check method exist or not
    $response->show404($pageUri);
}
$arguments = array_slice($c->load('uri')->rsegments, 2);

if (method_exists($class, '_remap')) {  // Is there any "remap" function? If so, we call it instead
    $class->_remap('index', $arguments);
} else {

    // Call the requested method. Any URI segments present (besides the directory / class / method) 
    // will be passed to the method for convenience
    // directory = 0, class = 1,  arguments = 2 (  method always = index )
    call_user_func_array(array($class, 'index'), $arguments);
}

/*
 * ------------------------------------------------------
 *  After controller event
 * ------------------------------------------------------
 */
$c['event']->fire('after.controller', array($class));

/*
 * ------------------------------------------------------
 *  Send the final rendered output to the browser
 * ------------------------------------------------------
 */
$response->sendOutput();    // Send the final rendered output to the browser

/*
 * ------------------------------------------------------
 *  After controller event
 * ------------------------------------------------------
 */
$c['event']->fire('after.response', array($start));


// END Obullo.php File
/* End of file Obullo.php

/* Location: .Obullo/Obullo.php */