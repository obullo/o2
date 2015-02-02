<?php

namespace Obullo\Authentication;

use Obullo\Container\Container,
    Obullo\Authentication\User\UserActivity,
    Obullo\Authentication\User\UserIdentity,
    Obullo\Authentication\User\UserLogin;

/**
 * O2 Authentication - User Service Provider
 *
 * @category  Authentication
 * @package   AuthServiceProvider
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/authentication
 */
Class AuthServiceProvider
{
    /**
     * Container
     * 
     * @var object
     */
    protected $c;

    /**
     * Service configuration parameters
     * 
     * @var array
     */
    protected $config = array();

    /**
     * Constructor
     * 
     * @param object $c      container
     * @param array  $params configuration parameters
     */
    public function __construct(Container $c, $params = array())
    {
        $this->c = $c;
        $this->config = $c['config']->load('auth');
        
        $this->c['auth.params'] = $params;

        $this->c['auth.storage'] = new $this->config['cache']['storage']($this->c);
        $this->c['auth.adapter'] = new $params['db.adapter']($this->c);
        $this->c['user.model']   = new $params['db.model']($this->c);

        $this->c['auth.login']    = new UserLogin($this->c);
        $this->c['auth.identity'] = new UserIdentity($this->c);
        $this->c['auth.activity'] = new UserActivity($this->c);
    }

    /**
     * Service class loader
     * 
     * @param string $class name
     * 
     * @return object | null
     */
    public function __get($class)
    {
        return $this->c['auth.'.strtolower($class)]; // Services: $this->user->login, $this->user->identity, $this->user->activity ..
    }

}

// END AuthServiceProvider.php File
/* End of file AuthServiceProvider.php

/* Location: .Obullo/Authentication/AuthServiceProvider.php */