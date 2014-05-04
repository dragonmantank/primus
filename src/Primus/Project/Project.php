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
    protected $tasks;

    public function getSlug()
    {
        $slug = strtolower($this->name);
        return preg_replace('/[^A-Za-z0-9-]+/', '-', $slug);
    }

    /**
     * Returns the tasks associated with this project
     * @return array
     */
    public function getTasks()
    {
        if($this->tasks instanceof \Closure) {
            $this->tasks = call_user_func($this->tasks);
        }

        return $this->tasks;
    }

    /**
     * Sets the tasks associated with this project
     * @param \Closure|array $tasks
     */
    public function setTasks($tasks)
    {
        $this->tasks = $tasks;
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
