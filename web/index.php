<?php
use Coquelux\Request;
use Coquelux\Config;
use Symfony\Component\HttpFoundation\Response;
use Aws\Ses\SesClient;

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

$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => BASE_DIR . '/src/Views',
));
$app['connection'] = function ($app) {
    return new Connection(Config::get()->mongodb->server);
};

$app['configMongo'] = function ($app) {
    $config = new Configuration();
    $config->setDefaultDB(Config::get()->mongodb->dbName);
    $config->setProxyDir(BASE_DIR . 'src/Models/Proxies');
    $config->setProxyNamespace('Models\Proxies');
    $config->setHydratorDir(BASE_DIR . 'src/Models//Hydrators');
    $config->setHydratorNamespace('Models\Hydrators');

    Doctrine\Common\Annotations\AnnotationRegistry::registerAutoloadNamespace(
        'JMS\Serializer\Annotation',
        '../vendor/jms/serializer/src'
    );

    $config->setMetadataDriverImpl(AnnotationDriver::create(BASE_DIR . 'src/Documents'));
    AnnotationDriver::registerAnnotationClasses();

    return $config;
};

$app['dm'] = function ($app) {
    return DocumentManager::create($app['connection'], $app['configMongo']);
};


$app['assana'] = function ($app) {
    $client = new Asana(['apiKey' => Config::get()->asana->apiKey]);
    return new Models\Assana($client);
};

$app['github'] = function ($app) {
    $http = new GuzzleHttp\Client();
    return new Models\Github($http);
};

$app['ses'] = function ($app) {
    return new Aws\Ses\SesClient([
        'credentials' => [
            'key'    => Config::get()->aws->key,
            'secret' => Config::get()->aws->secret,
        ],
        'region' => Config::get()->aws->region,
        'version' => 'latest',
    ]);
};

$app['report'] = $app->share(function () use ($app) {
    $tasks = new Models\Resources\Tasks($app['dm']);

    return new Controllers\Reports(
        $app['assana'], $app['github'], $app['twig'], $app['ses'], $tasks
    );
});

$app['tasks'] = $app->share(function () use ($app) {
    $http = new GuzzleHttp\Client();
    $assana = new Asana(['apiKey' => Config::get()->asana->apiKey]);
    $commitHistory = new Models\CommitHistory();
    return new Controllers\Tasks($assana, $app['github'], $commitHistory);
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
//        $response->headers->set('Content-Type', 'application/json; charset=UTF-8');
    }
});

$app->match("{url}", function($url) use ($app) { return "OK"; })->assert('url', '.*')->method("OPTIONS");

$app->register(new Silex\Provider\ServiceControllerServiceProvider());
$app->register(new Silex\Provider\ValidatorServiceProvider());

list($_, $method, $path) = $argv;
$request = Request::create($path, $method);

$app->get('/projects', 'report:get');
$app->get('/tasks', 'report:getTasks');
$app->get('/tasks/deploy', 'tasks:deploy');
$app->run($request);
