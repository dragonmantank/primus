<?php

namespace Primus\Service;

use PhpORM\Repository\RepositoryInterface;
use Primus\Project\Project;
use Primus\Project\ProjectTask;

class ProjectService
{
    /**
     * @var \PhpORM\Repository\RepositoryInterface
     */
    protected $projectRepo;

    /**
     * @var \PhpORM\Repository\RepositoryInterface
     */
    protected $projectTaskRepo;

    /**
     * @param RepositoryInterface $projectRepo Repository object for Projects
     * * @param RepositoryInterface $projectTaskRepo Repository object for Project Tasks
     */
    public function __construct(RepositoryInterface $projectRepo, RepositoryInterface $projectTaskRepo)
    {
        $this->projectRepo = $projectRepo;
        $this->projectTaskRepo = $projectTaskRepo;
    }

    public function addTask($project, $task)
    {
        try {
            $projectTask = new ProjectTask();
            $projectTask->project_id = $project->id;
            $projectTask->task = ucfirst($task);

            $this->projectTaskRepo->save($projectTask);
        } catch(\PDOException $e) {
            if(strpos($e->getMessage(), '1062 Duplicate entry')) {
                // This left blank
            } else {
                throw new \Exception($e->getMessage());
            }
        }
    }

    /**
     * Creates a new project from a set of data
     *
     * @param array $data
     * @return Project
     */
    public function createProject($data = array())
    {
        $project = new Project();
        $project->name = $data['name'];
        $project->repo = $data['repo'];
        $project->repoName = $data['repoName'];
        $project->branch = $data['branch'];
        $project->active = $data['active'];
        $project->deployPath = $data['deployPath'];
        $project->setTasks($this->returnProjectTasks($project));

        $project->id = $this->projectRepo->save($project);

        return $project;
    }

    public function fetchAllProjects()
    {
        return $this->projectRepo->fetchAll();
    }

    public function fetchProject($projectName)
    {
        $project = $this->projectRepo->findBy(array('name' => $projectName));
        $project->setTasks($this->returnProjectTasks($project));

        return $project;
    }

    public function findProjectBy($criteria)
    {
        $project = $this->projectRepo->findBy($criteria);
        $project->setTasks($this->returnProjectTasks($project));

        return $project;
    }

    protected function returnProjectTasks($project) {
        $projectTaskRepo = $this->projectTaskRepo;
        return function() use($project, $projectTaskRepo) {
            $tasks = $projectTaskRepo->fetchAllBy(array('project_id' => $project->id));
            return $tasks;
        };
    }
}
