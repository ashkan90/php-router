<?php


namespace Xav;


class Container
{
    use Macroable;

    protected $instance;

    public function __construct($instance)
    {
        $this->instance = $instance;
    }

    public function make($class)
    {
        return new $class();
    }
}