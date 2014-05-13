<?php

namespace Unicron\App\Controller;

use Unicron\Payload;

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
        $payload = new Payload();

        $this->request->on('data', function($data) use ($request, $response, $di, $payload){
            $payload->addChunk($data);
        });

        $this->request->on('end', function() use ($request, $response, $di, $payload) {
            parse_str($payload->getPayload(), $output);
            file_put_contents(UNICRON_LOGS_DIR.'/'.date('Ymd-His').'.log', $payload->getPayload());
            $payload = urldecode($output['payload']);
            $data = json_decode($payload, true);
            if(is_array($data)) {
                $repoName = substr($data['repository']['absolute_url'], 1, -1);
                $processedBranches = [];

                foreach($data['commits'] as $commit) {
                    $branch = $commit['branch'];
                    if(!in_array($branch, $processedBranches)) {
                        $project = exec(escapeshellcmd(PRIMUS_COMMAND.' projects search '.$repoName.' '.$branch));

                        if(!empty($project)) {
                            echo sprintf('[%s] Deploying %s', date('Y-m-d H:i:s'), $project).PHP_EOL;
                            exec(escapeshellcmd(PRIMUS_COMMAND.' projects deploy "'.$project.'"'));
                        }
                    }
                }
            }
            try {
                $response->writeHead(200, array('Content-Type' => 'text/plain'));
                $response->end('Completed');
            } catch(\Exception $e) {
                echo sprintf('[%s] Error deploying %s: %s', date('Y-m-d H:i:s'), $project, $e->getMessage()).PHP_EOL;
            }
            return;
        });
    }
}
