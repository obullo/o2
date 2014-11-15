<?php

namespace Obullo\Cli\LogFollower;

/**
 * File Follower
 * 
 * @category  Cli
 * @package   LogFollower
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/package/Cli
 */
Class File
{
    /**
     * Follow logs
     * 
     * @param string $c     container
     * @param string $route sections ( http, ajax, cli )
     * 
     * @return void
     */
    public function follow($c, $route = 'http')
    {
        $path = str_replace('/', DS, trim($c->load('config')['log']['file']['path'][$route], '/'));
        $file = $path;
        if (strpos($path, 'data') === 0) {  // Replace "data" word to application data path
            $file = str_replace('data', DS . trim(DATA, DS), $path);
        }
        echo "\n\33[1;36mFollowing \33[1;37m\33[1;46mFile Handler ".ucfirst($route)."\33[0m\33[1;36m logs ...\33[0m\n";

        static $lines = array();
        $size = 0;
        while (true) {
            clearstatcache(); // Clear the cache
            if ( ! file_exists($file)) { // Start process when file exists.
                continue;
            }
            $currentSize = filesize($file); // Continue the process when file size change.
            if ($size == $currentSize) {
                usleep(50);
                continue;
            }
            if ( ! $fh = fopen($file, 'rb')) {
                echo("\n\n\033[1;31mPermission Error: You need to have root access or log folder has not got write permission.\033[0m\n");
                die;
            }
            fseek($fh, $size);
            $printer = new Printer\Colorful;
            $i = 0;
            while ($line = fgets($fh)) {
                $printer->printLine($i, $line);
                $i++;
                $lines[$line] = $i;
            }
            fclose($fh);
            clearstatcache();
            $size = $currentSize;
        }

    }

}

// END File class

/* End of file File.php */
/* Location: .Obullo/Cli/LogFollower/File.php */