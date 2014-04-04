<?php

namespace Primus\Deployer;

class LocalFSDeployer
{
    public function deploy($project)
    {
        if(!is_dir($project->deployPath)) {
            mkdir(dirname($project->deployPath), 755, true);
            exec('git clone '.$project->repo.' '.$project->deployPath);
            exec('cd '.$project->deployPath.' && git checkout '.$project->branch);
        } else {
            exec('cd '.$project->deployPath.' && git checkout '.$project->branch);
            exec('cd '.$project->deployPath.' && git fetch origin '.$project->branch);
        }
    }
}