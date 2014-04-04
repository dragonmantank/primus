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
        $this->localDeploy($project);
    }

    protected function localDeploy($project)
    {
        $deployer = new LocalFSDeployer();
        $deployer->deploy($project);
    }
}