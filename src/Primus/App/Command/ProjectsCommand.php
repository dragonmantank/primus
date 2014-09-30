<?php

namespace Primus\App\Command;

use Primus\Deployer\LocalFSDeployer;

class ProjectsCommand
{
    protected $args;
    protected $di;
    protected $context;

    /**
     * @var \Primus\Service\ProjectService
     */
    protected $projectService;
    protected $stdio;

    public function __construct($args, $di, $context, $projectService)
    {
        $this->di = $di;
        $this->context = $context;
        $this->projectService = $projectService;
        $this->args = $this->context->argv->get();

        $this->stdio = $this->di->get('cli.stdio');
    }

    public function editbuildpropertiesAction()
    {
        $args = $this->context->argv->get();
        $project = $this->projectService->fetchProject($args[3]);
        $property = '';
        if($project) {
            do {
                $this->displayProjectData($project);
                $this->stdio->outln('');
                $this->stdio->out('Enter the property to edit/add: ');
                $property = $this->stdio->in();

                $properties = $project->getBuildProperties();
                $existing = false;
                foreach($properties as $currentProperty) {
                    if($currentProperty->property == $property) {
                        $existing = true;
                        $default = $currentProperty->propertyValue;
                        $this->stdio->out('Please enter a value for '.$currentProperty->property.' ['.$default.']: ');
                        $newValue = $this->stdio->in();
                        if(!empty($newValue)) {
                            $currentProperty->propertyValue = $newValue;
                            $this->projectService->updateBuildProperty($project, $property, $newValue);
                        } else {
                            $this->stdio->out('No value entered, do you want to remove this property (y/n)? ');
                            $confirmation = strtolower($this->stdio->in());

                            if($confirmation == 'y') {
                                $this->projectService->removeBuildProperty($project, $property);
                            }
                        }
                    }
                }

                if(!$existing && !empty($property)) {
                    $this->stdio->out('Please enter a value for '.$property.': ');
                    $newValue = $this->stdio->in();
                    if(!empty($newValue)) {
                        $this->projectService->addBuildProperty($project, $property, $newValue);
                    }
                }

                if('repo.branch' == $property && !empty($newValue)) {
                    $project->branch = $newValue;
                    $this->projectService->save($project);
                }
                
                $this->buildPhingConfig($project);
            } while(!empty($property));
        }
    }

    public function buildconfigAction()
    {
        $args = $this->context->argv->get();
        $project = $this->projectService->fetchProject($args[3]);
        if($project) {
            $this->buildPhingConfig($project);
        }
    }

    protected function buildPhingConfig($project)
    {
        $projectDir = PRIMUS_ROOT.'/projects/'.$project->getSlug();
        $phingDir = PRIMUS_ROOT.'/app/config/phing';
        @mkdir($projectDir, 0755, true);
        copy($phingDir.'/build.dist.xml', $projectDir.'/build.xml');

        $config = '';
        foreach($project->getBuildProperties() as $property) {
            $config .= $property->property.'='.$property->propertyValue."\n";
        }
        file_put_contents($projectDir.'/build.properties', $config);

        $this->stdio->outln('Created new config file for '.$project->name.':');
        $this->stdio->outln(str_repeat('=', 80));
        $this->stdio->outln($config);
    }

    /**
     * Creates a new project that we can set up
     */
    public function createAction()
    {
        $opts = $this->context->getopt(array('name:', 'repo:', 'branch:', 'deploy-path:'));
        $stdio = $this->di->get('cli.stdio');
        $projectService = $this->di->get('service.project');

        $name = $opts->get('--name');
        $repo = $opts->get('--repo');
        $repoName = $opts->get('--repo-name');
        $branch = $opts->get('--branch');
        $deployPath = $opts->get('--deploy-path');
        $active = 1;

        while (empty($name)) {
            $stdio->out('Please enter the name of this project: ');
            $name = $stdio->in();
        }

        while (empty($repo)) {
            $stdio->out('Please enter central repo to pull from: ');
            $repo = $stdio->in();
        }

        while (empty($repoName)) {
            $stdio->out('Please enter the repo name (vendor/project): ');
            $repoName = $stdio->in();
        }

        while (empty($branch)) {
            $stdio->out('Please enter the branch to work against: ');
            $branch = $stdio->in();
        }

        while (empty($deployPath)) {
            $stdio->out('Please enter the full path where this project lives: ');
            $deployPath = $stdio->in();
        }

        try {
            $project = $projectService->createProject(compact('name', 'repo', 'repoName', 'branch', 'active', 'deployPath'));
            $this->projectService->addBuildProperty($project, 'repo.dir', $project->deployPath);
            $this->projectService->addBuildProperty($project, 'repo.branch', $project->branch);
            $this->projectService->addBuildProperty($project, 'import.common', PRIMUS_ROOT.'/app/config/phing/build.common.local.xml');
            $this->buildPhingConfig($project);

            echo 'Created new project '.$project->name.' with a DB id of '.$project->id.PHP_EOL;
        } catch(\PDOException $e) {
            if(stripos($e->getMessage(), 'duplicate entry') !== false) {
                echo 'Project with that name already exists'.PHP_EOL;
            } else {
                echo 'Oh crap, we broke: '.$e->getMessage().PHP_EOL;
            }
        }
    }

