<?php
namespace Models;

use Coquelux\Config;

class CommitHistory
{
    private $history = [];
    private $file;

    public function shouldComment($repository, $environment, $commit)
    {
        settype($commit, 'integer');
        $this->history = $this->getHistory($repository, $environment);
        if (in_array($commit, $this->history)) {
            return false;
        }
        return true;
        
    }

    public function setCommit($commit)
    {
        settype($commit, 'integer');
        $this->history[] = $commit;
        file_put_contents($this->file, json_encode($this->history));
    }

    private function getHistory($repository, $environment)
    {
        $this->file = BASE_DIR . 'data/' . $repository . '/' . $environment .'.json';

        if ( ! is_dir(BASE_DIR . 'data/' . $repository)) {
            mkdir(BASE_DIR . 'data/' . $repository);
        }

        if ( ! is_file($this->file)) {
            return [];
        }
        $history = json_decode(file_get_contents($this->file), true);
        return $history;
    }
}
