<?php 
namespace NWM\Router;
/**
 * Route Attribute to define the route of a method in a controller
 */
#[\Attribute(\Attribute::TARGET_METHOD)]
class Route_Attribute
{
    /**
     * Constructor of the Route Attribute
     *
     * @param string $name
     * @param boolean|null $isLoggedAccess
     * @param string $method
     */
    public function __construct(private string $name="", private bool|null $isLoggedAccess = null, private string $method = "GET") {}
    /**
     * Check if the route is valid
     *
     * @return boolean
     */
    public function isValidRoute(string $checked_route): bool
    {
        if($checked_route === $this->name)
        {
            if($this->isValidMethod())
            {
                if($this->isGrantedAccess())
                {
                    return true;
                }
            }
        }
        return false;
    }
    /**
     * Check if the user is granted access
     *
     * @return boolean
     */
    public function isGrantedAccess(): bool
    {
        if($this->isLoggedAccess === null)return true;
        if($this->isLoggedAccess && !isset($_SESSION["admin"]))
        {
            header("Location: /admin/auth/login");
            exit;
        }
        if(!$this->isLoggedAccess && isset($_SESSION["admin"]))
        {
            header("Location: /admin/dashboard");
            exit;
        }
        return true;
    }
    /**
     * Check if the method is valid
     *
     * @return boolean
     */
    public function isValidMethod(): bool
    {
        return $this->method==="ANY"?true:$this->method === $_SERVER["REQUEST_METHOD"];
    }
}