    public function deleteAction()
    {
        $project = $this->projectService->fetchProject($this->args[3]);
        if($project) {
            $this->displayProjectData($project);
            $this->stdio->out('Are you sure you want to delete this project (y|n)? ');
            $decision = $this->stdio->in();
            $decision = strtolower($decision);

            if($decision == 'y') {
                $this->projectService->deleteProject($project);
            }
        } else {
            $this->stdio->outln('That project does not exist');
        }
    }

    public function deployAction()
    {
        $args = $this->context->argv->get();
        $project = $this->projectService->fetchProject($args[3]);
        if($project && $project->active) {
            $deploymentService = $this->di->get('service.deployment');
            $deploymentService->deploy($project);
            $this->stdio->outln('Deployed');
        } else {
            $this->stdio->outln('There is no project by that name');
        }
    }

    /**
     * Displays the information about a project
     *
     * @param Project $project
     */
    protected function displayProjectData($project)
    {
        $this->stdio->outln('Project Name: '.$project->name);
        $this->stdio->outln('Project Repo: '.$project->repo);
        $this->stdio->outln('Repo Name: '.$project->repoName);
        $this->stdio->outln('Active? '.($project->active ? 'Yes' : 'No'));
        $this->stdio->outln('');
        $this->stdio->outln('Build Properties: ');
        $this->stdio->outln(str_repeat('-', 80));
        foreach($project->getBuildProperties() as $property) {
            $this->stdio->outln($property->property.': '.$property->propertyValue);
        }
    }

    /**
     * Allows the user to edit a project settings
     */
    public function editAction()
    {
        $project = $this->projectService->fetchProject($this->args[3]);

        if($project) {
            $option = '';
            do {
                $this->displayProjectData($project);

                $this->stdio->out('What do you want to edit (name|repoName|active)? ');
                $option = $this->stdio->in();
                $option = strtolower($option);
                switch($option) {
                    case 'name':
                        $this->stdio->out('Please enter the new name: ');
                        $name = $this->stdio->in();
                        $project->name = $name;
                        $this->projectService->save($project);
                        break;
                    case 'active':
                        $this->stdio->out('Set the active status (0|1): ');
                        $active = $this->stdio->in();
                        $project->active = $active;
                        $this->projectService->save($project);
                        break;
                    case 'reponame':
                        $this->stdio->out('Please enter the new repo name in "vendor/project" format: ');
                        $name = $this->stdio->in();
                        $project->repoName = $name;
                        $this->projectService->save($project);
                        break;
                     default:
                        $this->stdio->outln('Exiting...');
                        break;
                }
            }while(!empty($option));
        } else {
            $this->stdio->outln('That project does not exist');
        }
    }

    /**
     * Lists all of the projects that are in the system
     */
    public function indexAction()
    {
        $stdio = $this->di->get('cli.stdio');
        $projectService = $this->di->get('service.project');
        $projects = $projectService->fetchAllProjects();

        foreach($projects as $project) {
            $branch = '';
            foreach($project->getBuildProperties() as $property) {
                if($property->property == 'repo.branch') {
                    $branch = $property->propertyValue;
                }
            }
            $stdio->outln($project->name.' - '.$branch.' - '.($project->active ? 'Active' : 'Inactive'));
        }
    }

    /**
     * Searches for a project based on the repo name and branch
     * If no project is found, there is no output.
     */
    public function searchAction()
    {
        $args = $this->context->argv->get();
        $project = $this->projectService->findProjectBy(array('repoName' => $args[3], 'branch' => $args[4]));
        if($project) {
            $this->stdio->outln($project->name);
        }
    }

    /**
     * Displays the settings for a project
     */
    public function viewAction()
    {
        $args = $this->context->argv->get();
        $project = $this->projectService->fetchProject($args[3]);
        if($project) {
            $this->displayProjectData($project);
        } else {
            $this->stdio->outln('There is no project by that name');
        }
    }
}
