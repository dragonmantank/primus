<?php

namespace Primus\Service;

use PhpORM\Repository\RepositoryInterface;
use Primus\Project\Project;

class ProjectService
{
    /**
     * @var \PhpORM\Repository\RepositoryInterface
     */
    protected $projectRepo;

    /**
     * @param RepositoryInterface $projectRepo Repository object for Projects
     */
    public function __construct(RepositoryInterface $projectRepo)
    {
        $this->projectRepo = $projectRepo;
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

        $project->id = $this->projectRepo->save($project);

        return $project;
    }

    public function fetchAllProjects()
    {
        return $this->projectRepo->fetchAll();
    }

    public function fetchProject($projectName)
    {
        return $this->projectRepo->findBy(['name' => $projectName]);
    }

    public function findProjectBy($criteria)
    {
        return $this->projectRepo->findBy($criteria);
    }
}