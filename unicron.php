<?php
date_default_timezone_set('America/New_York');

require_once 'app/bootstrap.php';

define('PRIMUS_COMMAND', 'php '.__DIR__.'/primus.php');
define('UNICRON_LOGS_DIR', __DIR__.'/logs');

$router_factory = new Aura\Router\RouterFactory();
$router = $router_factory->newInstance();
$router
    ->addPost('bitbucket_webhook', '/api/v0/bitbucket/webhook')
    ->addValues(array(
        'controller' => 'bitbucket',
        'action' => 'webhook',
    ))
;

$app = function ($request, $response) use($di, $router) {
    $server['REQUEST_METHOD'] = $request->getMethod();
    $route = $router->match($request->getPath(), $server);

    if(!$route) {
        echo sprintf('[%s] %s 404', date('Y-m-d H:i:s'), $request->getPath()).PHP_EOL;
        $response->writeHead(404, array('Content-Type' => 'text/plain'));
        $response->end('404');
        return;
    } else {
        $controllerName = 'Unicron\\App\\Controller\\'.ucfirst($route->params['controller']).'Controller';
        $actionName = $route->params['action'].'Action';

        $controller = new $controllerName($request, $response, $di);
        $controller->$actionName();
    }
};

$loop = React\EventLoop\Factory::create();
$socket = new React\Socket\Server($loop);
$http = new React\Http\Server($socket, $loop);

$httpConfig = $di->get('config')['http'];
$http->on('request', $app);
echo "Server running at http://".$httpConfig['ip'].":".$httpConfig['port']."\n";

$socket->listen($httpConfig['port'], $httpConfig['ip']);
$loop->run();
