<?php
/** @var NWM\Router\Router $router */

/**
 * Example of routes by methods
 * The router will try each routes in order
 * If a route matches, it require the corresponding file or call the corresponding function
 */


$router->get("", "MethodHomeController.php");
$router->get("about", "MethodHomeController.php");