<?php

use Aura\Cli\CliFactory;

$cli_factory = new CliFactory();
$context = $cli_factory->newContext($GLOBALS);

$di->params['Aura\Sql_Query\QueryFactory'] = [
    'db' => 'mysql'
];

$di->set('database.handler', function() use ($di) {
    return new \Aura\Sql\ExtendedPdo(
        'mysql:host='.$di->get('config')['db']['host'].';dbname='.$di->get('config')['db']['dbname'],
        $di->get('config')['db']['username'],
        $di->get('config')['db']['password']
    );
});
$di->set('database.query_handler', $di->lazyNew('Aura\Sql_Query\QueryFactory'));
$di->set('cli.stdio', function() use ($cli_factory) {
    return $cli_factory->newStdio();
});
$di->set('cli.context', $context);

$di->set('logistics.adapter', function() use ($di) {
    return new \PhpORM\Storage\AuraExtendedPdo($di->get('database.handler'), $di->get('database.query_handler'));
});

$di->set('repository.project', function() use ($di) {
    return new \PhpORM\Repository\DBRepository($di->get('logistics.adapter'), new \Primus\Project\Project());
});

$di->set('repository.projectTask', function() use ($di) {
    return new \PhpORM\Repository\DBRepository($di->get('logistics.adapter'), new \Primus\Project\ProjectTask());
});

$di->set('service.project', function() use ($di) {
    return new \Primus\Service\ProjectService($di->get('repository.project'), $di->get('repository.projectTask'));
});

$di->set('service.deployment', $di->lazyNew('Primus\Service\DeploymentService'));

$di->params['Primus\App\Command\ProjectsCommand'] = [
    'di' => $di,
    'context' => $di->get('cli.context'),
    'projectService' => $di->get('service.project'),
];