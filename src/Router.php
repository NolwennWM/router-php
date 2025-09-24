<?php
namespace NWM\Router;

use NWM\Renderer\Renderer;
use NWM\Router\Services\Flash_Service;

/**
 * Router class to manage the routing of the website
 */
class Router
{
    use Traits\Method_Filter;

    public int $route_index = 0;
    public array|string $current_route = [];
    public string $current_lang = "en";
    public array $available_lang = ["fr", "en", "jp"];
    private string $rootPath = "";
    private string $controllerNamespace = "";
    private array $whiteList = [];
    public Flash_Service $flashService;
    public Renderer $renderer;

    public function __construct(bool $doRoutingWithArray = true, bool $startSession = true)
    {
        $this->flashService = new Flash_Service();
        if ($startSession) {
            $this->flashService->startSession();
        }
        $this->getFilteredURI($doRoutingWithArray);
        $this->setRootPath("");
        $this->setWhiteList();
        $this->renderer = new Renderer(lang: $this->current_lang);
    }

    protected function filteredRouting(string $route, callable|string $callback, string $functionName = "")
    {

    }
    /**
     * Method to get the current URI and filter it
     *
     * @param boolean $toArray whether to return the URI as an array
     * @return array|string
     */
    public function getFilteredURI($toArray = false): array
    {
        $uri = filter_var($_SERVER["REQUEST_URI"], FILTER_SANITIZE_URL);
        $uri = explode("?", $uri)[0];
        $uri = trim($uri, "/");
        if ($toArray) {
            $uri = explode("/", $uri);
        }
        $this->getLangFromURI();
        $this->current_route = $uri;
        return $uri;
    }
    /**
     * Method to get the language from the URI
     *
     * @param boolean $keepLang whether to keep the language in the URI
     * @return string
     */
    private function getLangFromURI(bool $keepLang = false): string
    {
        $lang = "";
        $currentRoute = $this->current_route;
        if (is_array($currentRoute)) 
        {
            $langs = preg_grep('/^[a-z]{2}$/', $currentRoute);
            if (!empty($langs))
            {
                $lang = $langs[array_key_first($langs)];
            }
        }else
        {
            $langs = [];
            preg_match('/(?:^|\/)([a-z]{2})(?:\/|$)/', $currentRoute, $langs);
            if (!empty($langs))
            {
                $lang = $langs[1];
            }
        }

        if (!empty($lang) && in_array($lang, $this->available_lang)) 
        {
            $this->current_lang = $lang;
        }
        if(!$keepLang)
        {
            if (is_array($currentRoute)) 
            {
                $currentRoute = array_filter($currentRoute, fn($part) => $part !== $lang);
                $currentRoute = array_values($currentRoute);
            }else
            {
                $currentRoute = preg_replace('/^(\/)?' . $lang . '(\/)?/', '', $currentRoute);
                $currentRoute = trim($currentRoute, "/");
            }
            $this->current_route = $currentRoute;
        }
        return $this->current_lang;
    }
    /**
     * Method to route the page based on an array of routes
     *
     * @param array $routes
     * @return void
     */
    public function pageRouting(array $routes, bool $startBy = false): void
    {
        if (empty($routes) || !is_array($routes)) {
            $this->getPageNotFound("no routes defined");
        }
        $route = $this->getNextRoutePart();
        if (array_key_exists($route, $routes)) {
            $this->requirePage($routes[$route]);
            exit;
        }
        $this->getPageNotFound("page not found");
    }

    public function getNextRoutePart(): string
    {
        $uri = $this->current_route;
        $route = $uri[$this->route_index] ?? "";
        $this->route_index++;
        return $route;
    }
    /**
     * Method to require a page and pass data to it
     *
     * @param string $file file path to the page
     * @param array $data data to pass as variable to the page
     * @param array $toRender data to render in the page
     * @return void
     */
    public function requirePage(string $file, array $data = [], array $toRender = []): void
    {
        $path = $this->rootPath . $file;
        
        if (file_exists($path)) {
            $this->renderer->render($path, $data, $toRender);
            $fileName = basename($file, ".php");
            $className = $this->controllerNamespace . $fileName;
            if (class_exists($className, false)) {
                $this->callControllerClass($className);
            }
            exit;
        }
        $this->getPageNotFound("file not found");
    }
    /**
     * Method to display a 404 page
     *
     * @param string $message optional message to display
     * @return void
     */
    public function getPageNotFound(string $message = ""): void
    {
        require __DIR__ . "/404.php";
        exit;
    }

    public function callControllerClass(object|string $className)
    {
        require __DIR__ . "/Route_Attribute.php";
        $classReflector = new \ReflectionClass($className);
        $routeToCheck = $this->getNextRoutePart();
        $methods = $classReflector->getMethods();
        foreach ($methods as $method) {
            $attrs = $method->getAttributes("Portfolio\Router\Route_Attribute");
            foreach ($attrs as $attr) {
                $routeAttribute = $attr->newInstance();
                if (!$routeAttribute->isValidRoute($routeToCheck)) continue;
                $controller = $classReflector->newInstance();
                $method->invoke($controller);
                exit;
            }
        }
        $this->getPageNotFound("No Method found");
    }
    /**
     * Set the root path for the router
     *
     * @param string $path path to the root directory
     * @return void
     */
    public function setRootPath(string $path = ""): void
    {
        if (is_dir($path)) {
            $this->rootPath = $path;
        } elseif (is_dir($_ENV["ROOT_PATH"] ?? "")) {
            $this->rootPath = $_ENV["ROOT_PATH"];
        } else {
            $this->rootPath = __DIR__ . "/../";
        }
    }
    /**
     * Method to redirect to a given URL
     * Data can be passed as flash messages
     *
     * @param string $url URL to redirect to
     * @param array $data Data to pass as flash messages
     * @return void
     */
    public function redirect(string $url, array $data = []): void
    {
        if (!empty($data)) 
        {
            foreach ($data as $key => $value) 
            {
                $this->flashService->addFlashMessage($key, $value);
            }
        }
        header("Location: " . $url);
        exit;
    }

    /**
     * Set the controller namespace for the router
     *
     * @param string $namespace namespace of the controllers
     * @return void
     */
    public function setControllerNamespace(string $namespace): void
    {
        $this->controllerNamespace = $namespace . "\\";
    }
    /**
     * Set the IP whitelist for the router
     *
     * @param array $ips array of IP addresses
     * @return void
     */
    private function setWhiteList(array $ips = []): void
    {
        if (empty($ips) && !empty($_ENV["IP_WHITELIST"])) {
            $ips = explode(",", $_ENV["IP_WHITELIST"]);
        }
        if (!empty($ips)) {
            $this->whiteList = array_map('trim', $ips);
        }
    }

    /**
     * Check if the current user's IP is in the whitelist
     *
     * @return boolean
     */
    public function isInWhiteList(): bool
    {
        if (empty($this->whiteList)) return true;
        $user_ip = $_SERVER['REMOTE_ADDR'] ?? '';
        return in_array($user_ip, $this->whiteList);
    }

    public function setAvaiableLang(array $langs): void
    {
        if (!empty($langs)) {
            $this->available_lang = $langs;
        }
    }   
}