<?php
use NWM\Router\Router;

$routeByArray =  __DIR__."/routes.php";
$routeByFunction =  __DIR__."/routes_function.php";
$router = new Router();

if(!$router->isInWhiteList())
{
    $router->redirect("/");
    exit;
}
$router->renderer->setDefaultHTML(__DIR__."/../pages/base.php");
$router->setRootPath(__DIR__."/../");
$router->setControllerNamespace("RouterPHP\\Examples\\Controller");

// Choose one of the following routing methods:

// Routing by array
require $routeByArray;
// Routing by function
// require $routeByFunction;