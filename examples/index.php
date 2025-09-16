<?php
use NWM\Router\Router;

require __DIR__."/routes.php";
$router = new Router();

if(!$router->isInWhiteList())
{
    $router->redirect("/");
    exit;
}
$router->setDefaultHTML(__DIR__."/pages/base.php");
$router->setRootPath(__DIR__."/");
$router->setControllerNamespace("RouterPHP\\Examples\\Controller");

$router->pageRouting(ROUTES);