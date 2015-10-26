<?php

namespace Obullo\Log\Cli;

use Obullo\Container\ContainerInterface as Container;

/**
 * File reader
 * 
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2015 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 */
class File
{
    /**
     * Follow logs
     * 
     * @param string $c     container
     * @param string $dir   sections ( http, ajax, cli )
     * @param string $table tablename
     * 
     * @return void
     */
    public function follow(Container $c, $dir = 'http', $table = null)
    {
        $c = $table = null;  // Unused variables
        $directions = [
            'http'  => '/resources/data/logs/http.log',
            'cli'   => '/resources/data/logs/cli.log',
            'ajax'  => '/resources/data/logs/ajax.log',
        ];
        if (! isset($directions[$dir])) {
            echo("\n\n\033[1;31mPath Error: $dir item not defined in ".__CLASS__." \033[0m\n");
            exit;
        }
        $file = $directions[$dir];
        echo "\n\33[0;37mFollowing File Handler ".ucfirst($dir)." Logs ...\33[0m\n";

        $size = 0;
        while (true) {
            clearstatcache();           // Clear the cache
            if (! file_exists($file)) { // Start process when file exists.
                continue;
            }
            $currentSize = filesize($file); // Continue the process when file size change.
            if ($size == $currentSize) {
                usleep(50);
                continue;
            }
            if (! $fh = fopen($file, 'rb')) {
                echo("\n\n\033[1;31mPermission Error: You need to have root access or log folder has not got write permission.\033[0m\n");
                die;
            }
            fseek($fh, $size);
            $printer = new Printer;
            $i = 0;
            while ($line = fgets($fh)) {
                $printer->printLine($i, $line);
                $i++;
            }
            fclose($fh);
            clearstatcache();
            $size = $currentSize;
        }

    }

}