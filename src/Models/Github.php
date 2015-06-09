<?php
namespace Models;

use Coquelux\Config;

class Github
{
    private $http;

    public function __construct($http)
    {
        $this->http = $http;
    }

    public function getCommits($repository)
    {
        $res = $this->http->get(
            Config::get()->github->baseUrl . $repository 
            . '/commits', ['auth' =>  
                [
                    Config::get()->github->user, 
                    Config::get()->github->pass
                ]
            ]
        );
        return json_decode($res->getBody(), true);
    }
}
