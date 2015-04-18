<?php

namespace Obullo\Validator;

/**
 * IsJson Class
 * 
 * @category  Validator
 * @package   IsJson
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/validator
 */
class IsJson
{
    /**
     * Constructor
     * 
     * @param object $c container
     */
    public function __construct($c)
    {
        $this->c = $c;
    }
    
    /**
     * is Json
     * 
     * @param string $str string
     * 
     * @return bool
     */    
    public function isValid($str)
    {
        return ( ! is_object(json_decode($str))) ? false : true;
    }
}

// END IsJson Class
/* End of file IsJson.php */

/* Location: .Obullo/Validator/IsJson.php */
