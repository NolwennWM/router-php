<?php 
/** @var NWM\Router\Router $router */
/**
 * Example of routes array
 * Key is the route part after the domain
 * Value is the file to include
 * The router will look for a corresponding key in the array
 * If found, it will include the corresponding file
 * 
 * If the controller is a class, it will instantiate it and use the route attribute to call the right method
 * The controller class must be in the namespace defined in the router
 */
$routes = [
    ""=>"HomeController.php",
    "about"=>"HomeController.php",
    "example"=>function(){
        echo "This is an example of route using a function";
    }
];

$router->pageRouting($routes, startBy: true);