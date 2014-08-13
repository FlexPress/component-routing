<?php

namespace FlexPress\Components\Routing;

use FlexPress\Components\Routing\Route;
use Symfony\Component\HttpFoundation\Request;

class Router
{

    /**
     * @var array
     */
    protected $filters;

    /**
     * @var \SplQueue
     */
    protected $routes;

    /**
     * @var Route
     */
    protected $routePrototype;

    public function __construct(\SplQueue $routes, Route $routePrototype)
    {
        $this->filters = array();
        $this->routes = $routes;
        $this->routePrototype = $routePrototype;
    }

    /**
     * Runs through all the routes and runs them
     * @author Tim Perry
     */
    public function route()
    {
        $this->routes->rewind();
        while ($this->routes->valid()) {
            $route = $this->routes->current();
            if ($route->run()) {
                return;
            }
            $this->routes->next();
        }
    }

    /**
     *
     * Adds a route to the router
     *
     * @param $callable
     * @param $conditions
     * @author Tim Perry
     */
    public function addRoute($callable, $conditions)
    {

        $route = clone $this->routePrototype;

        $route->setCallable($callable);

        if (!is_array($conditions)) {
            $conditions = array($conditions);
        }
        $route->addConditionsFromArray($this->replaceFilterFunctions($conditions));
        $this->routes->enqueue($route);

    }

    /**
     * Adds a filter to the router
     *
     * @param $name
     * @param $function
     * @author Tim Perry
     */
    public function addFilter($name, $function)
    {
        $this->filters[$name] = $function;
    }

    /**
     * Replaces the string reference
     *
     * @param $conditions
     * @return string
     * @author Tim Perry
     */
    protected function replaceFilterFunctions(array $conditions)
    {

        foreach ($conditions as $key => $condition) {

            if (is_string($condition)) {
                $conditions[$key] = $this->filters[$condition];
            }

        }

        return $conditions;

    }
}
