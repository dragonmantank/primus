<?php

namespace Primus\Project;

class Project
{
    public $id;
    public $active;
    public $branch;
    public $deployPath;
    public $name;
    public $repo;
    public $repoName;
    protected $buildProperties;

    public function getSlug()
    {
        $slug = strtolower($this->name);
        return preg_replace('/[^A-Za-z0-9-]+/', '-', $slug);
    }

    /**
     * Returns the tasks associated with this project
     * @return array
     */
    public function getBuildProperties()
    {
        if($this->buildProperties instanceof \Closure) {
            $this->buildProperties = call_user_func($this->buildProperties);
        }

        return $this->buildProperties;
    }

    /**
     * Sets the build properties associated with this project
     * @param \Closure|array $buildProperties
     */
    public function setBuildProperties($buildProperties)
    {
        $this->buildProperties= $buildProperties;
    }

    public function toArray()
    {
        return array(
            'id' => $this->id,
            'active' => $this->active,
            'branch' => $this->branch,
            'deployPath' => $this->deployPath,
            'name' => $this->name,
            'repo' => $this->repo,
            'repoName' => $this->repoName,
        );
    }
}
