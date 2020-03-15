<?php

use Phalcon\Mvc\Micro;
use Phalcon\Events\Manager;


$router = $di->getRouter();

$app = new Micro();

//$eventsManager->attach('micro', new CORSMiddleware());
//$app->after(new CORSMiddleware());

$routes = require __DIR__ . '/routerLoader.php';

foreach ($routes as $key => $valueGlob) {
    $collection = new \Phalcon\Mvc\Micro\Collection();
    $collection->setHandler($key, true);
    //$collection->setPrefix($value['prefix']);
    $resources = $valueGlob['resources'];
    foreach ($resources as $value) {
        $access = (!isset($value['access']) || $value['access'] == null) ? 'public' : $value['access'];
        $paths = [];
        if ($access == 'public')
            $paths[] = $valueGlob['prefix'] . $value['path'];

        $paths[] = '/' . $access . $valueGlob['prefix'] . $value['path'];

        //$paths = [$path];

        foreach ($paths as $path) {
            switch ($value['type']) {
                case 'post' :
                    $collection->post($path, $value['action']);
                    $collection->Options($path, $value['action']);
                    break;
                case 'put' :
                    $collection->put($path, $value['action']);
                    $collection->Options($path, $value['action']);
                    break;
                case 'delete' :
                    $collection->delete($path, $value['action']);
                    $collection->Options($path, $value['action']);
                    break;
                case 'get' :
                    $collection->get($path, $value['action']);
                    break;
            }
        }
    }
    $app->mount($collection);
}

// not found URLs
$app->notFound(
    function () use ($app) {
        $exception = new \App\Controllers\HttpExceptions\Http404Exception(
            _('URI not found or error in request.'),
            \App\Controllers\AbstractController::ERROR_NOT_FOUND,
            new \Exception('URI not found: ' . $app->request->getMethod() . ' ' . $app->request->getURI())
        );
        throw $exception;
    }
);

$router->handle();
$app->setEventsManager($eventsManager);
