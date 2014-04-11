<?php

namespace Unicron\App\Controller;

class BitbucketController
{
    protected $di;
    protected $request;
    protected $response;

    public function __construct($request, $response, $di)
    {
        $this->di = $di;
        $this->request = $request;
        $this->response = $response;
    }

    public function webhookAction()
    {
        $request = $this->request;
        $response = $this->response;
        $di = $this->di;

        $this->request->on('data', function($data) use ($request, $response, $di){
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
                            echo sprintf('[%s] Deploying %s', date('Y-m-d H:i:s'), $project->name).PHP_EOL;
                            $deploymentService = $di->get('service.deployment');
                            $deploymentService->deploy($project);
                        }
                    }
                }
                $response->writeHead(202, array('Content-Type' => 'text/plain'));
                $response->end('Completed');
            }
        });
    }
}