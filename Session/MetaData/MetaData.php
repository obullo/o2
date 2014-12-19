<?php

namespace Obullo\Session\MetaData;

/**
 * MetaData Storage
 * 
 * @category  Session
 * @package   MetaData
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT
 * @link      http://obullo.com/package/session
 */
Class MetaData
{
    /**
     * Time
     * 
     * @var integer
     */
    public $now;

    /**
     * Ip address
     * 
     * @var string
     */
    public $ipAddress;

    /**
     * User Agent
     * 
     * @var string
     */
    public $userAgent;

    /**
     * Session class instance
     * 
     * @var object
     */
    public $session;

    /**
     * Logger class
     * 
     * @var object
     */
    public $logger;

    /**
     * Cache provider
     * 
     * @var object
     */
    public $cache;

    /**
     * Constructor
     * 
     * @param object $c       container
     * @param object $params  parameters
     * @param object $session session object
     */
    public function __construct($c, $params, $session)
    {
        $this->params = $params;
        $this->session = $session;
        $this->now = $this->session->getTime();
        $this->ipAddress = $c->load('request')->getIpAddress();
        $this->userAgent = $c->load('request')->server('HTTP_USER_AGENT');
        $this->cache = $c->load('service/provider/cache');
        $this->logger = $c->load('service/logger');
    }

    /**
     * Compare meta data with user data if something went 
     * wrong destroy the session and say good bye to user.
     * 
     * @return boolean
     */
    public function isValid()
    {
        $this->metaData = $this->read();
        if ( ! isset($this->metaData['sid'])
            OR ! isset($this->metaData['ip']) 
            OR ! isset($this->metaData['ua']) 
            OR ! isset($this->metaData['la'])
        ) {
            $this->session->destroy();
            return false;
        }
        if (($this->metaData['la'] + $this->params['lifetime']) < $this->now) {  // Is the session current?
            $this->logger->notice('Session expired', array('session_id' => session_id()));
            $this->session->destroy();
            return false;
        }
        if ($this->params['metaData']['matchIp'] == true AND $this->metaData['ip'] != $this->ipAddress) {  // Does the IP Match?
            $this->logger->notice('Session meta data is not valid', $this->metaData);
            $this->session->destroy();
            return false;
        }
        if ($this->params['metaData']['matchUserAgent'] == true AND trim($this->metaData['ua']) != $this->userAgent) {  // Does the User Agent Match?
            $this->session->destroy();
            return false;
        }
        return true;
    }

    /**
     * Stores meta data into $this->metaData variable.
     * 
     * @return void
     */
    public function build()
    {
        $this->metaData['sid'] = session_id(); // Don't reset array data like $this->metaData = array()
        $this->metaData['ip'] = $this->ipAddress;
        $this->metaData['ua'] = $this->userAgent;
        $this->metaData['la'] = $this->now; // last activity
    }

    /**
     * When first initializiation of session we create the session user 
     * meta data and we could not reach "user_id" and "username" items from $_SESSION variable.
     * Thats why we need to use this function in $this->set() method.
     * 
     * @param array $newData new session set data
     * 
     * @return void
     */
    public function buildUserData($newData = array())
    {
        if (isset($newData['user_id'])) {
            $this->metaData['uid'] = $newData['user_id'];
        }
        if (isset($newData['username'])) {
            $this->metaData['uname'] = $newData['username'];
        }
        if ($user_id = $this->session->get('user_id')) {
            $this->metaData['uid'] = $user_id;
        }
        if ($username = $this->session->get('username')) {
            $this->metaData['uname'] = $username;
        }
    }

    /**
     * Get latest metadata 
     * 
     * @return array metadata
     */
    public function getMetaData()
    {
        return $this->metaData;
    }

    /**
     * Create meta data
     * 
     * @return void
     */
    public function create()
    {
        $this->build();
        $this->buildUserData();
        $_SESSION['_o2_meta'] = json_encode($this->metaData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Update meta data
     * 
     * @return void
     */
    public function update()
    {
        if (($this->metaData['la'] + $this->params['timeToUpdate']) >= $this->now) {  // We only update the session every 5 seconds by default
            return;
        }
        $this->buildUserData();
        $this->metaData['la'] = $this->now; // Update the session ID and la
        $this->create($this->metaData);
    }

    /**
     * Remove meta data
     * 
     * @return void
     */
    public function remove()
    {
        unset($_SESSION['_o2_meta']);
    }

    /**
     * Read meta data
     * 
     * @return array
     */
    public function read()
    {
        if (isset($_SESSION['_o2_meta'])) {
            return json_decode($_SESSION['_o2_meta'], true);
        }
        return array();
    }
}

// END MetaData.php File
/* End of file MetaData.php

/* Location: .Obullo/Session/MetaData.php */