<?php

namespace Obullo\Permissions\Rbac\Resource\Object;

use Obullo\Permissions\Rbac\User;

/**
 * Element Permissions
 * 
 * @category  Permissions
 * @package   Element
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @author    Ali Ihsan Caglayan <ihsancaglayan@gmail.com>
 * @author    Ersin Guvenc <eguvenc@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/permissions
 */
Class Element
{
    /**
     * Object name
     * 
     * @var string
     */
    public $objectName;

    /**
     * Constructor
     * 
     * @param object $c container
     */
    public function __construct($c)
    {
        echo 'zz';
        $this->c = $c;
    }

    /**
     * Has operation
     * 
     * @param mix $permName   permission name
     * @param mix $opName     operations ( view,update,delete,insert,save )
     * @param int $expiration expiration time
     * 
     * @return boolean
     */
    public function getPermissions($permName, $opName, $expiration = 7200)
    {
        $opName   = $this->c['rbac.user']->arrayConvert($opName);
        $permName = $this->c['rbac.user']->arrayConvert($permName);
        
        $key = User::CACHE_HAS_ELEMENT_PERMISSIONS . $this->c['rbac.user']->getId() .':'. $this->c['rbac.user']->hash($permName) .':'. $this->c['rbac.user']->hash($opName);
        $resultArray = $this->c['rbac.user']->cache->get($key);
        $resultArray = false;

        if ($resultArray === false) { // If not exist in the cache
            $queryResultArray = $this->c['model.user']->hasElementPermissionSqlQuery($this->objectName, $permName, $opName);  // do sql query
            $resultArray      = ($queryResultArray == false) ? 'empty' : $queryResultArray;
            $this->c['rbac.user']->cache->set($key, $resultArray, $expiration);
        }
        if ($resultArray == 'empty') {
            return false;
        }
        return $resultArray;
    }
}


// END Child.php File
/* End of file Child.php

/* Location: .Obullo/Permissions/Rbac/Child.php */
