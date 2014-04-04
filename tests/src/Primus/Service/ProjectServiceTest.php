<?php

namespace PrimusTest\Service;

use Primus\Project\Project;
use Primus\Service\ProjectService;

class ProjectServiceTest extends \PHPUnit_Framework_TestCase
{
    protected $repositoryMock;

    public function setUp()
    {
        $this->repositoryMock = $this->getMock('PhpORM\Repository\RepositoryInterface', array('fetchAll', 'fetchAllBy', 'find', 'findBy', 'save'));
    }

    /**
     * Makes sure that a project is correctly created
     */
    public function testCreateProject()
    {
        $data = [
            'name' => 'sampleproject',
            'repo' => 'https://tankws@bitbucket.org/tankws/ctankersley.com.git',
            'deployPath' => '/var/www/',
            'branch' => 'master',
            'active' => 1,
        ];

        $repo = $this->repositoryMock;
        $repo
            ->expects($this->once())
            ->method('save')
            ->will($this->returnValue(1))
        ;

        $service = new ProjectService($repo);
        $project = $service->createProject($data);

        $this->assertTrue($project->id > 0);
        $this->assertEquals($data['name'], $project->name);
        $this->assertEquals($data['repo'], $project->repo);
        $this->assertEquals($data['branch'], $project->branch);
        $this->assertEquals($data['active'], $project->active);
    }

    /**
     * Makes sure that two projects are not created with the same name
     * @expectedException Exception
     */
    public function testDoNoCreateDuplicateNamedProject()
    {
        $data = [
            'name' => 'sampleproject',
            'repo' => 'https://tankws@bitbucket.org/tankws/ctankersley.com.git',
            'deployPath' => '/var/www/',
            'branch' => 'master',
            'active' => 1,
        ];

        $repo = $this->repositoryMock;
        $repo
            ->expects($this->once())
            ->method('save')
            ->will($this->returnCallback(function($project) {
                if($project->name == 'sampleproject') {
                    throw new \Exception('Project already exists');
                }
            }))
        ;

        $service = new ProjectService($repo);
        $project = $service->createProject($data);
    }

    /**
     * Makes sure that we can properly return a project by it's name
     */
    public function testFetchProject()
    {
        $data = [
            'name' => 'sampleproject',
            'repo' => 'https://tankws@bitbucket.org/tankws/ctankersley.com.git',
            'branch' => 'master',
            'active' => 1,
        ];
        $repo = $this->repositoryMock;
        $repo
            ->expects($this->once())
            ->method('findBy')
            ->will($this->returnCallback(function($criteria) use ($data) {
                if($criteria['name'] == 'sampleproject') {
                    $project = new Project();
                    $project->name = 'sampleproject';
                    $project->repo = 'https://tankws@bitbucket.org/tankws/ctankersley.com.git';
                    $project->branch = 'master';
                    $project->active = 1;
                    $project->id = 1;

                    return $project;
                }
            }))
        ;

        $service = new ProjectService($repo);
        $project = $service->fetchProject('sampleproject');

        $this->assertTrue(!is_null($project));
        $this->assertTrue($project->id > 0);
        $this->assertEquals($data['name'], $project->name);
        $this->assertEquals($data['repo'], $project->repo);
        $this->assertEquals($data['branch'], $project->branch);
        $this->assertEquals($data['active'], $project->active);
    }

    public function testFetchProjectUsingRepoNameAndBranch()
    {
        $repo = $this->repositoryMock;
        $repo
            ->expects($this->once())
            ->method('findBy')
            ->will($this->returnCallback(function($criteria) {
                if($criteria['repoName'] == 'vendor/sampleproject' && $criteria['branch'] == 'master') {
                    $project = new Project();
                    $project->name = 'sampleproject';
                    $project->repoName = 'vendor/sampleproject';
                    $project->repo = 'https://tankws@bitbucket.org/tankws/ctankersley.com.git';
                    $project->deployPath = '/var/www/';
                    $project->branch = 'master';
                    $project->active = 1;
                    $project->id = 1;

                    return $project;
                }
            }))
        ;

        $service = new ProjectService($repo);
        $project = $service->findProjectBy(['repoName' => 'vendor/sampleproject', 'branch' => 'master']);

        $this->assertTrue($project->id > 0);
        $this->assertEquals('sampleproject', $project->name);
        $this->assertEquals('vendor/sampleproject', $project->repoName);
        $this->assertEquals('https://tankws@bitbucket.org/tankws/ctankersley.com.git', $project->repo);
        $this->assertEquals('master', $project->branch);
        $this->assertEquals(1, $project->active);
    }

    public function testDisableProject()
    {

    }

    public function testDeleteProject()
    {

    }

    public function testSetupProject()
    {

    }

    public function testDeployProject()
    {

    }
}
