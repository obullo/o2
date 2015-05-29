<?php

namespace Obullo\Authentication;

use Obullo\Container\Container;

/**
 * Abstract Adapter
 * 
 * @category  Authentication
 * @package   Adapter
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2015 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/authentication
 */
abstract class AbstractAdapter
{
    /**
     * Container
     * 
     * @var object
     */
    protected $c;

    /**
     * Set container
     * 
     * @param object $c container
     * 
     * @return void
     */
    public function setContainer(Container $c)
    {
        $this->c = $c;
    }

    /**
     * Regenerate the session id
     *
     * @param bool $deleteOldSession whether to delete old session id
     * 
     * @return void
     */
    public function regenerateSessionId($deleteOldSession = true)
    {
        return $this->session->regenerateId($deleteOldSession);
    }

    /**
     * Verify password hash
     * 
     * @param string $plain plain  password
     * @param string $hash  hashed password
     * 
     * @return boolean | array
     */
    public function verifyPassword($plain, $hash)
    {
        $cost = $this->c['user']['security']['passwordNeedsRehash']['cost'];
        $password = $this->c['password'];

        if ($password->verify($plain, $hash)) {
            if ($password->needsRehash($hash, array('cost' => $cost))) {
                $value = $password->hash($plain, array('cost' => $cost));
                return array('hash' => $value);
            }
            return true;
        }
        return false;
    }

    /**
     * This method attempts to make
     * certain that only one record was returned in the resultset
     *
     * @return bool|Obullo\Authentication\Result 
     */
    abstract protected function validateResultSet();

    /**
     * This method attempts to validate that
     * the record in the resultset is indeed a record that matched the
     * identity provided to this adapter.
     *
     * @return AuthResult
     */
    abstract protected function validateResult();
}

// END AbstractAdapter.php File
/* End of file AbstractAdapter.php

/* Location: .Obullo/Authentication/AbstractAdapter.php */