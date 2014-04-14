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

    public function __construct($di, $context, $projectService)
    {
        $this->di = $di;
        $this->context = $context;
        $this->projectService = $projectService;
        $this->args = $this->context->argv->get();

        $this->stdio = $this->di->get('cli.stdio');
    }

    public function addtaskAction()
    {
        $args = $this->context->argv->get();
        $project = $this->projectService->fetchProject($args[3]);
        if($project) {
            $validTasks = array('drush');
            $this->stdio->out('Enter the task name ('.implode('|', $validTasks).'): ');
            $task = $this->stdio->in();
            $task = strtolower($task);
            if(in_array($task, $validTasks)) {
                $this->projectService->addTask($project, $task);
            } else {
                $this->stdio-outln('That is not a valid task.');
            }
        }
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
        $this->stdio->outln('Project Branch: '.$project->branch);
        $this->stdio->outln('Project Deploy Path: '.$project->deployPath);
        $this->stdio->outln('Active? '.($project->active ? 'Yes' : 'No'));
        $this->stdio->out('Tasks: ');
        foreach($project->getTasks() as $task) {
            $this->stdio->out($task->task);
        }
        $this->stdio->out(PHP_EOL);
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

                $this->stdio->out('What do you want to edit (name|branch|path|active|quit)? ');
                $option = $this->stdio->in();
                $option = strtolower($option);
                switch($option) {
                    case 'name':
                        $this->stdio->out('Please enter the new name: ');
                        $name = $this->stdio->in();
                        $project->name = $name;
                        $this->projectService->save($project);
                        break;
                    case 'branch':
                        $this->stdio->out('Please enter the new branch to work against: ');
                        $branch = $this->stdio->in();
                        $project->branch = $branch;
                        $this->projectService->save($project);
                        break;
                    case 'path':
                        $this->stdio->out('Please enter the new path to deploy to: ');
                        $path = $this->stdio->in();
                        $project->deployPath = $path;
                        $this->projectService->save($project);
                        break;
                    case 'active':
                        $this->stdio->out('Set the active status (0|1): ');
                        $active = $this->stdio->in();
                        $project->active = $active;
                        $this->projectService->save($project);
                        break;
                    case 'quit':
                        $this->stdio->outln('Exiting...');
                        break;
                     default:
                         break;
                }
            }while($option != 'quit');
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
            $stdio->outln($project->name.' - '.$project->branch.' - '.($project->active ? 'Active' : 'Inactive'));
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
