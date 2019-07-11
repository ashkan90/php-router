<?php


namespace Xav;


class ControllerDispatch
{

    /**
     * ControllerDispatch constructor.
     */
    public function __construct()
    {
    }

    public function dispatch(Route $route, $controller, $method)
    {
        $args = [];

        if (method_exists($controller, 'callAction')) {
            return $controller->callAction($method, $args);
        }

        return $controller->{$method}(...$args);
    }
}