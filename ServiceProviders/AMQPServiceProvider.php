<?php

namespace Obullo\ServiceProviders;

use Obullo\ServiceProviders\AMQPConnectionProvider,
    Obullo\Container\Container;

/**
 * AMQP Service Provider
 *
 * @category  ServiceProvider
 * @package   ServiceProviders
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/docs/serviceProviders
 */
Class AMQPServiceProvider
{
    /**
     * Registry
     *
     * @param object $c      container
     * @param array  $params parameters
     * 
     * @return void
     */
    public function register(Container $c, $params = array())
    {
        if ( ! AMQPConnectionProvider::isRegistered()) {         // Just one time register the shared objects
            $connector = AMQPConnectionProvider::getInstance($c); 
            $connector->register();
        }
        $connector = AMQPConnectionProvider::getInstance($c);

        if ( ! isset($params['connection'])) {                // Do factory ( creates new connection )
            return $connector->factory($params);
        }
        return $connector->getConnection($params);           // Get existing connection
    }
}

// END AMQPServiceProvider Class

/* End of file AMQPServiceProvider.php */
/* Location: .Obullo/ServiceProviders/AMQPServiceProvider.php */
