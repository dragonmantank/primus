<?php

use Aura\Cli\CliFactory;

$cli_factory = new CliFactory();
$context = $cli_factory->newContext($GLOBALS);

$di->params['Aura\Sql_Query\QueryFactory'] = array(
    'db' => 'mysql'
);

$di->set('database.handler', function() use ($di) {
    $config = $di->get('config');
    return new \Aura\Sql\ExtendedPdo(
        'mysql:host='.$config['db']['host'].';dbname='.$config['db']['dbname'],
        $config['db']['username'],
        $config['db']['password']
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

$di->set('repository.buildProperty', function() use ($di) {
    return new \PhpORM\Repository\DBRepository($di->get('logistics.adapter'), new \Primus\Project\BuildProperty());
});

$di->set('service.project', function() use ($di) {
    return new \Primus\Service\ProjectService($di->get('repository.project'), $di->get('repository.buildProperty'));
});

$di->set('service.deployment', $di->lazyNew('Primus\Service\DeploymentService'));

$di->params['Primus\App\Command\ProjectsCommand'] = array(
    'di' => $di,
    'context' => $di->get('cli.context'),
    'projectService' => $di->get('service.project'),
);
