<?php

use Phalcon\Db\Adapter\Pdo\Postgresql;
use Phalcon\Logger;
use Phalcon\Logger\Adapter\Stream;
use Phalcon\Logger\Adapter\Stream as FileAdapter;

use Phalcon\Mvc\View;
use Phalcon\Mvc\View\Engine\Php as PhpEngine;
use Phalcon\Mvc\View\Engine\Volt as VoltEngine;

use PhalconRest\Constants\Services;
use PhalconRest\Auth\Manager as AuthManager;
use App\Auth\UserEmailAccountType;
use App\Controllers\SessionAPIController;
use App\Services\SessionService;
use App\Libs\PseudoSession;
use Phalcon\Mailer;
use Phalcon\Di\FactoryDefault;
use App\Libs\Database\MySQLAdapter;

// Initializing a DI Container
$di = new FactoryDefault();
/**
 * Overriding Response-object to set the Content-type header globally
 */
$di->setShared(
        'response', function () {
    $response = new \Phalcon\Http\Response();
    $response->setContentType('application/json', 'utf-8');

    return $response;
}
);


/** Common config */
$di->setShared('config', $config);

/** Database */
$di->set(
    "db", function () use ($config) {
    return new Postgresql(
        [
            "host" => $config->database->host,
            "username" => $config->database->username,
            "password" => $config->database->password,
            "dbname" => $config->database->dbname,
        ]
    );
}
);

$di->setShared(
    "mysql", function () use ($config) {
    $mysql = new MySQLAdapter($config['mysql_server']);
    $mysql->openConnection();
    return $mysql;
}
);

$di->setShared('logger', function () {

    $adapter = new Stream(BASE_PATH . '/app/logs/debug.log');
    $logger  = new Logger(
        'messages',
        [
            'main' => $adapter,
        ]
    );
    return $logger;
});
$di->setShared('time', function () {
    $adapter = new Stream(BASE_PATH.'/app/logs/time.log');

    $logger  = new Logger(
        'time',
        [
            'main' => $adapter,
        ]
    );

    return $logger;
});

//
$di->setShared('authService', '\App\Services\AuthService'); //2
$di->setShared('bookService', '\App\Services\BookService'); //3
$di->setShared('authorService', '\App\Services\AuthorService'); //4
$di->setShared('reviewService', '\App\Services\ReviewService'); //5
$di->setShared('fileService', '\App\Services\FileService'); //6
$di->setShared('bookListsService', '\App\Services\BookListsService'); //7
$di->setShared('promotionService', '\App\Services\PromotionService'); //8

$di->setShared('userService', '\App\Services\UserService'); //1

$di['mailer'] = function() {
    $config = $this->getConfig()['mail'];
    $mailer = new \Phalcon\Mailer\Manager($config);
    return $mailer;
};

$di->setShared('session','\App\Libs\PseudoSession');

//It's only for parse content for sending mails.
$di->setShared('view', function () {
    $config = $this->getConfig();

    $view = new View();
    $view->setDI($this);
    $view->setViewsDir($config->application->viewsDir);

    $view->registerEngines([
        '.volt' => function ($view) {
            $config = $this->getConfig();

            $volt = new VoltEngine($view, $this);

            $volt->setOptions([
                'compiledPath' => $config->application->cacheDir,
                'compiledSeparator' => '_'
            ]);

            return $volt;
        },
        '.phtml' => PhpEngine::class

    ]);
    return $view;
});

return $di;
