<?php

namespace Primus\Service;

use Primus\Deployer\LocalFSDeployer;
use Primus\Project\Project;

class DeploymentService
{
    /**
     * Deploys a project to it's deployment path
     * Right now this assumes a local FS path, but moved this to it's own service so we can expand it in the future
     *
     * @param Project $project
     */
    public function deploy(Project $project)
    {
        echo 'Deploying project '.$project->name.PHP_EOL;
        $tasks = $project->getTasks();
        foreach($tasks as $task) {
            $class = "Primus\\Deployer\\Task\\".$task->task;
            $deployTask = new $class;
            if(method_exists($deployTask, 'predeploy')) {
                $this->runCommands($deployTask->predeploy($project), $project);
            }
        }

        $this->localDeploy($project);

        foreach($tasks as $task) {
            $class = "Primus\\Deployer\\Task\\".$task->task;
            $deployTask = new $class;
            if(method_exists($deployTask, 'postdeploy')) {
                $this->runCommands($deployTask->postdeploy($project), $project);
            }
        }
        echo 'Deployment finished'.PHP_EOL;
    }

    protected function localDeploy($project)
    {
        $deployer = new LocalFSDeployer();
        $deployer->deploy($project);
    }

    protected function runCommands($commands, $project)
    {
        $currentDir = getcwd();
        chdir($project->deployPath);

        foreach($commands as $command) {
            exec($command);
        }

        chdir($currentDir);
    }
}