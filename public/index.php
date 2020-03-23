<?php

use App\Controllers\AbstractHttpException;
use Dmkit\Phalcon\Auth\Middleware\Micro as AuthMicro;
use Phalcon\Events\Manager;
use App\Middleware\CORSMiddleware; // for CORS origine
use App\Middleware\JWTMiddleware;

try {
    // Loading Configs
    $config = require(__DIR__ . '/../app/config/config.php');

    // Autoloading classes
    require __DIR__ . '/../app/config/loader.php';

    // Initializing DI container
    /** @var \Phalcon\DI\FactoryDefault $di */
    $di = require __DIR__ . '/../app/config/di.php';

    // Initializing application
    $app = new \Phalcon\Mvc\Micro();
    // Setting DI container
    $app->setDI($di);


    $eventsManager = new Manager();
    $eventsManager->attach('micro', new CORSMiddleware());
    $eventsManager->attach('micro', new JWTMiddleware());
    $app->before(new CORSMiddleware());

    $app->before(new JWTMiddleware());

    // Setting up routing
    require __DIR__ . '/../app/config/router.php';

    // Making the correct answer after executing
    $app->after(
            function () use ($app) {
        // Getting the return value of method
        $return = $app->getReturnedValue();

        if (is_array($return)) {
            // Transforming arrays to JSON
            $app->response->setJsonContent($return);
        } elseif (!strlen($return)) {
            // Successful response without any content
            $app->response->setStatusCode('204', 'No Content');
        } else {
            // Unexpected response
            throw new Exception('Bad Response');
        }

        // Sending response to the client
        $app->response->send();
    }
    );

    /*if($app->request->isOptions()){

        $app->response->json('{"method":"OPTIONS"}', 200, $headers);
    }*/

    if($app->request->isOptions()){
        /*$headers = [
            'Access-Control-Allow-Origin'      => '*',
            'Access-Control-Allow-Methods'     => 'POST, GET, OPTIONS, PUT, DELETE',
            'Access-Control-Allow-Credentials' => 'true',
            'Access-Control-Max-Age'           => '86400',
            'Access-Control-Allow-Headers'     => 'Content-Type, Authorization, X-Requested-With'
        ];*/
        $response = new \Phalcon\Http\Response();
        $response->setStatusCode(200, 'All ok');

        $response->setHeader('Access-Control-Allow-Origin', '*')
            ->setHeader(
                'Access-Control-Allow-Methods',
                'GET, PUT, POST, DELETE, OPTIONS, CONNECT, HEAD, PURGE, PATCH'
            )
            ->setHeader(
                'Access-Control-Allow-Headers',
                'Content-Type, Authorization, X-Requested-With'
            )
            ->setHeader(
                'Access-Control-Max-Age',
                '86400'
            )
            ->setHeader('Access-Control-Allow-Credentials', 'true');
        $response->send();
        return;
    }

    // Processing request
    $request = new Phalcon\Http\Request();

    $uri = $request->getURI();
    $prefixLen = strlen(VERSION);

    if (substr($uri,0,$prefixLen) === VERSION) {
        $uri = substr($uri,$prefixLen);
    }

    $app->handle($uri);
} catch (AbstractHttpException $e) {
    $response = $app->response;
    $response->setStatusCode($e->getCode(), $e->getMessage());
    $response->setJsonContent($e->getAppError());
    $response->send();
} catch (\Phalcon\Http\Request\Exception $e) {
    $app->response->setStatusCode(400, 'Bad request')
            ->setJsonContent([
                AbstractHttpException::KEY_CODE => 400,
                AbstractHttpException::KEY_MESSAGE => 'Bad request'
            ])
            ->send();
} catch (\Exception $e) {

    // Standard error format
    if($di->getLogger()!=null && get_class($di->getLogger()) == 'Phalcon\Logger\Adapter\File')
        $di->getLogger()->critical(
            $e->getCode(). ' '. $e->getMessage()
        );

    $result = [
        AbstractHttpException::KEY_CODE => 500,
        AbstractHttpException::KEY_MESSAGE => 'Some error occurred on the server.'
    ];

    // Sending error response
    $app->response->setStatusCode(500, 'Internal Server Error')
            ->setJsonContent($result)
            ->send();
}
