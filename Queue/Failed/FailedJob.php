<?php

namespace Obullo\Queue\Failed;

use RuntimeException;
use Obullo\Container\ContainerInterface;

/**
 * Failed Job Class
 * 
 * @category  Queue
 * @package   Failed
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/queue
 */
abstract Class FailedJob
{
    /**
     * Db instance
     * 
     * @var object
     */
    public $db;

    /**
     * Db table
     * 
     * @var array
     */
    public $table;

    /**
     * Constuctor
     *
     * @param object $c container
     */
    public function __construct(ContainerInterface $c)
    {
        $config   = $c['config']->load('queue/workers');
        $database = $c['config']->load('database');
        $provider = $config['failed']['provider'];
        
        if (! isset($database['connections'][$provider['connection']])) {
            throw new RuntimeException(
                sprintf(
                    'Failed job database connection "%s" is not defined in your config database.php',
                    $provider['connection']
                )
            );
        }
        $this->db    = $c['app']->provider('database')->get(['connection' => $provider['connection']]);
        $this->table = $config['failed']['table'];
    }

    /**
     * Get database connection
     * 
     * @return object
     */
    public function getConnection()
    {
        return $this->db;
    }
    
}

// END FailedJob class

/* End of file FailedJob.php */
/* Location: .Obullo/Queue/Failed/FailedJob.php */