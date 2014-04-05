<?php

namespace Primus\App\Command;

use Primus\Deployer\LocalFSDeployer;

class ProjectsCommand
{
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

        $this->stdio = $this->di->get('cli.stdio');
    }

    public function addtaskAction()
    {
        $project = $this->projectService->fetchProject($this->context->argv->get()[3]);
        if($project) {
            $validTasks = ['drush'];
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
        $opts = $this->context->getopt(['name:', 'repo:', 'branch:', 'deploy-path:']);
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

    public function deployAction()
    {
        $project = $this->projectService->fetchProject($this->context->argv->get()[3]);
        if($project) {
            $deploymentService = $this->di->get('service.deployment');
            $deploymentService->deploy($project);
            $this->stdio->outln('Deployed');
        } else {
            $this->stdio->outln('There is no project by that name');
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
     * Displays the settings for a project
     */
    public function viewAction()
    {
        $project = $this->projectService->fetchProject($this->context->argv->get()[3]);
        if($project) {
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
        } else {
            $this->stdio->outln('There is no project by that name');
        }
    }
}