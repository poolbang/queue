<?php
require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once __DIR__ . '/config/define.php';
// init
\Swoft\App::$isInTest = true;
\Swoft\Bean\BeanFactory::init();
/* @var \Swoft\Bootstrap\Boots\Bootable $bootstrap*/
$bootstrap = \Swoft\App::getBean(\Swoft\Bootstrap\Bootstrap::class);
$bootstrap->bootstrap();
\Swoft\Bean\BeanFactory::reload([
    'application' => [
        'class' => \Swoft\Testing\Application::class,
        'inTest' => true
    ],
]);
$initApplicationContext = new \Swoft\Core\InitApplicationContext();
$initApplicationContext->init();