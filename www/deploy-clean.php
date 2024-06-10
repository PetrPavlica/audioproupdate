<?php

$time_pre = microtime(true);


$proxiesPath = __DIR__ . '/../temp/proxies';
$cashePath = __DIR__ . '/../temp/cache';

rrmdir($cashePath);
echo "OK - cashe <br>";

rrmdir($proxiesPath);
echo "OK - proxies <br><br>";

$time_post = microtime(true);
$exec_time = $time_post - $time_pre;

echo 'time: ' . $exec_time;

function rrmdir($dir) {
    if (is_dir($dir)) {
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                if (is_dir($dir . "/" . $object))
                    rrmdir($dir . "/" . $object);
                else
                    unlink($dir . "/" . $object);
            }
        }
        rmdir($dir);
    }
}

?>