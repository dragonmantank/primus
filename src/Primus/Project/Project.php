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
}