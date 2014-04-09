<?php

$loader = require_once __DIR__.'/../vendor/autoload.php';
$loader->add('', __DIR__.'/../src');

use Aura\Di\Container as DiContainer;
use Aura\Di\Factory as DiFactory;
use Aura\Includer\Includer;
//use Aura\Dispatcher\Dispatcher;
use Primus\Dispatcher;

$di = new DiContainer(new DiFactory());
$dispatcher = new Dispatcher();

$includer = new Includer();
$includer->setVars(array(
    'di' => $di,
));
$includer->setDirs(array(
    __DIR__.'/config'
));
$includer->setFiles(array(
    'global.php',
    'dependencies.php',
));
$includer->load();

$dispatcher->setObjectParam('controller');
$dispatcher->setMethodParam('action');
$dispatcher->setObject('projects', $di->lazyNew('Primus\App\Command\ProjectsCommand'));