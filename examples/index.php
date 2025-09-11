<?php
use NWM\Router\Router;

require __DIR__."/routes.php";
$router = new Router();

if(!$router->isInWhiteList())
{
    $router->redirect("/");
    exit;
}
$router->setDefaultHTML(__DIR__."/view/include/Default_Admin_View.php");
$router->setRootPath(__DIR__."/");
$router->setControllerNamespace("Portfolio\\Admin\\Controller");

$router->pageRouting(ROUTES);