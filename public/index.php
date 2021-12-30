<?php

// Start a Session
if( !session_id() ) @session_start();

require '../vendor/autoload.php';

use Aura\SqlQuery\QueryFactory;
use Delight\Auth\Auth;
use DI\ContainerBuilder;
use League\Plates\Engine;


$containerBuilder = new ContainerBuilder();

$containerBuilder->addDefinitions([
    Engine::class => function() {
        return new Engine('../app/views');
    },

    PDO::class => function() {
        return new PDO('mysql:host=localhost; dbname=marlin_exam3;', 'root');
    },

    QueryFactory::class => function() {
        return new QueryFactory('mysql');
    },

    Auth::class => function($container) {
        return new Auth($container->get('PDO'), null, null, false);
    }
]);

$container = $containerBuilder->build();




$dispatcher = FastRoute\simpleDispatcher(function(FastRoute\RouteCollector $r) {
    $r->addRoute('GET', '/reg', ['App\controllers\RegistrationController', 'registration']);

    $r->addRoute('POST', '/regUser', ['App\controllers\RegistrationController', 'registrationUser']);


    $r->addRoute('GET', '/login', ['App\controllers\LoginController', 'login']);

    $r->addRoute('POST', '/loginUser', ['App\controllers\LoginController', 'loginUser']);

    $r->addRoute('GET', '/verification', ['App\controllers\LoginController', 'verification']);

    $r->addRoute('GET', '/logout', ['App\controllers\LoginController', 'logout']);


    $r->addRoute('GET', '/users', ['App\controllers\UsersController', 'showUsers']);

    $r->addRoute('GET', '/create', ['App\controllers\UsersController', 'create']);

    $r->addRoute('POST', '/createUser', ['App\controllers\UsersController', 'createUser']);

    $r->addRoute('GET', '/edit/{id:\d+}', ['App\controllers\UsersController', 'edit']);

    $r->addRoute('POST', '/editUser/{id:\d+}', ['App\controllers\UsersController', 'editUser']);

    $r->addRoute('GET', '/security/{id:\d+}', ['App\controllers\UsersController', 'security']);

    $r->addRoute('POST', '/securityUser/{id:\d+}', ['App\controllers\UsersController', 'securityUser']);

    $r->addRoute('GET', '/status/{id:\d+}', ['App\controllers\UsersController', 'status']);

    $r->addRoute('POST', '/statusUser/{id:\d+}', ['App\controllers\UsersController', 'statusUser']);


    $r->addRoute('GET', '/profile/{id:\d+}', ['App\controllers\UsersController', 'profile']);

    $r->addRoute('GET', '/avatar/{id:\d+}', ['App\controllers\UsersController', 'avatar']);

    $r->addRoute('POST', '/uploadAvatar/{id:\d+}', ['App\controllers\UsersController', 'uploadAvatar']);


    $r->addRoute('GET', '/deleteUser/{id:\d+}', ['App\controllers\UsersController', 'deleteUser']);

});

// Fetch method and URI from somewhere
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// Strip query string (?foo=bar) and decode URI
if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}
$uri = rawurldecode($uri);

$routeInfo = $dispatcher->dispatch($httpMethod, $uri);

switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        // ... 404 Not Found
        echo '404 Not Found';
        break;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        $allowedMethods = $routeInfo[1];
        // ... 405 Method Not Allowed
        echo '405 Method Not Allowed';
        break;
    case FastRoute\Dispatcher::FOUND:
        $handler = $routeInfo[1];
        $vars = $routeInfo[2];

        // ... call $handler with $vars
        $container->call($routeInfo[1], [$routeInfo[2]]);

        break;
}

