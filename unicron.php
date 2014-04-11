<?php

require_once 'app/bootstrap.php';

$app = function ($request, $response) use($di) {
    if('/api/v0/bitbucket/webhook' == $request->getPath() && $request->getMethod() == 'POST') {
        $request->on('data', function($data) use ($request, $response, $di){
            $data = json_decode($data, true);
            if(is_array($data)) {
                $projectService = $di->get('service.project');
                $repoName = substr($data['repository']['absolute_url'], 1, -1);
                $processedBranches = [];

                foreach($data['commits'] as $commit) {
                    $branch = $commit['branch'];
                    if(!in_array($branch, $processedBranches)) {
                        $project = $projectService->findProjectBy(compact('repoName', 'branch'));

                        if($project) {
                            $deploymentService = $di->get('service.deployment');
                            $deploymentService->deploy($project);
                        }
                    }
                }
            }
        });
        $response->writeHead(200, array('Content-Type' => 'text/plain'));
        $response->end("Thanks!\n");
    } else {
        var_dump('Bad Request');
        var_dump($request);
        $response->writeHead(404, array('Content-Type' => 'text/plain'));
        $response->end('404');
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
