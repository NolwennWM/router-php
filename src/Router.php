<?php
// filepath: c:\Users\Nolwenn\Documents\dev\Dice-of-Developper\app\src\Router\Router.php
namespace NWM\Router;

use Portfolio\Renderer\Renderer;

/**
 * Router class to manage the routing of the website
 */
class Router
{
    public $route_index = 0;
    public $current_route = [];
    public $current_lang = "fr";
    public $available_lang = ["fr", "en", "jp"];
    private $rootPath = "";
    private $controllerNamespace = "";
    private $whiteList = [];
    private $default_html = "";

    public function __construct()
    {
        $this->startSession();
        $this->getFilteredURI();
        $this->setRootPath("");
        $this->setWhiteList();
    }

    public function getFilteredURI(): array
    {
        $uri = filter_var($_SERVER["REQUEST_URI"], FILTER_SANITIZE_URL);
        $uri = explode("?", $uri)[0];
        $uri = trim($uri, "/");
        $langs = [];
        preg_match('/(?:^|\/)([a-z]{2})(?:\/|$)/', $uri, $langs);
        $uri = explode("/", $uri);
        $this->current_route = $uri;
        if (isset($langs[1]) && in_array($langs[1], $this->available_lang)) {
            $this->current_lang = $langs[1];
        }
        return $uri;
    }

    public function pageRouting(array $routes): void
    {
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

    public function requirePage(string $file, array $data = [], array $toRender = []): void
    {
        $path = $this->rootPath . $file;
        $renderer = new Renderer($this->default_html, $this->current_lang);
        if (file_exists($path)) {
            $renderer->render($path, $data, $toRender);
            $fileName = basename($file, ".php");
            $className = $this->controllerNamespace . $fileName;
            if (class_exists($className, false)) {
                $this->callControllerClass($className);
            }
            exit;
        }
        $this->getPageNotFound("file not found");
    }

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

    public function setDefaultHTML(string $path): void
    {
        if (file_exists($path)) {
            $this->default_html = file_get_contents($path);
        }
    }

    public function getDefaultHTML(): string
    {
        return $this->default_html;
    }

    public function setRootPath(string $path): void
    {
        if (is_dir($path)) {
            $this->rootPath = $path;
        } elseif (is_dir($_ENV["ROOT_PATH"] ?? "")) {
            $this->rootPath = $_ENV["ROOT_PATH"];
        } else {
            $this->rootPath = __DIR__ . "/../";
        }
    }

    public function redirect(string $url, array $data = []): void
    {
        if (!empty($data)) {
            foreach ($data as $key => $value) {
                $this->addFlashMessage($key, $value);
            }
        }
        header("Location: " . $url);
        exit;
    }

    public function addFlashMessage(string $type, string|array $message): void
    {
        $_SESSION["flashes"][$type][] = $message;
    }

    public function getFlashMessages(string $type = ""): array
    {
        if (!empty($type)) {
            $flashes = $_SESSION["flashes"][$type] ?? [];
            unset($_SESSION["flashes"][$type]);
            return $flashes;
        }
        $flashes = $_SESSION["flashes"] ?? [];
        unset($_SESSION["flashes"]);
        return $flashes;
    }

    public function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function setControllerNamespace(string $namespace): void
    {
        $this->controllerNamespace = $namespace . "\\";
    }

    private function setWhiteList(array $ips = []): void
    {
        if (empty($ips) && !empty($_ENV["IP_WHITELIST"])) {
            $ips = explode(",", $_ENV["IP_WHITELIST"]);
        }
        if (!empty($ips)) {
            $this->whiteList = array_map('trim', $ips);
        }
    }

    public function isInWhiteList(): bool
    {
        if (empty($this->whiteList)) return true;
        $user_ip = $_SERVER['REMOTE_ADDR'] ?? '';
        return in_array($user_ip, $this->whiteList);
    }

    public function previewContent(string $html, int $limit = 150): string
    {
        $text = strip_tags($html);
        $text = trim(preg_replace('/\s+/', ' ', $text));
        if (mb_strlen($text) > $limit) {
            $text = mb_substr($text, 0, $limit) . '...';
        }
        return $text;
    }
}