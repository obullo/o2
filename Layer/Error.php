<?php

namespace Obullo\Layer;

/**
 * Layer error handler
 * 
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2015 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 */
class Error
{
    const ERROR_HEADER = '<div style="
    white-space: pre-wrap;
    white-space: -moz-pre-wrap;
    white-space: -pre-wrap;
    white-space: -o-pre-wrap;
    font-size:12px;
    font-family:Arial,Verdana,sans-serif;
    font-weight:normal;
    word-wrap: break-word; 
    background: #FFFAED;
    border: 1px solid #ddd;
    border-radius: 4px;
    -moz-border-radius: 4px;
    -webkit-border-radius:4px;
    padding:5px 10px;
    color:#E53528;
    font-size:12px;">';
    const ERROR_FOOTER = '</div>';

    /**
     * Format layer errors
     *
     * @param string $response layer response
     * 
     * @return mixed
     */
    public static function getError($response)
    {
        $error = str_replace('{Layer404}', '', $response);
        return (static::ERROR_HEADER . $error . static::ERROR_FOOTER);
    }
    
}