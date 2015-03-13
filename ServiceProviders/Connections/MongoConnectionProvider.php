<?php

namespace Obullo\ServiceProviders\Connections;

use Obullo\Container\Container;
use RuntimeException;
use UnexpectedValueException;

/**
 * Mongo Connection Provider
 *
 * @category  ConnectionProvider
 * @package   ServiceProviders
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/provider
 */
class MongoConnectionProvider extends AbstractConnectionProvider
{
    protected $c;            // Container
    protected $config;       // Configuration items
    protected $mongoClass;   // Mongo extension client name

    /**
     * Constructor ( Works one time )
     *
     * Automatically check if the Mongo PECL extension has been installed / enabled.
     *
     * @param string $c container
     */
    public function __construct(Container $c)
    {
        $this->c          = $c;
        $this->config     = $this->c['config']->load('mongo');  // Load nosql configuration file
        $this->mongoClass = (version_compare(phpversion('mongo'), '1.3.0', '<')) ? '\Mongo' : '\MongoClient';

        $this->setKey('mongo.connection.');

        if (! class_exists($this->mongoClass, false)) {
            throw new RuntimeException(
                sprintf(
                    'The %s extension has not been installed or enabled.',
                    trim($this->mongoClass, '\\')
                )
            );
        }
    }

    /**
     * Register all connections as shared services ( Works one time )
     *
     * @return void
     */
    public function register()
    {
        foreach ($this->config['connections'] as $key => $val) {
            $this->c[$this->getKey($key)] = function () use ($val) {  //  create shared connections
                return $this->createConnection($val['server'], $val);
            };
        }
    }

    /**
     * Creates mongo connections
     *
     * @param string $server dsn
     * @param array  $params connection parameters
     *
     * @return void
     */
    protected function createConnection($server, $params)
    {
        $options = isset($params['options']) ? $params['options'] : ['connect' => true];

        return new $this->mongoClass($server, $options);
    }

    /**
     * Retrieve shared mongo connection instance from connection pool
     *
     * @param array $params provider parameters
     *
     * @return object MongoClient
     */
    public function getConnection($params = [])
    {
        if (! isset($params['connection'])) {
            $params['connection'] = array_keys($this->config['connections'])[0];  //  Set default connection
        }
        if (! isset($this->config['connections'][$params['connection']])) {
            throw new UnexpectedValueException(
                sprintf(
                    'Connection key %s not exists in your mongo.php config file.',
                    $params['connection']
                )
            );
        }

        return $this->c[$this->getKey($params['connection'])];  // return to shared connection
    }

    /**
     * Create a new mongo connection
     *
     * if you don't want to add it to config file and you want to create new one.
     *
     * @param array $params connection parameters
     *
     * @return object mongo client
     */
    public function factory($params = [])
    {
        if (! isset($params['server'])) {
            throw new UnexpectedValueException("Mongo connection provider requires server parameter.");
        }
        $cid = $this->getKey($this->getConnectionId($params));

        if (! $this->c->exists($cid)) { //  create shared connection if not exists
            $this->c[$cid] = function () use ($params) {  //  create shared connections
                return $this->createConnection($params['server'], $params);
            };
        }

        return $this->c[$cid];
    }

    /**
     * Close all "active" connections
     */
    public function __destruct()
    {
        foreach ($this->config['connections'] as $key => $val) {  //  Close shared connections
            $val = null;
            if ($this->c->loaded($this->getKey($key))) {
                $connection = $this->c[$this->getKey($key)];
                foreach ($connection->getConnections() as $con) {
                    $connection->close($con['hash']);
                }
            }
        }
    }
}

// END MongoConnectionProvider.php class
/* End of file MongoConnectionProvider.php */

/* Location: .Obullo/ServiceProviders/Connections/MongoConnectionProvider.php */
