<?php
use Coquelux\Request;
use Coquelux\Config;
use Symfony\Component\HttpFoundation\Response;

use Doctrine\MongoDB\Connection;
use Doctrine\ODM\MongoDB\Configuration;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver;

define("APP_NAME", getenv('APP_NAME'));
define("APP_ENV", getenv('APP_ENV'));
define("BASE_DIR", __DIR__ . '/../');

$loader = require_once BASE_DIR . 'vendor/autoload.php';
$loader->add('Documents', BASE_DIR . 'src');

$app = new Coquelux\Application();
$app['debug'] = true;

/*
$connection = new Connection(Config::get()->mongodb->server);
$config = new Configuration();
$config->setProxyDir(BASE_DIR . 'src/Models/Proxies');
$config->setProxyNamespace('Models\Proxies');
$config->setHydratorDir(BASE_DIR . 'src/Models//Hydrators');
$config->setHydratorNamespace('Models\Hydrators');
$config->setDefaultDB('tasker');

Doctrine\Common\Annotations\AnnotationRegistry::registerAutoloadNamespace(
    'JMS\Serializer\Annotation', '../vendor/jms/serializer/src'
);

$config->setMetadataDriverImpl(AnnotationDriver::create(BASE_DIR . 'src/Documents'));
AnnotationDriver::registerAnnotationClasses();

$dm = DocumentManager::create($connection, $config);
*/

$app['tasks'] = $app->share(function () use ($app) {
    $http = new GuzzleHttp\Client();
    $github = new Models\Github($http);
    $asana = new Asana(['apiKey' => Config::get()->asana->apiKey]);
    $commitHistory = new Models\CommitHistory();
    return new Controllers\Tasks($asana, $github, $commitHistory);
});

$app->before(function (Request $request, Coquelux\Application $app) {
    if (extension_loaded('newrelic')) {
        newrelic_name_transaction(current(explode('?', $_SERVER['REQUEST_URI'])));
    }
});

$app->after(function (Request $request, Response $response) {
    $response->headers->set('Access-Control-Allow-Origin', '*');
    $response->headers->set('Access-Control-Allow-Headers', 'Content-Type');
    $response->headers->set('Access-Control-Allow-Methods', 'GET,PUT,POST,HEAD,DELETE,OPTIONS');
    if ($response->getStatusCode() != '500') {
        $response->headers->set('Content-Type', 'application/json; charset=UTF-8');
    }
});

$app->match("{url}", function($url) use ($app) { return "OK"; })->assert('url', '.*')->method("OPTIONS");

$app->register(new Silex\Provider\ServiceControllerServiceProvider());
$app->register(new Silex\Provider\ValidatorServiceProvider());

$app->get('/tasks', 'tasks:get');
$app->get('/tasks/deploy', 'tasks:deploy');
$app->run();
