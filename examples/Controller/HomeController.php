<?php
namespace RouterPHP\Examples\Controller;

use NWM\Router\Route_Attribute as Route;

class HomeController
{
    #[Route(name: "", method: "GET")]
    public function index()
    {
        // Logique pour la page d'accueil
        echo "Bienvenue sur la page d'accueil!";
    }
    #[Route(name: "about", method: "GET")]
    public function about()
    {
        // Logique pour la page "À propos"
        echo "Ceci est la page À propos.";
    }
}