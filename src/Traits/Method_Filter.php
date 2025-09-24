<?php

namespace NWM\Router\Traits;

/**
 * Trait to filter routing by HTTP method
 */
trait Method_Filter {
    /**
     * Method to filter the routing by HTTP method
     *
     * @param string $method HTTP method to filter by
     * @param string $route Route to match
     * @param callable|string $callback Callback to execute if the route matches
     * @return void
     */
    public function filter(string $method, string $route, callable|string $callback) 
    {
        if ($_SERVER['REQUEST_METHOD'] === $method) 
        {
            $this->filteredRouting($route, $callback);
            exit;
        }
    }
    /**
     * Method to implement in the child class to handle the routing filtering
     *
     * @param string $route
     * @param callable|string $callback
     * @return void
     */
    abstract protected function filteredRouting(string $route, callable|string $callback);
    /**
     * Method to handle any HTTP method
     *
     * @param string $route Route to match
     * @param callable|string $callback Callback to execute if the route matches
     * @return void
     */
    public function any(string $route, string|callable $callback)
    {
        $this->filteredRouting($route, $callback);
    }
    /**
     * Method to handle GET requests
     *
     * @param string $route Route to match
     * @param callable|string $callback Callback to execute if the route matches
     * @return void
     */
    public function get(string $route, string|callable $callback)
    {
        $this->filter("GET", $route, $callback);
    }
    /**
    * Method to handle POST requests
    *
    * @param string $route Route to match
    * @param callable|string $callback Callback to execute if the route matches
    * @return void
    */
    public function post(string $route, string|callable $callback)
    {
        $this->filter("POST", $route, $callback);
    }
    /**
    * Method to handle PUT requests
    *
    * @param string $route Route to match
    * @param callable|string $callback Callback to execute if the route matches
    * @return void
    */
    public function put(string $route, string|callable $callback)
    {
        $this->filter("PUT", $route, $callback);
    }
    /**
     * Method to handle DELETE requests
     *
     * @param string $route Route to match
     * @param callable|string $callback Callback to execute if the route matches
     * @return void
     */
    public function delete(string $route, string|callable $callback)
    {
        $this->filter("DELETE", $route, $callback);
    }
    /**
     * Method to handle PATCH requests
     *
     * @param string $route Route to match
     * @param callable|string $callback Callback to execute if the route matches
     * @return void
     */
    public function patch(string $route, string|callable $callback)
    {
        $this->filter("PATCH", $route, $callback);
    }
}