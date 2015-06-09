<?php

echo "asdasD";exit;
include 'vendor/autoload.php';

$client = new Asana(['apiKey' => '2MDZ4m4k.WbIKikDvyzTb4hVfOHXhsFV']);
$projectId = 36306074788595;

$options = ['due_on' => 'today','completed' => 'true', 'opt_fields'=> 'name, assignee, archived, created_at, assignee_status, completed, due_on'];
$result = json_decode($client->getProjectTasks($projectId, $options), true);


$released = [];
foreach ($result['data'] as $t) {

    if (empty($t['due_on'])) {
        continue;
    }
    $released[$t['due_on']][] = $t['name'];
}

ksort($released);
print_r($released);exit;

$client = Asana\Client::basicAuth('2MDZ4m4k.WbIKikDvyzTb4hVfOHXhsFV');
$options = [
    'iterator_type' => false,
    'page_size' => 1000
];

//print_r($client->tasks->findAll(['workspace' => 7263438403555, 'assignee' => 'me'], $options));
//print_r($client->projects->findAll());exit;

$params = [
    'completed_since' => 'now',
    'project' => 36306074788595,
];

$tasks = $client->tasks->findAll($params, $options);

foreach ($tasks as $t) {
    print_r($t);
    exit;
}
