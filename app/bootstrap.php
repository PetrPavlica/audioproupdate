<?php

require __DIR__ . '/../vendor/autoload.php';

$configurator = new Nette\Configurator;

$ipArr = [
    //'79.141.242.250', // WebRex Jihlava
    //'195.70.143.194', // Ivoš
    '127.0.0.1', // localhost
    //'83.240.58.198',
    //'46.252.224.14'
];

$configurator->setDebugMode($ipArr); // enable for your remote IP
$configurator->enableDebugger(__DIR__ . '/../log');

$configurator->setTimeZone('Europe/Prague');
$configurator->setTempDirectory(__DIR__ . '/../temp');

$baseUri = trim(dirname($_SERVER[ 'SCRIPT_NAME' ]), '\\/');
if (PHP_SAPI !== 'cli') {
    $baseUri = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . (empty($baseUri) ? '/' : "/$baseUri/");
}

$configurator->addParameters(array(
    'baseUri' => $baseUri, // hack for local assets urls
));

$configurator->enableTracy();
error_reporting(~E_USER_DEPRECATED);

$configurator->createRobotLoader()
    ->addDirectory(__DIR__)
    ->register();

$configurator->addConfig(__DIR__ . '/core/config/config.neon');
if (PHP_SAPI === 'cli') {
    $configurator->addConfig(__DIR__ . '/core/config/config.local.neon');
} else {
    if ($_SERVER['SERVER_NAME'] == 'localhost') {
        $configurator->addConfig(__DIR__ . '/core/config/config.local.neon');
    } else {
        if (stripos($_SERVER['SERVER_NAME'], 'worldwidewebrex.com') !== false || stripos($_SERVER['SERVER_NAME'], 'webrex.cz') !== false) {
            $configurator->addConfig(__DIR__ . '/core/config/config.dev.neon');
        } else {
            $configurator->addConfig(__DIR__ . '/core/config/config.server.neon');
        }
    }
}

$configurator->addConfig(__DIR__ . '/front/config/config.neon');
$configurator->addConfig(__DIR__ . '/front/config/config.local.neon');

$configurator->addConfig(__DIR__ . '/intra/config/config.neon');
$configurator->addConfig(__DIR__ . '/intra/config/config.local.neon');

include_once 'core/model/utils/BarDump.php';

try {
    $container = $configurator->createContainer();
} catch (\Nette\InvalidStateException $ex) {
    \Tracy\Debugger::log($ex->getMessage());
    if ($ex->getMessage() == "Failed to decode session object. Session has been destroyed") {
        $container = $configurator->createContainer();
    } else {
        throw $ex;
    }
}
return $container;
