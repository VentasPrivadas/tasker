<?php

use \Mockery as m;

class DeployTest extends \Coquelux\TestCase
{
    /**
     * @dataProvider providerDeployController
     */
    public function testDeployShouldReturn200($asana, $github, $commitHistory, $params)
    {
        $task = new Controllers\Tasks($asana, $github, $commitHistory);
        $this->assertEquals(new Coquelux\Response, $task->deploy($params));
    }

    public function testGithubGetCommitShouldReturnAList()
    {
        $httpResponse = m::mock('ClientResponse');
        $httpResponse->shouldReceive('getBody')
            ->andReturn('{}');

        $http = m::mock('Client');
        $http->shouldReceive('get')
            ->andReturn(true);

        $github = new Models\Github($http);
        $response = $github->getCommits('test');

        $this->assertEquals(new \StdClass(), $response);
    }

    public function providerDeployController()
    {
        $githubResponse = [['commit' => ['message' => 'Merge pull request #2572 alizadas #3632']]];
        $params = new Coquelux\Request(
            ['repository' => 'test', 'environment' => 'Test']
        );
        $asana = m::mock('Asana');
        $asana->shouldReceive('commentOnTask');
        
        $github = m::mock('Models\Github');
        $github->shouldReceive('getCommits')
            ->andReturn($githubResponse);
        $github->shouldReceive('setCommits');

        $commitHistory = m::mock('Models\CommitHistory');
        $commitHistory->shouldReceive('shouldComment')
            ->andReturn(true);
        $commitHistory->shouldReceive('setCommit');

        return [
            [$asana, $github, $commitHistory, $params]    
        ];
    }
}
