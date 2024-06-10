<?php

/*if ($_SERVER['REMOTE_ADDR'] != '213.226.225.71') {
    require '.maintenance.php';
}*/

$container = require __DIR__ . '/../app/bootstrap.php';

define('APP_DIR', __DIR__ . '/../app');

$container->getByType(Nette\Application\Application::class)->run();
