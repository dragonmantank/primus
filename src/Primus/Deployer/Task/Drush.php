<?php

namespace Primus\Deployer\Task;

class Drush
{
    /**
     * Commands that run after a deployment has finished
     *
     * @param $project
     * @return array
     */
    public function postDeploy($project)
    {
        return array(
            'drush fra -y',
            'drush cc all',
        );
    }
}
