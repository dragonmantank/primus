<?php

require_once 'app/bootstrap.php';

$context = $di->get('cli.context');
$argv = $context->argv->get();
if(count($argv) < 2) {
    $stdio = $di->get('cli.stdio');
    $stdio->outln('Please enter a commmand to run');
    die();
}
$controller = $argv[1];
if(isset($argv[2])) {
    $action = $context->argv->get()[2].'Action';
} else {
    $action = 'indexAction';
}

$dispatcher->__invoke(compact('controller', 'action'));
