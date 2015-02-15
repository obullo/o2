<?php

namespace Obullo\ServiceProviders;

use Obullo\Container\Container;

/**
 * Mongo Service Provider
 *
 * @category  ServiceProvider
 * @package   ServiceProviders
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/docs/service_providers
 */
Class PdoServiceProvider
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
        $connector = new PdoConnectionProvider($c);    // Register all Connectors as shared services
        $connector->register();
        return $connector->getConnection($params);     // Get existing connection
    }
}

// END MongoServiceProvider Class

/* End of file MongoServiceProvider.php */
/* Location: .Obullo/ServiceProviders/MongoServiceProvider.php */
