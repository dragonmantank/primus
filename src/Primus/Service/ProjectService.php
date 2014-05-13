<?php

namespace Primus\Service;

use PhpORM\Repository\RepositoryInterface;
use Primus\Project\BuildProperty;
use Primus\Project\Project;

class ProjectService
{
    /**
     * @var \PhpORM\Repository\RepositoryInterface
     */
    protected $projectRepo;

    /**
     * @var \PhpORM\Repository\RepositoryInterface
     */
    protected $buildPropertiesRepo;

    /**
     * @param RepositoryInterface $projectRepo Repository object for Projects
     * * @param RepositoryInterface $buildPropertiesRepo Repository object for Project Tasks
     */
    public function __construct(RepositoryInterface $projectRepo, RepositoryInterface $buildPropertiesRepo)
    {
        $this->projectRepo = $projectRepo;
        $this->buildPropertiesRepo = $buildPropertiesRepo;
    }

    public function addBuildProperty($project, $property, $value)
    {
        try {
            $buildProperty = new BuildProperty();
            $buildProperty->project_id = $project->id;
            $buildProperty->property = $property;
            $buildProperty->propertyValue = $value;

            $this->buildPropertiesRepo->save($buildProperty);
            $project->setBuildProperties($this->returnBuildProperties($project));
        } catch(\PDOException $e) {
            if(strpos($e->getMessage(), '1062 Duplicate entry')) {
                // This left blank
            } else {
                throw new \Exception($e->getMessage());
            }
        }
    }

    public function updateBuildProperty($project, $property, $value)
    {
        try {
            foreach($project->getBuildProperties() as $currentProperty) {
                if($currentProperty->property == $property) {
                    $currentProperty->propertyValue = $value;
                    $this->buildPropertiesRepo->save($currentProperty);
                    $project->setBuildProperties($this->returnBuildProperties($project));
                }
            }
        } catch(\PDOException $e) {
            echo $e->getMessage()."\n";
        }
    }

    public function removeBuildProperty($project, $property)
    {
        try {
            foreach($project->getBuildProperties() as $currentProperty) {
                if($currentProperty->property == $property) {
                    $this->buildPropertiesRepo->delete(array('id' => $currentProperty->id));
                    $project->setBuildProperties($this->returnBuildProperties($project));
                }
            }
        } catch(\PDOException $e) {
            echo $e->getMessage()."\n";
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
        $project->setBuildProperties($this->returnBuildProperties($project));

        $project->id = $this->projectRepo->save($project);

        return $project;
    }

    public function deleteProject($project)
    {
        $this->projectRepo->delete(array('id' => $project->id));
    }

    public function fetchAllProjects()
    {
        $projects = $this->projectRepo->fetchAll();
        foreach($projects as $key => $project) {
            $project->setBuildProperties($this->returnBuildProperties($project));
        }
        return $projects;
    }

    public function fetchProject($projectName)
    {
        $project = $this->projectRepo->findBy(array('name' => $projectName));
        if($project) {
            $project->setBuildProperties($this->returnBuildProperties($project));
        }

        return $project;
    }

    public function findProjectBy($criteria)
    {
        $project = $this->projectRepo->findBy($criteria);
        if($project) {
            $project->setBuildProperties($this->returnBuildProperties($project));
        }

        return $project;
    }

    protected function returnBuildProperties($project) {
        $projectTaskRepo = $this->buildPropertiesRepo;
        return function() use($project, $projectTaskRepo) {
            $tasks = $projectTaskRepo->fetchAllBy(array('project_id' => $project->id));
            return $tasks;
        };
    }

    public function save($project)
    {
        return $this->projectRepo->save($project);
    }
}
