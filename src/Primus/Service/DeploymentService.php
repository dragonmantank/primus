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

        passthru(PRIMUS_ROOT.'/vendor/bin/phing -buildfile '.PRIMUS_ROOT.'/projects/'.$project->getSlug().'/build.xml');

        echo 'Deployment finished'.PHP_EOL;
    }
}